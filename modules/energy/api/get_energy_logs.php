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
    $logs = $db->fetchAll(
        "SELECT * FROM energy_logs WHERE user_id = ? AND log_date >= CURRENT_DATE - INTERVAL '30 days' ORDER BY log_date DESC, log_time DESC",
        [$userId]
    );

    echo json_encode([
        'success' => true,
        'logs' => $logs
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch energy logs'
    ]);
}
