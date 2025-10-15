<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

$auth = new Auth();

if (!$auth->isLoggedIn()) {
    jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
}

$userId = $auth->getUserId();
$db = Database::getInstance();

$action = $_GET['action'] ?? '';
$module = $_GET['module'] ?? '';

if (in_array($action, ['create', 'update', 'delete'])) {
    $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? '';
    
    if (!$auth->validateCSRFToken($csrfToken)) {
        jsonResponse(['success' => false, 'message' => 'Invalid security token'], 403);
    }
}

$allowedModules = ['assets', 'bills', 'birthdays', 'finance', 'goals', 'habits', 'health', 'hobbies', 'investments', 'journal', 'learning', 'media', 'subscriptions', 'tasks'];

if (!in_array($module, $allowedModules)) {
    jsonResponse(['success' => false, 'message' => 'Invalid module'], 400);
}

switch ($action) {
    case 'create':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $data['user_id'] = $userId;
        
        $id = $db->insert($module, $data);
        
        if ($id) {
            jsonResponse(['success' => true, 'message' => 'Created successfully', 'id' => $id]);
        } else {
            jsonResponse(['success' => false, 'message' => 'Failed to create'], 500);
        }
        break;
        
    case 'read':
        $id = $_GET['id'] ?? null;
        
        if ($id) {
            $item = $db->fetchOne("SELECT * FROM $module WHERE id = ? AND user_id = ?", [$id, $userId]);
            jsonResponse(['success' => true, 'data' => $item]);
        } else {
            $items = $db->fetchAll("SELECT * FROM $module WHERE user_id = ? ORDER BY created_at DESC", [$userId]);
            jsonResponse(['success' => true, 'data' => $items]);
        }
        break;
        
    case 'update':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? null;
        unset($data['id'], $data['user_id']);
        
        if (!$id) {
            jsonResponse(['success' => false, 'message' => 'ID required'], 400);
        }
        
        $data['updated_at'] = date('Y-m-d H:i:s');
        $updated = $db->update($module, $data, 'id = :id AND user_id = :user_id', [':id' => $id, ':user_id' => $userId]);
        
        if ($updated) {
            jsonResponse(['success' => true, 'message' => 'Updated successfully']);
        } else {
            jsonResponse(['success' => false, 'message' => 'Failed to update'], 500);
        }
        break;
        
    case 'delete':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? null;
        
        if (!$id) {
            jsonResponse(['success' => false, 'message' => 'ID required'], 400);
        }
        
        $deleted = $db->delete($module, 'id = :id AND user_id = :user_id', [':id' => $id, ':user_id' => $userId]);
        
        if ($deleted) {
            jsonResponse(['success' => true, 'message' => 'Deleted successfully']);
        } else {
            jsonResponse(['success' => false, 'message' => 'Failed to delete'], 500);
        }
        break;
        
    default:
        jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
}
