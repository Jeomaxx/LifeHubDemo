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
    $reminders = $db->fetchAll(
        "SELECT * FROM smart_reminders WHERE user_id = ? ORDER BY created_at DESC",
        [$userId]
    );

    echo json_encode([
        'success' => true,
        'reminders' => $reminders
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch reminders'
    ]);
}
