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

$spaceId = intval($_POST['space_id'] ?? 0);
$taskTitle = trim($_POST['task_title'] ?? '');
$taskDescription = trim($_POST['task_description'] ?? '');
$assignedToId = intval($_POST['assigned_to'] ?? 0);

if ($spaceId <= 0 || empty($taskTitle)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$isMember = $db->fetchOne("
    SELECT id FROM shared_space_members 
    WHERE space_id = ? AND user_id = ?
", [$spaceId, $userId]);

if (!$isMember) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Not a member of this space']);
    exit;
}

try {
    $taskId = $db->insert('shared_tasks', [
        'space_id' => $spaceId,
        'task_title' => $taskTitle,
        'task_description' => $taskDescription,
        'assigned_to' => $assignedToId > 0 ? $assignedToId : null,
        'created_by' => $userId,
        'status' => 'pending'
    ]);

    echo json_encode([
        'success' => true,
        'task_id' => $taskId,
        'message' => 'Task created successfully'
    ]);
} catch (Exception $e) {
    error_log("Create task error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to create task'
    ]);
}
