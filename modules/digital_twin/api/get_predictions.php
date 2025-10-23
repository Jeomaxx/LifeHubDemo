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
    $predictions = $db->fetchAll(
        "SELECT * FROM digital_twin_predictions WHERE user_id = ? ORDER BY created_at DESC LIMIT 10",
        [$userId]
    );

    echo json_encode([
        'success' => true,
        'predictions' => $predictions
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch predictions'
    ]);
}
