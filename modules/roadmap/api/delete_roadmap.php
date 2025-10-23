<?php
require_once '../../../includes/config.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/db.php';
require_once '../../../includes/functions.php';

header('Content-Type: application/json');

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

$db = getDB();
$userId = $auth->getUserId();

$roadmapId = intval($_POST['id'] ?? $_GET['id'] ?? 0);

if ($roadmapId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid roadmap ID']);
    exit;
}

$existing = $db->fetchOne("SELECT * FROM life_roadmap WHERE id = ? AND user_id = ?", [$roadmapId, $userId]);
if (!$existing) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Roadmap not found']);
    exit;
}

try {
    $db->delete('roadmap_milestones', 'roadmap_id = ?', [$roadmapId]);
    $db->delete('life_roadmap', 'id = ? AND user_id = ?', [$roadmapId, $userId]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Roadmap deleted successfully'
    ]);
} catch (Exception $e) {
    error_log("Roadmap deletion error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to delete roadmap'
    ]);
}
