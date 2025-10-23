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
    $entries = $db->fetchAll(
        "SELECT * FROM journal_entries WHERE user_id = ? ORDER BY entry_date DESC, entry_time DESC LIMIT 20",
        [$userId]
    );

    echo json_encode([
        'success' => true,
        'entries' => $entries
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch journal entries'
    ]);
}
