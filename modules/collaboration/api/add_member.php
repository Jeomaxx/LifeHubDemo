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
$memberEmail = trim($_POST['member_email'] ?? '');
$role = trim($_POST['role'] ?? 'member');

if ($spaceId <= 0 || empty($memberEmail)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$space = $db->fetchOne("SELECT * FROM shared_spaces WHERE id = ? AND owner_user_id = ?", [$spaceId, $userId]);
if (!$space) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Space not found or access denied']);
    exit;
}

$memberUser = $db->fetchOne("SELECT id FROM users WHERE email = ?", [$memberEmail]);
if (!$memberUser) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit;
}

$existingMember = $db->fetchOne("
    SELECT id FROM shared_space_members 
    WHERE space_id = ? AND user_id = ?
", [$spaceId, $memberUser['id']]);

if ($existingMember) {
    echo json_encode(['success' => false, 'message' => 'User is already a member']);
    exit;
}

try {
    $memberId = $db->insert('shared_space_members', [
        'space_id' => $spaceId,
        'user_id' => $memberUser['id'],
        'role' => $role,
        'permissions' => json_encode(['read' => true, 'write' => ($role !== 'viewer')])
    ]);

    echo json_encode([
        'success' => true,
        'member_id' => $memberId,
        'message' => 'Member added successfully'
    ]);
} catch (Exception $e) {
    error_log("Add member error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to add member'
    ]);
}
