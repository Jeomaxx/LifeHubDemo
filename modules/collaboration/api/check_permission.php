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

$spaceId = intval($_GET['space_id'] ?? 0);
$permission = trim($_GET['permission'] ?? '');

if ($spaceId <= 0 || empty($permission)) {
    echo json_encode(['success' => false, 'message' => 'Space ID and permission are required']);
    exit;
}

try {
    $space = $db->fetchOne("SELECT * FROM collaboration_spaces WHERE id = ?", [$spaceId]);

    if (!$space) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Space not found']);
        exit;
    }

    if ($space['owner_id'] == $userId) {
        echo json_encode([
            'success' => true,
            'has_permission' => true,
            'role' => 'owner'
        ]);
        exit;
    }

    $member = $db->fetchOne(
        "SELECT * FROM collaboration_members WHERE space_id = ? AND user_id = ?",
        [$spaceId, $userId]
    );

    if (!$member) {
        echo json_encode([
            'success' => true,
            'has_permission' => false,
            'message' => 'Not a member of this space'
        ]);
        exit;
    }

    $permissions = json_decode($member['permissions'], true) ?? [];
    $hasPermission = isset($permissions[$permission]) && $permissions[$permission] === true;

    echo json_encode([
        'success' => true,
        'has_permission' => $hasPermission,
        'role' => $member['role'],
        'all_permissions' => $permissions
    ]);
} catch (Exception $e) {
    error_log("Permission check error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to check permission'
    ]);
}
