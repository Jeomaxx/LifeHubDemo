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
    $integrations = $db->fetchAll(
        "SELECT * FROM integration_connections WHERE user_id = ? ORDER BY created_at DESC",
        [$userId]
    );

    echo json_encode([
        'success' => true,
        'integrations' => $integrations
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch integrations'
    ]);
}
