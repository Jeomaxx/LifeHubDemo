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
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'add_dependency':
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Verify both tasks belong to the authenticated user
            $task = $db->fetchOne("SELECT id FROM tasks WHERE id = ? AND user_id = ?", [$data['task_id'], $userId]);
            $dependsOnTask = $db->fetchOne("SELECT id FROM tasks WHERE id = ? AND user_id = ?", [$data['depends_on_task_id'], $userId]);
            
            if (!$task || !$dependsOnTask) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Unauthorized: Tasks not found or access denied']);
                exit;
            }
            
            $db->execute(
                "INSERT INTO task_dependencies (task_id, depends_on_task_id) VALUES (?, ?)",
                [$data['task_id'], $data['depends_on_task_id']]
            );
            
            echo json_encode(['success' => true, 'message' => 'Dependency added']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to add dependency']);
        }
        break;
    
    case 'remove_dependency':
        try {
            $dependencyId = $_GET['id'] ?? 0;
            
            // Verify the dependency belongs to a task owned by the user
            $dependency = $db->fetchOne(
                "SELECT td.id FROM task_dependencies td 
                JOIN tasks t ON td.task_id = t.id 
                WHERE td.id = ? AND t.user_id = ?",
                [$dependencyId, $userId]
            );
            
            if (!$dependency) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Unauthorized: Dependency not found or access denied']);
                exit;
            }
            
            $db->execute(
                "DELETE FROM task_dependencies WHERE id = ?",
                [$dependencyId]
            );
            
            echo json_encode(['success' => true, 'message' => 'Dependency removed']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to remove dependency']);
        }
        break;
    
    case 'get_dependencies':
        try {
            $taskId = $_GET['task_id'] ?? 0;
            
            // Verify the task belongs to the authenticated user
            $task = $db->fetchOne("SELECT id FROM tasks WHERE id = ? AND user_id = ?", [$taskId, $userId]);
            
            if (!$task) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Unauthorized: Task not found or access denied']);
                exit;
            }
            
            $dependencies = $db->fetchAll(
                "SELECT td.*, t.title as depends_on_title 
                FROM task_dependencies td 
                JOIN tasks t ON td.depends_on_task_id = t.id 
                WHERE td.task_id = ?",
                [$taskId]
            );
            
            echo json_encode(['success' => true, 'dependencies' => $dependencies]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to get dependencies']);
        }
        break;
    
    case 'create_recurring':
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            $nextOccurrence = calculateNextOccurrence(date('Y-m-d'), $data['recurrence_pattern']);
            
            $db->execute(
                "UPDATE tasks SET is_recurring = TRUE, recurrence_pattern = ?, next_occurrence = ? WHERE id = ? AND user_id = ?",
                [$data['recurrence_pattern'], $nextOccurrence, $data['task_id'], $userId]
            );
            
            echo json_encode(['success' => true, 'next_occurrence' => $nextOccurrence]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to set recurring']);
        }
        break;
    
    case 'start_pomodoro':
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Verify the task belongs to the authenticated user
            $task = $db->fetchOne("SELECT id FROM tasks WHERE id = ? AND user_id = ?", [$data['task_id'], $userId]);
            
            if (!$task) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Unauthorized: Task not found or access denied']);
                exit;
            }
            
            $db->execute(
                "INSERT INTO pomodoro_sessions (task_id, user_id, duration) VALUES (?, ?, ?)",
                [$data['task_id'], $userId, $data['duration'] ?? 25]
            );
            
            $sessionId = $db->lastInsertId();
            
            echo json_encode(['success' => true, 'session_id' => $sessionId]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to start pomodoro']);
        }
        break;
    
    case 'complete_pomodoro':
        try {
            $sessionId = $_GET['session_id'] ?? 0;
            
            // Verify the session belongs to the authenticated user and get task info
            $session = $db->fetchOne(
                "SELECT ps.task_id, ps.duration, t.user_id 
                FROM pomodoro_sessions ps
                JOIN tasks t ON ps.task_id = t.id
                WHERE ps.id = ? AND ps.user_id = ?",
                [$sessionId, $userId]
            );
            
            if (!$session) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Unauthorized: Session not found or access denied']);
                exit;
            }
            
            // Verify the task also belongs to the user (double-check)
            if ($session['user_id'] != $userId) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Unauthorized: Task does not belong to user']);
                exit;
            }
            
            $db->execute(
                "UPDATE pomodoro_sessions SET completed = TRUE, completed_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ?",
                [$sessionId, $userId]
            );
            
            $db->execute(
                "UPDATE tasks SET pomodoro_count = pomodoro_count + 1, time_spent = time_spent + ? WHERE id = ? AND user_id = ?",
                [$session['duration'], $session['task_id'], $userId]
            );
            
            echo json_encode(['success' => true, 'message' => 'Pomodoro completed']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to complete pomodoro']);
        }
        break;
    
    case 'get_pomodoro_stats':
        try {
            $taskId = $_GET['task_id'] ?? 0;
            
            $stats = $db->fetchOne(
                "SELECT 
                    COUNT(*) as total_sessions,
                    SUM(CASE WHEN completed = TRUE THEN 1 ELSE 0 END) as completed_sessions,
                    SUM(CASE WHEN completed = TRUE THEN duration ELSE 0 END) as total_time
                FROM pomodoro_sessions 
                WHERE task_id = ? AND user_id = ?",
                [$taskId, $userId]
            );
            
            echo json_encode(['success' => true, 'stats' => $stats]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to get stats']);
        }
        break;
    
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

function calculateNextOccurrence($currentDate, $pattern) {
    $date = new DateTime($currentDate);
    
    switch ($pattern) {
        case 'daily':
            $date->modify('+1 day');
            break;
        case 'weekly':
            $date->modify('+1 week');
            break;
        case 'monthly':
            $date->modify('+1 month');
            break;
        case 'yearly':
            $date->modify('+1 year');
            break;
        default:
            $date->modify('+1 day');
    }
    
    return $date->format('Y-m-d');
}
?>
