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
    $assets = $db->fetchAll(
        "SELECT * FROM owned_assets WHERE user_id = ? ORDER BY purchase_date DESC",
        [$userId]
    );

    echo json_encode([
        'success' => true,
        'assets' => $assets
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch assets'
    ]);
}
