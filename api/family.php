<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = $auth->getUserId();
$db = Database::getInstance();
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            $type = $_GET['type'] ?? 'members';
            
            if ($type === 'members') {
                $members = $db->fetchAll("SELECT * FROM family_members WHERE user_id = ? ORDER BY name", [$userId]);
                echo json_encode(['success' => true, 'data' => $members]);
            } elseif ($type === 'tasks') {
                $tasks = $db->fetchAll("SELECT * FROM household_tasks WHERE user_id = ? ORDER BY due_date", [$userId]);
                echo json_encode(['success' => true, 'data' => $tasks]);
            } elseif ($type === 'expenses') {
                $expenses = $db->fetchAll("SELECT * FROM household_expenses WHERE user_id = ? ORDER BY expense_date DESC", [$userId]);
                echo json_encode(['success' => true, 'data' => $expenses]);
            } elseif ($type === 'grocery') {
                $lists = $db->fetchAll("SELECT * FROM grocery_lists WHERE user_id = ? ORDER BY created_at DESC", [$userId]);
                echo json_encode(['success' => true, 'data' => $lists]);
            }
            break;
            
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $type = $data['type'] ?? 'member';
            
            if ($type === 'member') {
                $id = $db->insert('family_members', [
                    'user_id' => $userId,
                    'name' => $data['name'],
                    'relationship' => $data['relationship'] ?? '',
                    'email' => $data['email'] ?? '',
                    'phone' => $data['phone'] ?? '',
                    'birthday' => $data['birthday'] ?? null,
                    'notes' => $data['notes'] ?? ''
                ]);
                echo json_encode(['success' => true, 'id' => $id]);
            } elseif ($type === 'task') {
                $id = $db->insert('household_tasks', [
                    'user_id' => $userId,
                    'title' => $data['title'],
                    'description' => $data['description'] ?? '',
                    'assigned_to_member_id' => $data['assigned_to_member_id'] ?? null,
                    'due_date' => $data['due_date'] ?? null,
                    'priority' => $data['priority'] ?? 'medium',
                    'category' => $data['category'] ?? ''
                ]);
                echo json_encode(['success' => true, 'id' => $id]);
            } elseif ($type === 'expense') {
                $id = $db->insert('household_expenses', [
                    'user_id' => $userId,
                    'description' => $data['description'],
                    'total_amount' => $data['total_amount'],
                    'expense_date' => $data['expense_date'],
                    'paid_by_member_id' => $data['paid_by_member_id'] ?? null,
                    'category' => $data['category'] ?? '',
                    'split_type' => $data['split_type'] ?? 'equal'
                ]);
                echo json_encode(['success' => true, 'id' => $id]);
            }
            break;
            
        case 'DELETE':
            $id = (int)($_GET['id'] ?? 0);
            $type = $_GET['type'] ?? 'member';
            
            if ($type === 'member') $db->delete('family_members', $id);
            elseif ($type === 'task') $db->delete('household_tasks', $id);
            elseif ($type === 'expense') $db->delete('household_expenses', $id);
            
            echo json_encode(['success' => true]);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
