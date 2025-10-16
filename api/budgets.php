<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';

header('Content-Type: application/json');

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = $auth->getUserId();
$db = Database::getInstance();
$action = $_GET['action'] ?? '';

if (in_array($action, ['create', 'update', 'delete'])) {
    $csrf = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!$auth->validateCSRFToken($csrf)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
        exit;
    }
}

switch ($action) {
    case 'create':
        $data = json_decode(file_get_contents('php://input'), true);
        $data['user_id'] = $userId;
        $id = $db->insert('budgets', $data);
        echo json_encode(['success' => (bool)$id, 'id' => $id]);
        break;
        
    case 'read':
        $id = $_GET['id'] ?? null;
        if ($id) {
            $budget = $db->fetchOne("SELECT * FROM budgets WHERE id = ? AND user_id = ?", [$id, $userId]);
            echo json_encode(['success' => true, 'data' => $budget]);
        } else {
            $budgets = $db->fetchAll("SELECT * FROM budgets WHERE user_id = ? ORDER BY year DESC, month DESC", [$userId]);
            echo json_encode(['success' => true, 'data' => $budgets]);
        }
        break;
        
    case 'update':
        $id = $_GET['id'] ?? null;
        $data = json_decode(file_get_contents('php://input'), true);
        $data['updated_at'] = date('Y-m-d H:i:s');
        $success = $db->update('budgets', $data, 'id = ? AND user_id = ?', [$id, $userId]);
        echo json_encode(['success' => $success]);
        break;
        
    case 'delete':
        $id = $_GET['id'] ?? null;
        $success = $db->delete('budgets', 'id = ? AND user_id = ?', [$id, $userId]);
        echo json_encode(['success' => $success]);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>
