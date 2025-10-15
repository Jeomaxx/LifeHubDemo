<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    die('Unauthorized');
}

$userId = $auth->getUserId();
$db = Database::getInstance();

$format = $_GET['format'] ?? 'json';

$tables = [
    'assets', 'bills', 'birthdays', 'finance', 'goals', 'habits', 'habit_logs',
    'health', 'hobbies', 'investments', 'journal', 'learning', 'media',
    'medical_records', 'subscriptions', 'tasks', 'crypto_portfolio', 'crypto_alerts'
];

$exportData = [];

foreach ($tables as $table) {
    $data = $db->fetchAll("SELECT * FROM $table WHERE user_id = ?", [$userId]);
    if (!empty($data)) {
        $exportData[$table] = $data;
    }
}

$exportData['metadata'] = [
    'export_date' => date('Y-m-d H:i:s'),
    'user_id' => $userId,
    'version' => '1.0'
];

if ($format === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="life_atlas_export_' . date('Y-m-d') . '.csv"');
    
    foreach ($exportData as $tableName => $rows) {
        if ($tableName === 'metadata') continue;
        if (empty($rows)) continue;
        
        echo "\n=== $tableName ===\n";
        
        $headers = array_keys($rows[0]);
        echo implode(',', $headers) . "\n";
        
        foreach ($rows as $row) {
            $values = array_map(function($val) {
                return '"' . str_replace('"', '""', $val) . '"';
            }, array_values($row));
            echo implode(',', $values) . "\n";
        }
    }
} else {
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="life_atlas_export_' . date('Y-m-d') . '.json"');
    echo json_encode($exportData, JSON_PRETTY_PRINT);
}
?>
