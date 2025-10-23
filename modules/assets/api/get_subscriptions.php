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
    $subscriptions = $db->fetchAll(
        "SELECT * FROM subscriptions WHERE user_id = ? AND is_active = true ORDER BY cost DESC",
        [$userId]
    );

    echo json_encode([
        'success' => true,
        'subscriptions' => $subscriptions
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch subscriptions'
    ]);
}
