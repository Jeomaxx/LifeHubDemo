<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';

header('Content-Type: application/json');

$auth = new Auth();

if (!$auth->isLoggedIn()) {
    echo json_encode(['success' => false, 'count' => 0]);
    exit;
}

$userId = $auth->getUserId();
$db = Database::getInstance();

$count = $db->fetchOne(
    "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = FALSE",
    [$userId]
);

echo json_encode([
    'success' => true,
    'count' => $count['count'] ?? 0
]);
