<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

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

// CSRF validation for state-changing operations
if (in_array($action, ['create', 'update', 'delete'])) {
    $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? '';
    
    if (!$auth->validateCSRFToken($csrfToken)) {
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
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validate required fields
        if (empty($data['account_name']) || empty($data['account_type'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Account name and type are required']);
            exit;
        }
        
        // Validate account type
        $validTypes = ['checking', 'savings', 'credit_card', 'investment', 'cash', 'other'];
        if (!in_array($data['account_type'], $validTypes)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid account type']);
            exit;
        }
        
        $accountData = [
            'user_id' => $userId,
            'account_name' => $data['account_name'],
            'account_type' => $data['account_type'],
            'bank_name' => $data['bank_name'] ?? null,
            'account_number_last4' => $data['account_number_last4'] ?? null,
            'currency' => $data['currency'] ?? 'USD',
            'current_balance' => (float)($data['current_balance'] ?? 0),
            'credit_limit' => isset($data['credit_limit']) ? (float)$data['credit_limit'] : null,
            'interest_rate' => isset($data['interest_rate']) ? (float)$data['interest_rate'] : null,
            'is_active' => (bool)($data['is_active'] ?? true),
            'notes' => $data['notes'] ?? null
        ];
        
        $id = $db->insert('financial_accounts', $accountData);
        
        if ($id) {
            echo json_encode([
                'success' => true,
                'message' => 'Account created successfully',
                'id' => $id
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to create account']);
        }
        break;
        
    case 'read':
        $id = $_GET['id'] ?? null;
        
        if ($id) {
            $account = $db->fetchOne(
                "SELECT * FROM financial_accounts WHERE id = ? AND user_id = ?",
                [$id, $userId]
            );
            
            if ($account) {
                echo json_encode(['success' => true, 'data' => $account]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Account not found']);
            }
        } else {
            $accounts = $db->fetchAll(
                "SELECT * FROM financial_accounts WHERE user_id = ? ORDER BY account_type, created_at DESC",
                [$userId]
            );
            
            echo json_encode(['success' => true, 'data' => $accounts]);
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
            echo json_encode(['success' => false, 'message' => 'Account ID is required']);
            exit;
        }
        
        // Verify ownership
        $existing = $db->fetchOne(
            "SELECT id FROM financial_accounts WHERE id = ? AND user_id = ?",
            [$id, $userId]
        );
        
        if (!$existing) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Account not found']);
            exit;
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validate required fields
        if (empty($data['account_name']) || empty($data['account_type'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Account name and type are required']);
            exit;
        }
        
        // Validate account type
        $validTypes = ['checking', 'savings', 'credit_card', 'investment', 'cash', 'other'];
        if (!in_array($data['account_type'], $validTypes)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid account type']);
            exit;
        }
        
        $updateData = [
            'account_name' => $data['account_name'],
            'account_type' => $data['account_type'],
            'bank_name' => $data['bank_name'] ?? null,
            'account_number_last4' => $data['account_number_last4'] ?? null,
            'currency' => $data['currency'] ?? 'USD',
            'current_balance' => (float)($data['current_balance'] ?? 0),
            'credit_limit' => isset($data['credit_limit']) ? (float)$data['credit_limit'] : null,
            'interest_rate' => isset($data['interest_rate']) ? (float)$data['interest_rate'] : null,
            'is_active' => (bool)($data['is_active'] ?? true),
            'notes' => $data['notes'] ?? null,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $success = $db->update('financial_accounts', $updateData, 'id = ?', [$id]);
        
        if ($success) {
            echo json_encode([
                'success' => true,
                'message' => 'Account updated successfully'
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to update account']);
        }
        break;
        
    case 'delete':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }
        
        $id = $_GET['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Account ID is required']);
            exit;
        }
        
        // Verify ownership
        $existing = $db->fetchOne(
            "SELECT id FROM financial_accounts WHERE id = ? AND user_id = ?",
            [$id, $userId]
        );
        
        if (!$existing) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Account not found']);
            exit;
        }
        
        $success = $db->delete('financial_accounts', 'id = ? AND user_id = ?', [$id, $userId]);
        
        if ($success) {
            echo json_encode([
                'success' => true,
                'message' => 'Account deleted successfully'
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to delete account']);
        }
        break;
        
    case 'stats':
        $stats = $db->fetchAll("
            SELECT 
                account_type,
                COUNT(*) as count,
                SUM(current_balance) as total_balance
            FROM financial_accounts
            WHERE user_id = ? AND is_active = TRUE
            GROUP BY account_type
        ", [$userId]);
        
        echo json_encode(['success' => true, 'data' => $stats]);
        break;
        
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
?>
