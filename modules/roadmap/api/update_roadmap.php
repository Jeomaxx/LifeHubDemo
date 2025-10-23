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

if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

$db = getDB();
$userId = $auth->getUserId();

$roadmapId = intval($_POST['id'] ?? 0);
$progressPercentage = intval($_POST['progress_percentage'] ?? 0);
$status = trim($_POST['status'] ?? 'active');

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
    $updateData = [
        'progress_percentage' => max(0, min(100, $progressPercentage)),
        'status' => $status
    ];

    if (isset($_POST['title'])) $updateData['title'] = trim($_POST['title']);
    if (isset($_POST['description'])) $updateData['description'] = trim($_POST['description']);
    if (isset($_POST['target_date'])) $updateData['target_date'] = $_POST['target_date'];

    $db->update('life_roadmap', $updateData, 'id = ? AND user_id = ?', [$roadmapId, $userId]);

    echo json_encode([
        'success' => true,
        'message' => 'Roadmap updated successfully'
    ]);
} catch (Exception $e) {
    error_log("Roadmap update error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update roadmap'
    ]);
}
