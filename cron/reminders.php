<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (php_sapi_name() !== 'cli') {
    die('This script can only be run from the command line');
}

$db = Database::getInstance();

echo "Checking for upcoming birthdays and bills...\n\n";

$users = $db->fetchAll("SELECT * FROM users WHERE telegram_chat_id IS NOT NULL OR email IS NOT NULL");

foreach ($users as $user) {
    $userId = $user['id'];
    
    $upcomingBirthdays = getUpcomingBirthdays($userId, 3);
    $upcomingBills = getUpcomingBills($userId, 3);
    
    if (count($upcomingBirthdays) > 0 || count($upcomingBills) > 0) {
        $message = "📅 Upcoming Reminders:\n\n";
        
        if (count($upcomingBirthdays) > 0) {
            $message .= "🎂 Birthdays:\n";
            foreach ($upcomingBirthdays as $birthday) {
                $message .= "- " . $birthday['name'] . " on " . formatDate($birthday['birth_date'], 'M d') . "\n";
            }
            $message .= "\n";
        }
        
        if (count($upcomingBills) > 0) {
            $message .= "💳 Bills:\n";
            foreach ($upcomingBills as $bill) {
                $message .= "- " . $bill['name'] . ": " . formatCurrency($bill['amount']) . " due on " . formatDate($bill['due_date'], 'M d') . "\n";
            }
        }
        
        if (!empty($user['telegram_chat_id'])) {
            $sent = sendTelegramMessage($user['telegram_chat_id'], $message);
            echo $sent ? "✓" : "✗";
            echo " Telegram notification sent to user ID $userId\n";
        }
        
        if (!empty($user['email']) && !empty(SMTP_HOST)) {
            $sent = sendEmail($user['email'], 'Upcoming Reminders', nl2br($message));
            echo $sent ? "✓" : "✗";
            echo " Email notification sent to {$user['email']}\n";
        }
        
        createNotification($userId, 'reminder', 'Upcoming Reminders', $message);
    }
}

echo "\nReminder process completed!\n";
