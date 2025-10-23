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

$name = trim($_POST['name'] ?? '');
$description = trim($_POST['description'] ?? '');
$spaceType = trim($_POST['space_type'] ?? 'family');

if (empty($name)) {
    echo json_encode(['success' => false, 'message' => 'Space name is required']);
    exit;
}

if (strlen($name) > 255) {
    echo json_encode(['success' => false, 'message' => 'Space name too long']);
    exit;
}

try {
    $spaceId = $db->insert('shared_spaces', [
        'name' => $name,
        'description' => $description,
        'owner_user_id' => $userId,
        'space_type' => $spaceType,
        'settings' => json_encode([]),
        'is_active' => true
    ]);

    $db->insert('shared_space_members', [
        'space_id' => $spaceId,
        'user_id' => $userId,
        'role' => 'owner',
        'permissions' => json_encode(['all' => true])
    ]);

    echo json_encode([
        'success' => true,
        'space_id' => $spaceId,
        'message' => 'Shared space created successfully'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to create shared space'
    ]);
}
