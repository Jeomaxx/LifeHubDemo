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
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'add_project':
            $projectName = $_POST['project_name'] ?? '';
            $description = $_POST['description'] ?? '';
            $priority = $_POST['priority'] ?? 'medium';
            $startDate = $_POST['start_date'] ?? null;
            $endDate = $_POST['end_date'] ?? null;
            
            $projectId = $db->insert('career_projects', [
                'user_id' => $userId,
                'project_name' => $projectName,
                'description' => $description,
                'priority' => $priority,
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);
            
            echo json_encode(['success' => true, 'project_id' => $projectId]);
            break;
            
        case 'update_project':
            $projectId = $_POST['project_id'] ?? 0;
            $status = $_POST['status'] ?? null;
            $progress = $_POST['progress_percentage'] ?? null;
            
            $updates = [];
            $params = [];
            
            if ($status !== null) {
                $updates[] = "status = ?";
                $params[] = $status;
            }
            if ($progress !== null) {
                $updates[] = "progress_percentage = ?";
                $params[] = $progress;
            }
            
            if (!empty($updates)) {
                $params[] = $projectId;
                $params[] = $userId;
                $db->execute("UPDATE career_projects SET " . implode(', ', $updates) . " WHERE id = ? AND user_id = ?", $params);
            }
            
            echo json_encode(['success' => true]);
            break;
            
        case 'add_task':
            $projectId = $_POST['project_id'] ?? null;
            $taskTitle = $_POST['task_title'] ?? '';
            $taskDescription = $_POST['task_description'] ?? '';
            $status = $_POST['status'] ?? 'todo';
            
            $taskId = $db->insert('career_tasks', [
                'user_id' => $userId,
                'project_id' => $projectId,
                'task_title' => $taskTitle,
                'task_description' => $taskDescription,
                'status' => $status
            ]);
            
            $task = $db->fetchOne("SELECT * FROM career_tasks WHERE id = ?", [$taskId]);
            echo json_encode(['success' => true, 'task' => $task]);
            break;
            
        case 'update_task_status':
            $taskId = $_POST['task_id'] ?? 0;
            $status = $_POST['status'] ?? '';
            
            $db->execute("UPDATE career_tasks SET status = ? WHERE id = ? AND user_id = ?", [$status, $taskId, $userId]);
            echo json_encode(['success' => true]);
            break;
            
        case 'get_tasks':
            $projectId = $_GET['project_id'] ?? null;
            
            if ($projectId) {
                $tasks = $db->fetchAll("SELECT * FROM career_tasks WHERE user_id = ? AND project_id = ? ORDER BY created_at DESC", [$userId, $projectId]) ?: [];
            } else {
                $tasks = $db->fetchAll("SELECT * FROM career_tasks WHERE user_id = ? ORDER BY created_at DESC", [$userId]) ?: [];
            }
            
            echo json_encode(['success' => true, 'tasks' => $tasks]);
            break;
            
        case 'log_time':
            $projectId = $_POST['project_id'] ?? null;
            $activityDescription = $_POST['activity_description'] ?? '';
            $hoursLogged = $_POST['hours_logged'] ?? 0;
            $logDate = $_POST['log_date'] ?? date('Y-m-d');
            
            $timeLogId = $db->insert('time_logs', [
                'user_id' => $userId,
                'project_id' => $projectId,
                'activity_description' => $activityDescription,
                'hours_logged' => $hoursLogged,
                'log_date' => $logDate
            ]);
            
            echo json_encode(['success' => true, 'time_log_id' => $timeLogId]);
            break;
            
        case 'set_salary_goal':
            $currentSalary = $_POST['current_salary'] ?? 0;
            $targetSalary = $_POST['target_salary'] ?? 0;
            $targetDate = $_POST['target_date'] ?? null;
            $notes = $_POST['notes'] ?? '';
            
            $goalId = $db->insert('salary_progress', [
                'user_id' => $userId,
                'current_salary' => $currentSalary,
                'target_salary' => $targetSalary,
                'target_date' => $targetDate,
                'notes' => $notes
            ]);
            
            echo json_encode(['success' => true, 'goal_id' => $goalId]);
            break;
            
        case 'delete_task':
            $taskId = $_POST['task_id'] ?? 0;
            $db->delete('career_tasks', 'id = ? AND user_id = ?', [$taskId, $userId]);
            echo json_encode(['success' => true]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    error_log("Career Hub API Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}
