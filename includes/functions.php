<?php
// Common utility functions

function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function formatDate($date, $format = 'Y-m-d') {
    if (empty($date)) return '';
    return date($format, strtotime($date));
}

function formatCurrency($amount) {
    return '$' . number_format($amount, 2);
}

function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $difference = time() - $timestamp;
    
    if ($difference < 60) return 'just now';
    if ($difference < 3600) return floor($difference / 60) . ' minutes ago';
    if ($difference < 86400) return floor($difference / 3600) . ' hours ago';
    if ($difference < 604800) return floor($difference / 86400) . ' days ago';
    if ($difference < 2592000) return floor($difference / 604800) . ' weeks ago';
    if ($difference < 31536000) return floor($difference / 2592000) . ' months ago';
    return floor($difference / 31536000) . ' years ago';
}

function sendEmail($to, $subject, $body) {
    if (empty(SMTP_HOST)) {
        error_log("SMTP not configured");
        return false;
    }
    
    $headers = "From: " . SMTP_FROM_EMAIL . "\r\n";
    $headers .= "Reply-To: " . SMTP_FROM_EMAIL . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    return mail($to, $subject, $body, $headers);
}

function sendTelegramMessage($chatId, $message) {
    if (empty(TELEGRAM_BOT_TOKEN) || empty($chatId)) {
        return false;
    }
    
    $url = TELEGRAM_API_URL . TELEGRAM_BOT_TOKEN . '/sendMessage';
    $data = [
        'chat_id' => $chatId,
        'text' => $message,
        'parse_mode' => 'HTML'
    ];
    
    $options = [
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/json',
            'content' => json_encode($data)
        ]
    ];
    
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    
    return $result !== false;
}

function createNotification($userId, $type, $title, $message) {
    $db = Database::getInstance();
    return $db->insert('notifications', [
        'user_id' => $userId,
        'type' => $type,
        'title' => $title,
        'message' => $message
    ]);
}

function getUpcomingBirthdays($userId, $days = 7) {
    $db = Database::getInstance();
    $sql = "SELECT * FROM birthdays 
            WHERE user_id = ? 
            AND EXTRACT(DOY FROM birth_date) BETWEEN EXTRACT(DOY FROM CURRENT_DATE) 
            AND EXTRACT(DOY FROM CURRENT_DATE + INTERVAL '$days days')
            ORDER BY EXTRACT(DOY FROM birth_date)";
    return $db->fetchAll($sql, [$userId]);
}

function getUpcomingBills($userId, $days = 7) {
    $db = Database::getInstance();
    $sql = "SELECT * FROM bills 
            WHERE user_id = ? 
            AND due_date BETWEEN CURRENT_DATE AND CURRENT_DATE + INTERVAL '$days days'
            AND payment_status != 'paid'
            ORDER BY due_date";
    return $db->fetchAll($sql, [$userId]);
}

function calculateProgress($current, $target) {
    if ($target <= 0) return 0;
    $progress = ($current / $target) * 100;
    return min(100, max(0, $progress));
}

function exportToCSV($data, $filename) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    if (!empty($data)) {
        fputcsv($output, array_keys($data[0]));
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
    }
    
    fclose($output);
    exit;
}

function generateBackup($userId) {
    $db = Database::getInstance();
    $backup = [];
    
    $tables = [
        'assets', 'bills', 'birthdays', 'finance', 'goals', 'habits', 
        'health', 'hobbies', 'investments', 'journal', 'learning', 
        'media', 'subscriptions', 'tasks'
    ];
    
    foreach ($tables as $table) {
        $backup[$table] = $db->fetchAll("SELECT * FROM $table WHERE user_id = ?", [$userId]);
    }
    
    $filename = 'backup_' . $userId . '_' . date('Y-m-d_His') . '.json';
    $filepath = BACKUP_PATH . $filename;
    
    if (!is_dir(BACKUP_PATH)) {
        mkdir(BACKUP_PATH, 0755, true);
    }
    
    file_put_contents($filepath, json_encode($backup, JSON_PRETTY_PRINT));
    
    $db->insert('backups', [
        'user_id' => $userId,
        'filename' => $filename,
        'backup_type' => 'manual',
        'file_size' => filesize($filepath)
    ]);
    
    return $filename;
}

function getStats($userId) {
    $db = Database::getInstance();
    
    return [
        'assets' => $db->fetchOne("SELECT COUNT(*) as count FROM assets WHERE user_id = ?", [$userId])['count'],
        'bills' => $db->fetchOne("SELECT COUNT(*) as count FROM bills WHERE user_id = ? AND payment_status != 'paid'", [$userId])['count'],
        'goals' => $db->fetchOne("SELECT COUNT(*) as count FROM goals WHERE user_id = ? AND status = 'active'", [$userId])['count'],
        'tasks' => $db->fetchOne("SELECT COUNT(*) as count FROM tasks WHERE user_id = ? AND status != 'completed'", [$userId])['count'],
        'habits' => $db->fetchOne("SELECT COUNT(*) as count FROM habits WHERE user_id = ?", [$userId])['count'],
        'total_income' => $db->fetchOne("SELECT COALESCE(SUM(amount), 0) as total FROM finance WHERE user_id = ? AND type = 'income' AND EXTRACT(MONTH FROM date) = EXTRACT(MONTH FROM CURRENT_DATE)", [$userId])['total'],
        'total_expense' => $db->fetchOne("SELECT COALESCE(SUM(amount), 0) as total FROM finance WHERE user_id = ? AND type = 'expense' AND EXTRACT(MONTH FROM date) = EXTRACT(MONTH FROM CURRENT_DATE)", [$userId])['total']
    ];
}
