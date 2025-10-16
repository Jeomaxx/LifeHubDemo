<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

$auth = new Auth();
$auth->requireLogin();

$db = Database::getInstance();
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['id'])) {
        $note = $db->queryOne(
            "SELECT * FROM secure_notes WHERE id = ? AND user_id = ?",
            [$_GET['id'], $user_id]
        );
        echo json_encode($note);
    } else {
        $notes = $db->query(
            "SELECT * FROM secure_notes WHERE user_id = ? ORDER BY updated_at DESC",
            [$user_id]
        );
        echo json_encode($notes);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    try {
        if (!empty($data['id'])) {
            $db->query(
                "UPDATE secure_notes SET title = ?, content = ?, is_encrypted = ?, updated_at = NOW() 
                 WHERE id = ? AND user_id = ?",
                [$data['title'], $data['content'], $data['is_encrypted'], $data['id'], $user_id]
            );
            $db->logActivity($user_id, 'notes', 'update', 'Updated note: ' . $data['title']);
        } else {
            $db->query(
                "INSERT INTO secure_notes (user_id, title, content, is_encrypted, created_at, updated_at) 
                 VALUES (?, ?, ?, ?, NOW(), NOW())",
                [$user_id, $data['title'], $data['content'], $data['is_encrypted']]
            );
            $db->logActivity($user_id, 'notes', 'create', 'Created note: ' . $data['title']);
        }
        
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    try {
        $db->query(
            "DELETE FROM secure_notes WHERE id = ? AND user_id = ?",
            [$data['id'], $user_id]
        );
        
        $db->logActivity($user_id, 'notes', 'delete', 'Deleted note');
        
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
