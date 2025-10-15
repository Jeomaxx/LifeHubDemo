<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';

header('Content-Type: application/json');

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = $auth->getUserId();
$db = Database::getInstance();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$jsonData = file_get_contents('php://input');
$importData = json_decode($jsonData, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit;
}

$allowedTables = [
    'assets', 'bills', 'birthdays', 'finance', 'goals', 'habits', 'habit_logs',
    'health', 'hobbies', 'investments', 'journal', 'learning', 'media',
    'medical_records', 'subscriptions', 'tasks', 'crypto_portfolio', 'crypto_alerts'
];

$imported = 0;
$errors = [];

try {
    foreach ($importData as $tableName => $rows) {
        if ($tableName === 'metadata') continue;
        if (!in_array($tableName, $allowedTables)) continue;
        if (empty($rows)) continue;
        
        foreach ($rows as $row) {
            $row['user_id'] = $userId;
            unset($row['id']);
            
            try {
                $db->insert($tableName, $row);
                $imported++;
            } catch (Exception $e) {
                $errors[] = "Error importing to $tableName: " . $e->getMessage();
            }
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => "Successfully imported $imported records",
        'imported' => $imported,
        'errors' => $errors
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Import failed: ' . $e->getMessage()
    ]);
}
?>
