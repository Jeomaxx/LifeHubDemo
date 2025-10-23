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

try {
    $spaces = $db->fetchAll(
        "SELECT ss.*, 
                (SELECT COUNT(*) FROM shared_space_members WHERE space_id = ss.id) as member_count
         FROM shared_spaces ss
         WHERE ss.owner_user_id = ? OR ss.id IN (
             SELECT space_id FROM shared_space_members WHERE user_id = ?
         )
         ORDER BY ss.created_at DESC",
        [$userId, $userId]
    );

    echo json_encode([
        'success' => true,
        'spaces' => $spaces
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch shared spaces'
    ]);
}
