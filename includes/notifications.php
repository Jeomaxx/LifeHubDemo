<?php

function sendEmail($to, $subject, $message) {
    if (!defined('SMTP_HOST') || !SMTP_HOST) {
        error_log("SMTP not configured, skipping email to: $to");
        return false;
    }
    
    $headers = "From: " . SMTP_FROM_EMAIL . "\r\n";
    $headers .= "Reply-To: " . SMTP_FROM_EMAIL . "\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    $htmlMessage = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #4a90e2; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background: #f9f9f9; }
            .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Life Atlas Organizer</h1>
            </div>
            <div class='content'>
                <p>{$message}</p>
            </div>
            <div class='footer'>
                <p>This is an automated message from Life Atlas Organizer</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return mail($to, $subject, $htmlMessage, $headers);
}

function sendTelegramMessage($chatId, $message) {
    if (!defined('TELEGRAM_BOT_TOKEN') || !TELEGRAM_BOT_TOKEN) {
        error_log("Telegram bot token not configured");
        return false;
    }
    
    $url = "https://api.telegram.org/bot" . TELEGRAM_BOT_TOKEN . "/sendMessage";
    
    $data = [
        'chat_id' => $chatId,
        'text' => $message,
        'parse_mode' => 'HTML'
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return $httpCode === 200;
}

function sendWebPushNotification($userId, $title, $body, $url = null) {
    $db = Database::getInstance();
    $subscriptions = $db->query(
        "SELECT * FROM push_subscriptions WHERE user_id = ?",
        [$userId]
    );
    
    foreach ($subscriptions as $sub) {
        $payload = json_encode([
            'title' => $title,
            'body' => $body,
            'url' => $url ?? '/',
            'icon' => '/assets/images/icon-192x192.png'
        ]);
        
        error_log("Would send web push: {$payload} to user {$userId}");
    }
    
    return true;
}
