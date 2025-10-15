<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once 'api-helper.php';

header('Content-Type: application/json');

$auth = authenticateApiRequest();
$user_id = $auth['user_id'];
$db = Database::getInstance();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $tasks = $db->query(
        "SELECT * FROM tasks WHERE user_id = ? ORDER BY created_at DESC",
        [$user_id]
    );
    sendApiResponse(['tasks' => $tasks]);
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $db->query(
        "INSERT INTO tasks (user_id, title, description, category, priority, due_date, status, created_at) 
         VALUES (?, ?, ?, ?, ?, ?, ?, NOW())",
        [
            $user_id,
            $data['title'],
            $data['description'] ?? '',
            $data['category'] ?? 'general',
            $data['priority'] ?? 'medium',
            $data['due_date'] ?? null,
            $data['status'] ?? 'pending'
        ]
    );
    
    sendApiResponse(['success' => true, 'message' => 'Task created'], 201);
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['id'])) {
        sendApiError('Task ID required');
    }
    
    $db->query(
        "UPDATE tasks SET title = ?, description = ?, category = ?, priority = ?, 
         due_date = ?, status = ?, updated_at = NOW() 
         WHERE id = ? AND user_id = ?",
        [
            $data['title'],
            $data['description'] ?? '',
            $data['category'] ?? 'general',
            $data['priority'] ?? 'medium',
            $data['due_date'] ?? null,
            $data['status'] ?? 'pending',
            $data['id'],
            $user_id
        ]
    );
    
    sendApiResponse(['success' => true, 'message' => 'Task updated']);
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['id'])) {
        sendApiError('Task ID required');
    }
    
    $db->query("DELETE FROM tasks WHERE id = ? AND user_id = ?", [$data['id'], $user_id]);
    sendApiResponse(['success' => true, 'message' => 'Task deleted']);
}
