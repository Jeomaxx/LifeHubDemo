<?php
require_once '../../../includes/config.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/db.php';

header('Content-Type: application/json');

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$db = getDB();
$userId = $auth->getUserId();

try {
    $todayMD = date('m-d');
    
    $memories = $db->fetchAll(
        "SELECT * FROM journal_entries 
         WHERE user_id = ? 
         AND TO_CHAR(entry_date, 'MM-DD') = ? 
         AND EXTRACT(YEAR FROM entry_date) < EXTRACT(YEAR FROM CURRENT_DATE)
         ORDER BY entry_date DESC",
        [$userId, $todayMD]
    );

    echo json_encode([
        'success' => true,
        'memories' => $memories
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch memories'
    ]);
}
