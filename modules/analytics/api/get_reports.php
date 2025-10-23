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
    $reports = $db->fetchAll(
        "SELECT * FROM life_reports WHERE user_id = ? ORDER BY report_period DESC LIMIT 10",
        [$userId]
    );

    echo json_encode([
        'success' => true,
        'reports' => $reports
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch reports'
    ]);
}
