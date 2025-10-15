#!/usr/bin/env php
<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/notifications.php';

$db = Database::getInstance();

$today = date('Y-m-d');
$tomorrow = date('Y-m-d', strtotime('+1 day'));

$upcomingBills = $db->query(
    "SELECT b.*, u.email, u.telegram_chat_id, u.name 
     FROM bills b
     JOIN users u ON b.user_id = u.id
     WHERE b.next_due_date <= ? AND b.paid = 0",
    [$tomorrow]
);

foreach ($upcomingBills as $bill) {
    $daysUntil = (strtotime($bill['next_due_date']) - strtotime($today)) / 86400;
    $message = "Reminder: Bill '{$bill['title']}' of \${$bill['amount']} is due " . 
               ($daysUntil == 0 ? 'today' : 'tomorrow') . "!";
    
    sendEmail($bill['email'], 'Bill Reminder - Life Atlas', $message);
    
    if ($bill['telegram_chat_id']) {
        sendTelegramMessage($bill['telegram_chat_id'], $message);
    }
    
    echo "Sent reminder for bill: {$bill['title']}\n";
}

$upcomingBirthdays = $db->query(
    "SELECT b.*, u.email, u.telegram_chat_id, u.name 
     FROM birthdays b
     JOIN users u ON b.user_id = u.id
     WHERE DATE_FORMAT(b.date, '%m-%d') = DATE_FORMAT(?, '%m-%d')",
    [$tomorrow]
);

foreach ($upcomingBirthdays as $birthday) {
    $message = "Reminder: {$birthday['name']}'s birthday is tomorrow!";
    
    sendEmail($birthday['email'], 'Birthday Reminder - Life Atlas', $message);
    
    if ($birthday['telegram_chat_id']) {
        sendTelegramMessage($birthday['telegram_chat_id'], $message);
    }
    
    echo "Sent birthday reminder for: {$birthday['name']}\n";
}

$cryptoAlerts = $db->query(
    "SELECT ca.*, cc.name, cc.symbol, u.email, u.telegram_chat_id,
            (SELECT price_usd FROM crypto_price_history WHERE coin_id = ca.coin_id ORDER BY timestamp DESC LIMIT 1) as current_price
     FROM crypto_alerts ca
     JOIN crypto_coins cc ON ca.coin_id = cc.id
     JOIN users u ON ca.user_id = u.id
     WHERE ca.active = 1"
);

foreach ($cryptoAlerts as $alert) {
    $triggered = false;
    $message = '';
    
    if ($alert['direction'] === 'above' && $alert['current_price'] >= $alert['target_price']) {
        $triggered = true;
        $message = "{$alert['name']} ({$alert['symbol']}) has reached \${$alert['current_price']}! (Target: \${$alert['target_price']})";
    } elseif ($alert['direction'] === 'below' && $alert['current_price'] <= $alert['target_price']) {
        $triggered = true;
        $message = "{$alert['name']} ({$alert['symbol']}) has dropped to \${$alert['current_price']}! (Target: \${$alert['target_price']})";
    }
    
    if ($triggered) {
        sendEmail($alert['email'], 'Crypto Price Alert - Life Atlas', $message);
        
        if ($alert['telegram_chat_id']) {
            sendTelegramMessage($alert['telegram_chat_id'], $message);
        }
        
        $db->query("UPDATE crypto_alerts SET active = 0, triggered_at = NOW() WHERE id = ?", [$alert['id']]);
        
        echo "Sent crypto alert: {$message}\n";
    }
}

$upcomingSubscriptions = $db->query(
    "SELECT s.*, u.email, u.telegram_chat_id 
     FROM subscriptions s
     JOIN users u ON s.user_id = u.id
     WHERE s.next_billing_date <= ? AND s.status = 'active'",
    [$tomorrow]
);

foreach ($upcomingSubscriptions as $sub) {
    $message = "Reminder: Your subscription to '{$sub['name']}' will renew tomorrow for \${$sub['amount']}";
    
    sendEmail($sub['email'], 'Subscription Renewal Reminder - Life Atlas', $message);
    
    if ($sub['telegram_chat_id']) {
        sendTelegramMessage($sub['telegram_chat_id'], $message);
    }
    
    echo "Sent subscription reminder for: {$sub['name']}\n";
}

echo "Reminders completed at " . date('Y-m-d H:i:s') . "\n";
