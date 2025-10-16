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
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Validate required fields
        if (empty($input['budget_name']) || empty($input['category']) || !isset($input['category_limit'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Budget name, category, and limit are required']);
            exit;
        }
        
        if (!isset($input['month']) || !isset($input['year'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Month and year are required']);
            exit;
        }
        
        // Validate data types and ranges
        $month = (int)$input['month'];
        $year = (int)$input['year'];
        
        if ($month < 1 || $month > 12) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Month must be between 1 and 12']);
            exit;
        }
        
        if ($year < 2000 || $year > 2100) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid year']);
            exit;
        }
        
        // Whitelist allowed fields only
        $data = [
            'user_id' => $userId,
            'budget_name' => $input['budget_name'],
            'month' => $month,
            'year' => $year,
            'total_budget' => (float)($input['total_budget'] ?? 0),
            'category' => $input['category'],
            'category_limit' => (float)$input['category_limit'],
            'spent_amount' => 0.00,
            'notes' => $input['notes'] ?? null,
            'is_active' => isset($input['is_active']) ? (bool)$input['is_active'] : true
        ];
        
        $id = $db->insert('budgets', $data);
        echo json_encode(['success' => (bool)$id, 'id' => $id, 'message' => 'Budget created successfully']);
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
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }
        
        $id = $_GET['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Budget ID is required']);
            exit;
        }
        
        // Verify ownership
        $existing = $db->fetchOne("SELECT id FROM budgets WHERE id = ? AND user_id = ?", [$id, $userId]);
        if (!$existing) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Budget not found']);
            exit;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Validate month and year if provided
        if (isset($input['month'])) {
            $month = (int)$input['month'];
            if ($month < 1 || $month > 12) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Month must be between 1 and 12']);
                exit;
            }
        }
        
        if (isset($input['year'])) {
            $year = (int)$input['year'];
            if ($year < 2000 || $year > 2100) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid year']);
                exit;
            }
        }
        
        // Whitelist allowed fields only
        $data = [];
        $allowedFields = ['budget_name', 'month', 'year', 'total_budget', 'category', 'category_limit', 'notes', 'is_active'];
        
        foreach ($allowedFields as $field) {
            if (isset($input[$field])) {
                if (in_array($field, ['month', 'year'])) {
                    $data[$field] = (int)$input[$field];
                } elseif (in_array($field, ['total_budget', 'category_limit'])) {
                    $data[$field] = (float)$input[$field];
                } elseif ($field === 'is_active') {
                    $data[$field] = (bool)$input[$field];
                } else {
                    $data[$field] = $input[$field];
                }
            }
        }
        
        // Ensure at least one field is being updated
        if (empty($data)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'No valid fields to update']);
            exit;
        }
        
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        $success = $db->update('budgets', $data, 'id = ? AND user_id = ?', [$id, $userId]);
        echo json_encode(['success' => $success, 'message' => 'Budget updated successfully']);
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
