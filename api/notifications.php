<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';

header('Content-Type: application/json');

$auth = new Auth();

if (!$auth->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = $auth->getUserId();
$db = Database::getInstance();

// Handle different request methods
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    
    // Verify CSRF token
    if (!isset($input['csrf_token']) || !$auth->validateCSRFToken($input['csrf_token'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
        exit;
    }
    
    if ($action === 'mark_read') {
        $notificationId = (int)($input['id'] ?? 0);
        $db->query(
            "UPDATE notifications SET is_read = TRUE WHERE id = ? AND user_id = ?",
            [$notificationId, $userId]
        );
        $count = $db->fetchOne(
            "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = FALSE",
            [$userId]
        );
        echo json_encode([
            'success' => true,
            'message' => 'Notification marked as read',
            'count' => $count['count'] ?? 0
        ]);
        exit;
    }
    
    if ($action === 'mark_all_read') {
        $db->query("UPDATE notifications SET is_read = TRUE WHERE user_id = ?", [$userId]);
        echo json_encode([
            'success' => true,
            'message' => 'All notifications marked as read',
            'count' => 0
        ]);
        exit;
    }
    
    if ($action === 'delete') {
        $notificationId = (int)($input['id'] ?? 0);
        $db->query("DELETE FROM notifications WHERE id = ? AND user_id = ?", [$notificationId, $userId]);
        $count = $db->fetchOne(
            "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = FALSE",
            [$userId]
        );
        echo json_encode([
            'success' => true,
            'message' => 'Notification deleted',
            'count' => $count['count'] ?? 0
        ]);
        exit;
    }
    
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit;
}

// GET request - return count
$count = $db->fetchOne(
    "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = FALSE",
    [$userId]
);

echo json_encode([
    'success' => true,
    'count' => $count['count'] ?? 0
]);
