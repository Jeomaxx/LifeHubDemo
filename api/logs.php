<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

$auth = new Auth();
$auth->requireLogin();

$db = Database::getInstance();
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    try {
        $db->query("DELETE FROM activity_logs WHERE user_id = ?", [$user_id]);
        
        $db->logActivity($user_id, 'system', 'delete', 'Cleared all activity logs');
        
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
