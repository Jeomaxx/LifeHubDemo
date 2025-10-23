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

$roadmapId = intval($_POST['roadmap_id'] ?? 0);
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$dueDate = $_POST['due_date'] ?? null;

if ($roadmapId <= 0 || empty($title)) {
    echo json_encode(['success' => false, 'message' => 'Roadmap ID and title are required']);
    exit;
}

$roadmap = $db->fetchOne("SELECT * FROM life_roadmap WHERE id = ? AND user_id = ?", [$roadmapId, $userId]);
if (!$roadmap) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Roadmap not found']);
    exit;
}

try {
    $milestoneId = $db->insert('roadmap_milestones', [
        'roadmap_id' => $roadmapId,
        'title' => $title,
        'description' => $description,
        'due_date' => $dueDate,
        'is_completed' => false
    ]);

    echo json_encode([
        'success' => true,
        'milestone_id' => $milestoneId,
        'message' => 'Milestone created successfully'
    ]);
} catch (Exception $e) {
    error_log("Milestone creation error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to create milestone'
    ]);
}
