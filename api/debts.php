<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'get':
            $debts = $db->fetchAll("SELECT * FROM debts WHERE user_id = ? ORDER BY priority_order, current_balance DESC", [$userId]);
            jsonResponse(['success' => true, 'debts' => $debts]);
            break;
            
        case 'add':
            if ($method !== 'POST') jsonResponse(['success' => false, 'message' => 'Invalid method'], 405);
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            $id = $db->insert('debts', [
                'user_id' => $userId,
                'name' => sanitize($data['name']),
                'debt_type' => $data['debt_type'],
                'principal_amount' => $data['principal_amount'],
                'current_balance' => $data['current_balance'],
                'interest_rate' => $data['interest_rate'],
                'minimum_payment' => $data['minimum_payment'],
                'payment_due_day' => $data['payment_due_day'] ?? null,
                'start_date' => $data['start_date'] ?? date('Y-m-d'),
                'payoff_strategy' => $data['payoff_strategy'] ?? 'snowball',
                'priority_order' => $data['priority_order'] ?? 0
            ]);
            
            jsonResponse(['success' => true, 'id' => $id, 'message' => 'Debt added successfully']);
            break;
            
        case 'update':
            if ($method !== 'POST') jsonResponse(['success' => false, 'message' => 'Invalid method'], 405);
            
            $data = json_decode(file_get_contents('php://input'), true);
            $id = (int)$data['id'];
            
            $db->update('debts', [
                'name' => sanitize($data['name']),
                'debt_type' => $data['debt_type'],
                'principal_amount' => $data['principal_amount'],
                'current_balance' => $data['current_balance'],
                'interest_rate' => $data['interest_rate'],
                'minimum_payment' => $data['minimum_payment'],
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = ? AND user_id = ?', [$id, $userId]);
            
            jsonResponse(['success' => true, 'message' => 'Debt updated successfully']);
            break;
            
        case 'delete':
            if ($method !== 'POST') jsonResponse(['success' => false, 'message' => 'Invalid method'], 405);
            
            $data = json_decode(file_get_contents('php://input'), true);
            $id = (int)$data['id'];
            
            $db->query("DELETE FROM debts WHERE id = ? AND user_id = ?", [$id, $userId]);
            jsonResponse(['success' => true, 'message' => 'Debt deleted successfully']);
            break;
            
        case 'record_payment':
            if ($method !== 'POST') jsonResponse(['success' => false, 'message' => 'Invalid method'], 405);
            
            $data = json_decode(file_get_contents('php://input'), true);
            $debtId = (int)$data['debt_id'];
            $amount = (float)$data['amount'];
            
            $debt = $db->fetchOne("SELECT * FROM debts WHERE id = ? AND user_id = ?", [$debtId, $userId]);
            if (!$debt) jsonResponse(['success' => false, 'message' => 'Debt not found'], 404);
            
            $newBalance = $debt['current_balance'] - $amount;
            $interestPortion = ($debt['current_balance'] * ($debt['interest_rate'] / 100)) / 12;
            $principalPortion = $amount - $interestPortion;
            
            $db->insert('debt_payments', [
                'debt_id' => $debtId,
                'user_id' => $userId,
                'payment_amount' => $amount,
                'payment_date' => $data['payment_date'] ?? date('Y-m-d'),
                'principal_paid' => $principalPortion,
                'interest_paid' => $interestPortion,
                'remaining_balance' => $newBalance,
                'notes' => sanitize($data['notes'] ?? '')
            ]);
            
            $db->update('debts', [
                'current_balance' => $newBalance,
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$debtId]);
            
            jsonResponse(['success' => true, 'message' => 'Payment recorded successfully', 'new_balance' => $newBalance]);
            break;
            
        default:
            jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
    }
} catch (Exception $e) {
    error_log("Debts API Error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'An error occurred'], 500);
}
