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
                $lists = $db->fetchAll("SELECT * FROM grocery_lists WHERE user_id = ? ORDER BY id DESC", [$userId]);
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
            } elseif ($type === 'grocery') {
                $id = $db->insert('grocery_lists', [
                    'user_id' => $userId,
                    'name' => $data['name'] ?? 'Grocery List',
                    'items' => $data['items'] ?? '',
                    'status' => $data['status'] ?? 'active'
                ]);
                echo json_encode(['success' => true, 'id' => $id]);
            }
            break;
            
        case 'DELETE':
            $id = (int)($_GET['id'] ?? 0);
            $type = $_GET['type'] ?? 'member';
            
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid ID']);
                break;
            }
            
            // Delete with user authorization check
            if ($type === 'member') {
                $db->query(
                    "DELETE FROM family_members WHERE id = ? AND user_id = ?",
                    [$id, $userId]
                );
            } elseif ($type === 'task') {
                $db->query(
                    "DELETE FROM household_tasks WHERE id = ? AND user_id = ?",
                    [$id, $userId]
                );
            } elseif ($type === 'expense') {
                $db->query(
                    "DELETE FROM household_expenses WHERE id = ? AND user_id = ?",
                    [$id, $userId]
                );
            } elseif ($type === 'grocery') {
                $db->query(
                    "DELETE FROM grocery_lists WHERE id = ? AND user_id = ?",
                    [$id, $userId]
                );
            }
            
            echo json_encode(['success' => true, 'message' => 'Deleted successfully']);
            break;
            
        case 'PUT':
        case 'PATCH':
            $data = json_decode(file_get_contents('php://input'), true);
            $id = (int)($data['id'] ?? 0);
            $type = $data['type'] ?? 'member';
            
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid ID']);
                break;
            }
            
            if ($type === 'member') {
                $db->update('family_members', [
                    'name' => $data['name'],
                    'relationship' => $data['relationship'] ?? '',
                    'email' => $data['email'] ?? '',
                    'phone' => $data['phone'] ?? '',
                    'birthday' => $data['birthday'] ?? null,
                    'notes' => $data['notes'] ?? ''
                ], 'id = ? AND user_id = ?', [$id, $userId]);
            } elseif ($type === 'task') {
                $db->update('household_tasks', [
                    'title' => $data['title'],
                    'description' => $data['description'] ?? '',
                    'assigned_to_member_id' => $data['assigned_to_member_id'] ?? null,
                    'due_date' => $data['due_date'] ?? null,
                    'priority' => $data['priority'] ?? 'medium',
                    'category' => $data['category'] ?? '',
                    'status' => $data['status'] ?? 'pending'
                ], 'id = ? AND user_id = ?', [$id, $userId]);
            } elseif ($type === 'expense') {
                $db->update('household_expenses', [
                    'description' => $data['description'],
                    'total_amount' => $data['total_amount'],
                    'expense_date' => $data['expense_date'],
                    'paid_by_member_id' => $data['paid_by_member_id'] ?? null,
                    'category' => $data['category'] ?? '',
                    'split_type' => $data['split_type'] ?? 'equal'
                ], 'id = ? AND user_id = ?', [$id, $userId]);
            } elseif ($type === 'grocery') {
                $db->update('grocery_lists', [
                    'name' => $data['name'] ?? 'Grocery List',
                    'items' => $data['items'] ?? '',
                    'status' => $data['status'] ?? 'active'
                ], 'id = ? AND user_id = ?', [$id, $userId]);
            }
            
            echo json_encode(['success' => true, 'message' => 'Updated successfully']);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
} catch (Exception $e) {
    error_log('Family API error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
