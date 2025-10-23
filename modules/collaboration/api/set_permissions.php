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
$memberId = intval($_POST['member_id'] ?? 0);
$role = trim($_POST['role'] ?? 'viewer');
$permissions = $_POST['permissions'] ?? [];

if ($spaceId <= 0 || $memberId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Space ID and member ID are required']);
    exit;
}

try {
    $space = $db->fetchOne(
        "SELECT * FROM collaboration_spaces WHERE id = ? AND owner_id = ?",
        [$spaceId, $userId]
    );

    if (!$space) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Only space owners can modify permissions']);
        exit;
    }

    $member = $db->fetchOne(
        "SELECT * FROM collaboration_members WHERE id = ? AND space_id = ?",
        [$memberId, $spaceId]
    );

    if (!$member) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Member not found in this space']);
        exit;
    }

    $rolePermissions = defineRolePermissions($role);

    if (is_array($permissions) && !empty($permissions)) {
        $customPermissions = $permissions;
    } else {
        $customPermissions = $rolePermissions;
    }

    $db->update('collaboration_members', [
        'role' => $role,
        'permissions' => json_encode($customPermissions)
    ], 'id = ?', [$memberId]);

    echo json_encode([
        'success' => true,
        'message' => 'Permissions updated successfully',
        'role' => $role,
        'permissions' => $customPermissions
    ]);
} catch (Exception $e) {
    error_log("Permission setting error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to set permissions'
    ]);
}

function defineRolePermissions($role) {
    $permissions = [
        'owner' => [
            'view' => true,
            'create' => true,
            'edit' => true,
            'delete' => true,
            'manage_members' => true,
            'manage_permissions' => true,
            'delete_space' => true
        ],
        'admin' => [
            'view' => true,
            'create' => true,
            'edit' => true,
            'delete' => true,
            'manage_members' => true,
            'manage_permissions' => false,
            'delete_space' => false
        ],
        'editor' => [
            'view' => true,
            'create' => true,
            'edit' => true,
            'delete' => false,
            'manage_members' => false,
            'manage_permissions' => false,
            'delete_space' => false
        ],
        'contributor' => [
            'view' => true,
            'create' => true,
            'edit' => false,
            'delete' => false,
            'manage_members' => false,
            'manage_permissions' => false,
            'delete_space' => false
        ],
        'viewer' => [
            'view' => true,
            'create' => false,
            'edit' => false,
            'delete' => false,
            'manage_members' => false,
            'manage_permissions' => false,
            'delete_space' => false
        ]
    ];

    return $permissions[$role] ?? $permissions['viewer'];
}
