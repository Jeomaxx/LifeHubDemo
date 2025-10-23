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
    $patterns = $db->fetchAll(
        "SELECT * FROM user_behavior_patterns WHERE user_id = ? AND is_active = true ORDER BY detected_at DESC LIMIT 10",
        [$userId]
    );

    echo json_encode([
        'success' => true,
        'patterns' => $patterns
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch patterns'
    ]);
}
