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
$timeline = $_GET['timeline'] ?? '1year';

try {
    $roadmaps = $db->fetchAll(
        "SELECT * FROM life_roadmap WHERE user_id = ? AND roadmap_type = ? ORDER BY created_at DESC",
        [$userId, $timeline]
    );

    echo json_encode([
        'success' => true,
        'roadmaps' => $roadmaps
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch roadmaps'
    ]);
}
