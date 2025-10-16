<?php
/**
 * Bills Calendar Export API
 * Generate ICS calendar file for bills
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$action = $_GET['action'] ?? 'export';

switch ($action) {
    case 'export':
        exportBillsCalendar($userId, $db);
        break;
    case 'google-sync':
        syncToGoogleCalendar($userId, $db);
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
}

function exportBillsCalendar($userId, $db) {
    // Get all unpaid bills for the user
    $bills = $db->fetchAll(
        "SELECT * FROM bills 
         WHERE user_id = ? 
         AND payment_status != 'paid' 
         ORDER BY due_date",
        [$userId]
    );
    
    // Get user info for calendar details
    $user = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$userId]);
    
    // Generate ICS content
    $ics = generateICS($bills, $user);
    
    // Set headers for file download
    header('Content-Type: text/calendar; charset=utf-8');
    header('Content-Disposition: attachment; filename="bills_calendar.ics"');
    header('Cache-Control: no-cache');
    
    echo $ics;
}

function generateICS($bills, $user) {
    $ics = "BEGIN:VCALENDAR\r\n";
    $ics .= "VERSION:2.0\r\n";
    $ics .= "PRODID:-//Life Atlas Organizer//Bills Calendar//EN\r\n";
    $ics .= "CALSCALE:GREGORIAN\r\n";
    $ics .= "METHOD:PUBLISH\r\n";
    $ics .= "X-WR-CALNAME:Bills & Payments\r\n";
    $ics .= "X-WR-TIMEZONE:UTC\r\n";
    $ics .= "X-WR-CALDESC:Bills and payment reminders from Life Atlas Organizer\r\n";
    
    foreach ($bills as $bill) {
        $ics .= generateBillEvent($bill, $user);
        
        // Add reminder event if reminder_days_before is set
        if ($bill['reminder_days_before'] > 0) {
            $ics .= generateReminderEvent($bill, $user);
        }
    }
    
    $ics .= "END:VCALENDAR\r\n";
    
    return $ics;
}

function generateBillEvent($bill, $user) {
    $uid = 'bill-' . $bill['id'] . '-' . time() . '@lifeatlas.local';
    $dtstamp = gmdate('Ymd\THis\Z');
    $dtstart = date('Ymd', strtotime($bill['due_date']));
    $summary = escapeICS($bill['name'] . ' - ' . formatCurrency($bill['amount']));
    $description = escapeICS(
        "Bill: {$bill['name']}\n" .
        "Amount: " . formatCurrency($bill['amount']) . "\n" .
        "Vendor: " . ($bill['vendor'] ?: 'N/A') . "\n" .
        "Category: " . ($bill['category'] ?: 'N/A') . "\n" .
        ($bill['notes'] ? "Notes: {$bill['notes']}\n" : '') .
        "Status: {$bill['payment_status']}"
    );
    
    $event = "BEGIN:VEVENT\r\n";
    $event .= "UID:{$uid}\r\n";
    $event .= "DTSTAMP:{$dtstamp}\r\n";
    $event .= "DTSTART;VALUE=DATE:{$dtstart}\r\n";
    $event .= "SUMMARY:{$summary}\r\n";
    $event .= "DESCRIPTION:{$description}\r\n";
    $event .= "STATUS:CONFIRMED\r\n";
    $event .= "SEQUENCE:0\r\n";
    $event .= "CATEGORIES:Bills,Finance\r\n";
    
    // Add alarm for reminder
    if ($bill['reminder_days_before'] > 0) {
        $reminderMinutes = $bill['reminder_days_before'] * 24 * 60;
        $event .= "BEGIN:VALARM\r\n";
        $event .= "TRIGGER:-PT{$reminderMinutes}M\r\n";
        $event .= "ACTION:DISPLAY\r\n";
        $event .= "DESCRIPTION:Reminder: {$bill['name']} due in {$bill['reminder_days_before']} days\r\n";
        $event .= "END:VALARM\r\n";
    }
    
    $event .= "END:VEVENT\r\n";
    
    return $event;
}

function generateReminderEvent($bill, $user) {
    $uid = 'bill-reminder-' . $bill['id'] . '-' . time() . '@lifeatlas.local';
    $dtstamp = gmdate('Ymd\THis\Z');
    
    // Calculate reminder date
    $reminderDate = date('Ymd', strtotime($bill['due_date'] . ' -' . $bill['reminder_days_before'] . ' days'));
    
    $summary = escapeICS('Reminder: ' . $bill['name'] . ' due in ' . $bill['reminder_days_before'] . ' days');
    $description = escapeICS(
        "Bill Payment Reminder\n" .
        "Bill: {$bill['name']}\n" .
        "Amount: " . formatCurrency($bill['amount']) . "\n" .
        "Due Date: " . formatDate($bill['due_date']) . "\n" .
        "Days Until Due: {$bill['reminder_days_before']}"
    );
    
    $event = "BEGIN:VEVENT\r\n";
    $event .= "UID:{$uid}\r\n";
    $event .= "DTSTAMP:{$dtstamp}\r\n";
    $event .= "DTSTART;VALUE=DATE:{$reminderDate}\r\n";
    $event .= "SUMMARY:{$summary}\r\n";
    $event .= "DESCRIPTION:{$description}\r\n";
    $event .= "STATUS:CONFIRMED\r\n";
    $event .= "SEQUENCE:0\r\n";
    $event .= "CATEGORIES:Reminders,Bills\r\n";
    $event .= "END:VEVENT\r\n";
    
    return $event;
}

function syncToGoogleCalendar($userId, $db) {
    // This would require Google Calendar API integration
    // For now, provide instructions or redirect to Google Calendar import
    
    $bills = $db->fetchAll(
        "SELECT * FROM bills 
         WHERE user_id = ? 
         AND payment_status != 'paid' 
         ORDER BY due_date",
        [$userId]
    );
    
    $user = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$userId]);
    $ics = generateICS($bills, $user);
    
    // Save ICS file temporarily
    $tempFile = sys_get_temp_dir() . '/bills_' . $userId . '_' . time() . '.ics';
    file_put_contents($tempFile, $ics);
    
    // Return instructions for Google Calendar import
    echo json_encode([
        'success' => true,
        'message' => 'Calendar file generated',
        'instructions' => [
            '1. Download the calendar file',
            '2. Go to Google Calendar (calendar.google.com)',
            '3. Click the + icon next to "Other calendars"',
            '4. Select "Import"',
            '5. Choose the downloaded ICS file',
            '6. Select the calendar to import to',
            '7. Click "Import"'
        ],
        'download_url' => '/api/bills_calendar.php?action=export',
        'auto_sync_available' => false,
        'note' => 'Google Calendar API integration can be added for automatic syncing'
    ]);
}

function escapeICS($text) {
    $text = str_replace(['\\', ',', ';', "\n"], ['\\\\', '\\,', '\\;', '\\n'], $text);
    return $text;
}

function formatCurrency($amount) {
    return '$' . number_format($amount, 2);
}

function formatDate($date) {
    return date('M d, Y', strtotime($date));
}
