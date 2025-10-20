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
$type = $_GET['type'] ?? '';

try {
    switch ($method) {
        case 'GET':
            if ($action === 'stats') {
                $stats = [
                    'my_boards' => $db->fetchColumn("SELECT COUNT(*) FROM team_boards WHERE owner_id = ?", [$userId]) ?: 0,
                    'shared_boards' => $db->fetchColumn("SELECT COUNT(*) FROM team_board_members WHERE user_id = ?", [$userId]) ?: 0,
                    'active_tasks' => $db->fetchColumn("SELECT COUNT(*) FROM team_tasks t JOIN team_boards b ON t.board_id = b.id WHERE (b.owner_id = ? OR t.assigned_to = ?) AND t.status != 'done'", [$userId, $userId]) ?: 0,
                    'team_members' => $db->fetchColumn("SELECT COUNT(DISTINCT user_id) FROM team_board_members WHERE board_id IN (SELECT id FROM team_boards WHERE owner_id = ?)", [$userId]) ?: 0
                ];
                echo json_encode(['success' => true, 'data' => $stats]);
            } elseif ($type === 'boards') {
                $ownedBoards = $db->fetchAll("SELECT *, 'owner' as role FROM team_boards WHERE owner_id = ? ORDER BY created_at DESC", [$userId]);
                
                $sharedBoards = $db->fetchAll("SELECT b.*, m.role FROM team_boards b JOIN team_board_members m ON b.id = m.board_id WHERE m.user_id = ? ORDER BY b.created_at DESC", [$userId]);
                
                $allBoards = array_merge($ownedBoards, $sharedBoards);
                echo json_encode(['success' => true, 'data' => $allBoards]);
            } elseif ($type === 'board-tasks') {
                $boardId = $_GET['board_id'] ?? null;
                if (!$boardId) throw new Exception('Board ID required');
                
                $hasAccess = $db->fetchColumn("SELECT COUNT(*) FROM team_boards WHERE id = ? AND (owner_id = ? OR id IN (SELECT board_id FROM team_board_members WHERE user_id = ?))", [$boardId, $userId, $userId]);
                
                if (!$hasAccess) {
                    throw new Exception('Access denied to this board');
                }
                
                $tasks = $db->fetchAll("SELECT t.*, u.name as assigned_name FROM team_tasks t LEFT JOIN users u ON t.assigned_to = u.id WHERE t.board_id = ? ORDER BY t.created_at DESC", [$boardId]);
                echo json_encode(['success' => true, 'data' => $tasks]);
            } elseif ($type === 'board-members') {
                $boardId = $_GET['board_id'] ?? null;
                if (!$boardId) throw new Exception('Board ID required');
                
                $hasAccess = $db->fetchColumn("SELECT COUNT(*) FROM team_boards WHERE id = ? AND (owner_id = ? OR id IN (SELECT board_id FROM team_board_members WHERE user_id = ?))", [$boardId, $userId, $userId]);
                
                if (!$hasAccess) {
                    throw new Exception('Access denied to this board');
                }
                
                $members = $db->fetchAll("SELECT m.*, u.name, u.email FROM team_board_members m JOIN users u ON m.user_id = u.id WHERE m.board_id = ? ORDER BY m.role DESC", [$boardId]);
                echo json_encode(['success' => true, 'data' => $members]);
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            if ($type === 'board') {
                $id = $db->insert('team_boards', [
                    'owner_id' => $userId,
                    'board_name' => $data['board_name'],
                    'description' => $data['description'] ?? null,
                    'is_private' => $data['is_private'] ?? false
                ]);
                echo json_encode(['success' => true, 'id' => $id, 'message' => 'Board created successfully']);
            } elseif ($type === 'board-member') {
                $boardId = $data['board_id'];
                
                $isOwner = $db->fetchColumn("SELECT COUNT(*) FROM team_boards WHERE id = ? AND owner_id = ?", [$boardId, $userId]);
                if (!$isOwner) {
                    throw new Exception('Only board owner can add members');
                }
                
                $targetUser = $db->fetchOne("SELECT id FROM users WHERE email = ?", [$data['user_email']]);
                if (!$targetUser) {
                    throw new Exception('User not found');
                }
                
                $id = $db->insert('team_board_members', [
                    'board_id' => $boardId,
                    'user_id' => $targetUser['id'],
                    'role' => $data['role'] ?? 'viewer'
                ]);
                echo json_encode(['success' => true, 'id' => $id, 'message' => 'Member added successfully']);
            } elseif ($type === 'task') {
                $boardId = $data['board_id'];
                
                $hasAccess = $db->fetchColumn("SELECT COUNT(*) FROM team_boards WHERE id = ? AND (owner_id = ? OR id IN (SELECT board_id FROM team_board_members WHERE user_id = ? AND role IN ('editor', 'admin')))", [$boardId, $userId, $userId]);
                
                if (!$hasAccess) {
                    throw new Exception('You do not have permission to create tasks on this board');
                }
                
                $id = $db->insert('team_tasks', [
                    'board_id' => $boardId,
                    'created_by' => $userId,
                    'assigned_to' => $data['assigned_to'] ?? null,
                    'title' => $data['title'],
                    'description' => $data['description'] ?? null,
                    'status' => $data['status'] ?? 'todo',
                    'priority' => $data['priority'] ?? 'medium',
                    'due_date' => $data['due_date'] ?? null,
                    'tags' => $data['tags'] ?? null
                ]);
                echo json_encode(['success' => true, 'id' => $id, 'message' => 'Task created successfully']);
            }
            break;

        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? null;
            if (!$id) throw new Exception('ID required');
            
            if ($type === 'task-status') {
                $task = $db->fetchOne("SELECT board_id, assigned_to FROM team_tasks WHERE id = ?", [$id]);
                if (!$task) throw new Exception('Task not found');
                
                $hasAccess = $db->fetchColumn("SELECT COUNT(*) FROM team_boards WHERE id = ? AND (owner_id = ? OR id IN (SELECT board_id FROM team_board_members WHERE user_id = ? AND role IN ('editor', 'admin')))", [$task['board_id'], $userId, $userId]);
                
                if (!$hasAccess && $task['assigned_to'] != $userId) {
                    throw new Exception('Access denied - you do not have permission to update this task');
                }
                
                $db->execute("UPDATE team_tasks SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?", [$data['status'], $id]);
                echo json_encode(['success' => true, 'message' => 'Task updated']);
            } elseif ($type === 'member-role') {
                $member = $db->fetchOne("SELECT board_id FROM team_board_members WHERE id = ?", [$id]);
                if (!$member) throw new Exception('Member not found');
                
                $isOwner = $db->fetchColumn("SELECT COUNT(*) FROM team_boards WHERE id = ? AND owner_id = ?", [$member['board_id'], $userId]);
                if (!$isOwner) {
                    throw new Exception('Access denied - only board owner can change roles');
                }
                
                $db->execute("UPDATE team_board_members SET role = ? WHERE id = ?", [$data['role'], $id]);
                echo json_encode(['success' => true, 'message' => 'Role updated']);
            }
            break;

        case 'DELETE':
            $id = $_GET['id'] ?? null;
            if (!$id) throw new Exception('ID required');
            
            if ($type === 'board') {
                $affected = $db->execute("DELETE FROM team_boards WHERE id = ? AND owner_id = ?", [$id, $userId]);
                if ($affected === 0) {
                    throw new Exception('Board not found or access denied - only the board owner can delete it');
                }
                echo json_encode(['success' => true, 'message' => 'Board deleted']);
            } elseif ($type === 'task') {
                $task = $db->fetchOne("SELECT board_id, created_by FROM team_tasks WHERE id = ?", [$id]);
                if (!$task) {
                    throw new Exception('Task not found');
                }
                
                $hasAccess = $db->fetchColumn("SELECT COUNT(*) FROM team_boards WHERE id = ? AND (owner_id = ? OR id IN (SELECT board_id FROM team_board_members WHERE user_id = ? AND role IN ('editor', 'admin')))", [$task['board_id'], $userId, $userId]);
                
                if (!$hasAccess && $task['created_by'] != $userId) {
                    throw new Exception('Access denied - you do not have permission to delete this task');
                }
                
                $db->execute("DELETE FROM team_tasks WHERE id = ?", [$id]);
                echo json_encode(['success' => true, 'message' => 'Task deleted']);
            } elseif ($type === 'board-member') {
                $member = $db->fetchOne("SELECT board_id FROM team_board_members WHERE id = ?", [$id]);
                if (!$member) {
                    throw new Exception('Member not found');
                }
                
                $isOwner = $db->fetchColumn("SELECT COUNT(*) FROM team_boards WHERE id = ? AND owner_id = ?", [$member['board_id'], $userId]);
                if (!$isOwner) {
                    throw new Exception('Access denied - only board owner can remove members');
                }
                
                $affected = $db->execute("DELETE FROM team_board_members WHERE id = ?", [$id]);
                if ($affected === 0) {
                    throw new Exception('Member not found');
                }
                echo json_encode(['success' => true, 'message' => 'Member removed']);
            }
            break;

        default:
            throw new Exception('Invalid request method');
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
