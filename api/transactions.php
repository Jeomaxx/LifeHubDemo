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

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$type = $_GET['type'] ?? 'accounts'; // accounts or transactions

try {
    switch ($method) {
        case 'GET':
            if ($type === 'accounts') {
                $accounts = $db->fetchAll(
                    "SELECT * FROM accounts WHERE user_id = ? ORDER BY created_at DESC",
                    [$userId]
                );
                echo json_encode(['success' => true, 'accounts' => $accounts]);
            } elseif ($type === 'transactions') {
                $accountId = $_GET['account_id'] ?? null;
                $limit = $_GET['limit'] ?? 50;
                
                if ($accountId) {
                    $transactions = $db->fetchAll(
                        "SELECT * FROM transactions WHERE user_id = ? AND account_id = ? ORDER BY transaction_date DESC, created_at DESC LIMIT ?",
                        [$userId, $accountId, $limit]
                    );
                } else {
                    $transactions = $db->fetchAll(
                        "SELECT t.*, a.account_name FROM transactions t 
                        LEFT JOIN accounts a ON t.account_id = a.id 
                        WHERE t.user_id = ? ORDER BY t.transaction_date DESC, t.created_at DESC LIMIT ?",
                        [$userId, $limit]
                    );
                }
                echo json_encode(['success' => true, 'transactions' => $transactions]);
            } elseif ($action === 'summary') {
                $accountId = $_GET['account_id'] ?? null;
                
                if ($accountId) {
                    $summary = $db->fetchOne(
                        "SELECT 
                            COALESCE(SUM(CASE WHEN transaction_type = 'income' THEN amount ELSE 0 END), 0) as total_income,
                            COALESCE(SUM(CASE WHEN transaction_type = 'expense' THEN amount ELSE 0 END), 0) as total_expense,
                            COUNT(*) as transaction_count
                        FROM transactions WHERE user_id = ? AND account_id = ?",
                        [$userId, $accountId]
                    );
                } else {
                    $summary = $db->fetchOne(
                        "SELECT 
                            COALESCE(SUM(CASE WHEN transaction_type = 'income' THEN amount ELSE 0 END), 0) as total_income,
                            COALESCE(SUM(CASE WHEN transaction_type = 'expense' THEN amount ELSE 0 END), 0) as total_expense,
                            COUNT(*) as transaction_count
                        FROM transactions WHERE user_id = ?",
                        [$userId]
                    );
                }
                echo json_encode(['success' => true, 'summary' => $summary]);
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            if ($type === 'accounts' && $action === 'create') {
                $id = $db->insert('accounts', [
                    'user_id' => $userId,
                    'account_name' => $data['account_name'],
                    'account_type' => $data['account_type'],
                    'balance' => $data['balance'] ?? 0,
                    'currency' => $data['currency'] ?? 'USD',
                    'is_active' => $data['is_active'] ?? true
                ]);
                echo json_encode(['success' => true, 'id' => $id, 'message' => 'Account created successfully']);
            } elseif ($type === 'transactions' && $action === 'create') {
                $id = $db->insert('transactions', [
                    'user_id' => $userId,
                    'account_id' => $data['account_id'] ?? null,
                    'transaction_type' => $data['transaction_type'],
                    'amount' => $data['amount'],
                    'category' => $data['category'] ?? null,
                    'description' => $data['description'] ?? null,
                    'transaction_date' => $data['transaction_date']
                ]);
                
                // Update account balance if account_id is provided
                if (isset($data['account_id'])) {
                    if ($data['transaction_type'] === 'income') {
                        $db->execute(
                            "UPDATE accounts SET balance = balance + ? WHERE id = ? AND user_id = ?",
                            [$data['amount'], $data['account_id'], $userId]
                        );
                    } else {
                        $db->execute(
                            "UPDATE accounts SET balance = balance - ? WHERE id = ? AND user_id = ?",
                            [$data['amount'], $data['account_id'], $userId]
                        );
                    }
                }
                
                echo json_encode(['success' => true, 'id' => $id, 'message' => 'Transaction added successfully']);
            }
            break;

        case 'DELETE':
            if ($type === 'accounts' && isset($_GET['id'])) {
                $db->execute("DELETE FROM accounts WHERE id = ? AND user_id = ?", [$_GET['id'], $userId]);
                echo json_encode(['success' => true, 'message' => 'Account deleted successfully']);
            } elseif ($type === 'transactions' && isset($_GET['id'])) {
                // Get transaction details before deleting
                $transaction = $db->fetchOne(
                    "SELECT * FROM transactions WHERE id = ? AND user_id = ?",
                    [$_GET['id'], $userId]
                );
                
                if ($transaction && $transaction['account_id']) {
                    // Reverse the balance change
                    if ($transaction['transaction_type'] === 'income') {
                        $db->execute(
                            "UPDATE accounts SET balance = balance - ? WHERE id = ? AND user_id = ?",
                            [$transaction['amount'], $transaction['account_id'], $userId]
                        );
                    } else {
                        $db->execute(
                            "UPDATE accounts SET balance = balance + ? WHERE id = ? AND user_id = ?",
                            [$transaction['amount'], $transaction['account_id'], $userId]
                        );
                    }
                }
                
                $db->execute("DELETE FROM transactions WHERE id = ? AND user_id = ?", [$_GET['id'], $userId]);
                echo json_encode(['success' => true, 'message' => 'Transaction deleted successfully']);
            }
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
