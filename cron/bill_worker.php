<?php
/**
 * Bill Worker - Cron Script
 * Run this script via cron to handle:
 * - Send bill reminders
 * - Mark overdue bills
 * - Generate next recurring bills
 * 
 * Recommended crontab: */15 * * * * php /path/to/cron/bill_worker.php
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/notifications.php';

// Prevent duplicate runs
$lockFile = sys_get_temp_dir() . '/bill_worker.lock';
$fp = fopen($lockFile, 'w');
if (!flock($fp, LOCK_EX | LOCK_NB)) {
    echo "Bill worker is already running\n";
    exit;
}

echo "=== Bill Worker Started at " . date('Y-m-d H:i:s') . " ===\n\n";

$db = Database::getInstance();
$processedCount = 0;

try {
    // 1. Send Bill Reminders
    echo "1. Checking for bills needing reminders...\n";
    $remindersProcessed = sendBillReminders($db);
    echo "   Sent {$remindersProcessed} reminders\n\n";
    $processedCount += $remindersProcessed;
    
    // 2. Mark Overdue Bills
    echo "2. Marking overdue bills...\n";
    $overdueMarked = markOverdueBills($db);
    echo "   Marked {$overdueMarked} bills as overdue\n\n";
    $processedCount += $overdueMarked;
    
    // 3. Generate Next Recurring Bills
    echo "3. Generating next recurring bills...\n";
    $recurringGenerated = generateRecurringBills($db);
    echo "   Generated {$recurringGenerated} recurring bills\n\n";
    $processedCount += $recurringGenerated;
    
    // 4. Send Overdue Escalation Notifications
    echo "4. Checking for overdue escalations...\n";
    $escalationsProcessed = sendOverdueEscalations($db);
    echo "   Sent {$escalationsProcessed} escalation notifications\n\n";
    $processedCount += $escalationsProcessed;
    
    echo "=== Bill Worker Completed Successfully ===\n";
    echo "Total items processed: {$processedCount}\n";
    echo "Finished at: " . date('Y-m-d H:i:s') . "\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    error_log("Bill Worker Error: " . $e->getMessage());
} finally {
    flock($fp, LOCK_UN);
    fclose($fp);
}

/**
 * Send bill reminders for upcoming bills
 */
function sendBillReminders($db) {
    $count = 0;
    
    // Get bills that need reminders
    // Bill needs reminder if: due_date - reminder_days_before <= today AND not paid
    $bills = $db->fetchAll("
        SELECT b.*, u.email, u.telegram_chat_id, u.name as user_name
        FROM bills b
        JOIN users u ON b.user_id = u.id
        WHERE b.payment_status != 'paid'
        AND b.due_date - INTERVAL '1 day' * b.reminder_days_before <= CURRENT_DATE
        AND b.due_date >= CURRENT_DATE
        AND NOT EXISTS (
            SELECT 1 FROM notifications n
            WHERE n.user_id = b.user_id
            AND n.type = 'bill_reminder'
            AND n.reference_id = b.id::text
            AND n.created_at::date = CURRENT_DATE
        )
    ");
    
    foreach ($bills as $bill) {
        $daysUntilDue = ceil((strtotime($bill['due_date']) - time()) / 86400);
        $message = "Reminder: {$bill['name']} of " . formatCurrency($bill['amount']) . " is due in {$daysUntilDue} day(s) on " . formatDate($bill['due_date']);
        
        // Send email
        if ($bill['email']) {
            try {
                sendEmailNotification($bill['email'], 'Bill Reminder: ' . $bill['name'], $message);
                echo "   - Sent email reminder to {$bill['email']} for bill #{$bill['id']}\n";
            } catch (Exception $e) {
                echo "   - Failed to send email to {$bill['email']}: {$e->getMessage()}\n";
            }
        }
        
        // Send Telegram
        if ($bill['telegram_chat_id']) {
            try {
                sendTelegramNotification($bill['telegram_chat_id'], $message);
                echo "   - Sent Telegram reminder to user #{$bill['user_id']} for bill #{$bill['id']}\n";
            } catch (Exception $e) {
                echo "   - Failed to send Telegram: {$e->getMessage()}\n";
            }
        }
        
        // Create in-app notification
        $db->insert('notifications', [
            'user_id' => $bill['user_id'],
            'type' => 'bill_reminder',
            'message' => $message,
            'reference_id' => (string)$bill['id'],
            'is_read' => false
        ]);
        
        $count++;
    }
    
    return $count;
}

/**
 * Mark bills as overdue if past due date and not paid
 */
function markOverdueBills($db) {
    $count = 0;
    
    // Find bills that are past due and not marked as overdue
    $overdueBills = $db->fetchAll("
        SELECT b.*, u.email, u.telegram_chat_id
        FROM bills b
        JOIN users u ON b.user_id = u.id
        WHERE b.payment_status = 'pending'
        AND b.due_date < CURRENT_DATE
    ");
    
    foreach ($overdueBills as $bill) {
        // Update payment status to overdue (if your system tracks this)
        // Note: Based on schema, we only have 'pending' and 'paid', so we'll keep as pending but send notifications
        
        $daysOverdue = ceil((time() - strtotime($bill['due_date'])) / 86400);
        $message = "OVERDUE: {$bill['name']} of " . formatCurrency($bill['amount']) . " was due on " . formatDate($bill['due_date']) . " ({$daysOverdue} days overdue)";
        
        // Check if we already sent overdue notification today
        $notificationExists = $db->fetchOne(
            "SELECT id FROM notifications 
             WHERE user_id = ? 
             AND type = 'bill_overdue' 
             AND reference_id = ? 
             AND created_at::date = CURRENT_DATE",
            [$bill['user_id'], (string)$bill['id']]
        );
        
        if (!$notificationExists) {
            // Create in-app notification
            $db->insert('notifications', [
                'user_id' => $bill['user_id'],
                'type' => 'bill_overdue',
                'message' => $message,
                'reference_id' => (string)$bill['id'],
                'is_read' => false
            ]);
            
            echo "   - Marked bill #{$bill['id']} as overdue ({$daysOverdue} days)\n";
            $count++;
        }
    }
    
    return $count;
}

/**
 * Generate next occurrence for recurring bills that have been paid
 */
function generateRecurringBills($db) {
    $count = 0;
    
    // Find recurring bills that are paid and need next occurrence generated
    $recurringBills = $db->fetchAll("
        SELECT *
        FROM bills
        WHERE recurring = true
        AND payment_status = 'paid'
        AND (next_due_date IS NULL OR next_due_date <= CURRENT_DATE)
        AND frequency IS NOT NULL
    ");
    
    foreach ($recurringBills as $bill) {
        // Calculate next due date
        $nextDueDate = calculateNextDueDate($bill['due_date'], $bill['frequency']);
        
        // Check if a bill for this next occurrence already exists
        $existingNextBill = $db->fetchOne(
            "SELECT id FROM bills 
             WHERE user_id = ? 
             AND name = ? 
             AND due_date = ?
             AND vendor = ?",
            [$bill['user_id'], $bill['name'], $nextDueDate, $bill['vendor']]
        );
        
        if (!$existingNextBill) {
            // Create next bill
            $newBillData = [
                'user_id' => $bill['user_id'],
                'name' => $bill['name'],
                'amount' => $bill['amount'],
                'due_date' => $nextDueDate,
                'payment_status' => 'pending',
                'recurring' => true,
                'frequency' => $bill['frequency'],
                'category' => $bill['category'],
                'vendor' => $bill['vendor'],
                'reminder_days_before' => $bill['reminder_days_before'],
                'notes' => $bill['notes'],
                'auto_pay' => $bill['auto_pay'],
                'budget_id' => $bill['budget_id'],
                'payment_method' => $bill['payment_method'],
                'next_due_date' => calculateNextDueDate($nextDueDate, $bill['frequency'])
            ];
            
            $newBillId = $db->insert('bills', $newBillData);
            
            // Update original bill's next_due_date
            $db->update('bills', 
                ['next_due_date' => $nextDueDate], 
                ['id' => $bill['id']]
            );
            
            echo "   - Generated next occurrence for '{$bill['name']}' (ID: {$newBillId}, Due: {$nextDueDate})\n";
            $count++;
            
            // Notify user about new bill
            $message = "Recurring bill generated: {$bill['name']} of " . formatCurrency($bill['amount']) . " due on " . formatDate($nextDueDate);
            $db->insert('notifications', [
                'user_id' => $bill['user_id'],
                'type' => 'bill_generated',
                'message' => $message,
                'reference_id' => (string)$newBillId,
                'is_read' => false
            ]);
        }
    }
    
    return $count;
}

/**
 * Send escalation notifications for bills that are significantly overdue
 */
function sendOverdueEscalations($db) {
    $count = 0;
    
    // Send escalation for bills overdue by 7+ days
    $severelyOverdue = $db->fetchAll("
        SELECT b.*, u.email, u.telegram_chat_id
        FROM bills b
        JOIN users u ON b.user_id = u.id
        WHERE b.payment_status != 'paid'
        AND b.due_date < CURRENT_DATE - INTERVAL '7 days'
        AND NOT EXISTS (
            SELECT 1 FROM notifications n
            WHERE n.user_id = b.user_id
            AND n.type = 'bill_escalation'
            AND n.reference_id = b.id::text
            AND n.created_at::date = CURRENT_DATE
        )
    ");
    
    foreach ($severelyOverdue as $bill) {
        $daysOverdue = ceil((time() - strtotime($bill['due_date'])) / 86400);
        $message = "⚠️ URGENT: Bill '{$bill['name']}' is {$daysOverdue} days overdue! Amount: " . formatCurrency($bill['amount']);
        
        // Send email with high priority
        if ($bill['email']) {
            try {
                sendEmailNotification($bill['email'], 'URGENT: Overdue Bill Escalation', $message, true);
                echo "   - Sent escalation email for bill #{$bill['id']}\n";
            } catch (Exception $e) {
                echo "   - Failed to send escalation email: {$e->getMessage()}\n";
            }
        }
        
        // Send Telegram
        if ($bill['telegram_chat_id']) {
            try {
                sendTelegramNotification($bill['telegram_chat_id'], $message);
            } catch (Exception $e) {
                echo "   - Failed to send Telegram escalation: {$e->getMessage()}\n";
            }
        }
        
        // Create high-priority in-app notification
        $db->insert('notifications', [
            'user_id' => $bill['user_id'],
            'type' => 'bill_escalation',
            'message' => $message,
            'reference_id' => (string)$bill['id'],
            'is_read' => false
        ]);
        
        $count++;
    }
    
    return $count;
}

/**
 * Calculate next due date based on frequency
 */
function calculateNextDueDate($currentDate, $frequency) {
    $date = new DateTime($currentDate);
    
    switch ($frequency) {
        case 'weekly':
            $date->modify('+1 week');
            break;
        case 'biweekly':
            $date->modify('+2 weeks');
            break;
        case 'monthly':
            $date->modify('+1 month');
            break;
        case 'quarterly':
            $date->modify('+3 months');
            break;
        case 'yearly':
            $date->modify('+1 year');
            break;
        default:
            $date->modify('+1 month');
    }
    
    return $date->format('Y-m-d');
}

/**
 * Format currency helper
 */
function formatCurrency($amount) {
    return '$' . number_format($amount, 2);
}

/**
 * Format date helper
 */
function formatDate($date) {
    return date('M d, Y', strtotime($date));
}
