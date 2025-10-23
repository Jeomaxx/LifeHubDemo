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

$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$timeline = trim($_POST['timeline'] ?? '1year');
$category = trim($_POST['category'] ?? 'personal');
$targetDate = $_POST['target_date'] ?? null;

if (empty($title)) {
    echo json_encode(['success' => false, 'message' => 'Title is required']);
    exit;
}

try {
    $roadmapId = $db->insert('life_roadmap', [
        'user_id' => $userId,
        'title' => $title,
        'description' => $description,
        'timeline_type' => $timeline,
        'category' => $category,
        'target_date' => $targetDate,
        'progress_percentage' => 0,
        'status' => 'active'
    ]);

    echo json_encode([
        'success' => true,
        'roadmap_id' => $roadmapId,
        'message' => 'Vision plan created successfully'
    ]);
} catch (Exception $e) {
    error_log("Roadmap creation error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to create vision plan'
    ]);
}
