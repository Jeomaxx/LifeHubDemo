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
    case 'create_recurring':
        $data = json_decode(file_get_contents('php://input'), true);
        
        $taskId = $db->execute(
            "INSERT INTO tasks (user_id, title, description, category, priority, due_date, status, recurring_pattern, recurring_until) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?) RETURNING id",
            [
                $userId,
                $data['title'],
                $data['description'] ?? null,
                $data['category'] ?? null,
                $data['priority'] ?? 'medium',
                $data['due_date'] ?? null,
                'pending',
                $data['recurring_pattern'],
                $data['recurring_until'] ?? null
            ]
        );
        
        echo json_encode(['success' => true, 'task_id' => $taskId]);
        break;
    
    case 'add_dependency':
        $data = json_decode(file_get_contents('php://input'), true);
        
        $db->execute(
            "INSERT INTO task_dependencies (task_id, depends_on_task_id) VALUES (?, ?)",
            [$data['task_id'], $data['depends_on_id']]
        );
        
        echo json_encode(['success' => true]);
        break;
    
    case 'get_dependencies':
        $taskId = $_GET['task_id'] ?? 0;
        
        $dependencies = $db->fetchAll(
            "SELECT t.*, d.id as dependency_id 
             FROM task_dependencies d
             JOIN tasks t ON d.depends_on_task_id = t.id
             WHERE d.task_id = ?",
            [$taskId]
        );
        
        echo json_encode(['success' => true, 'dependencies' => $dependencies]);
        break;
    
    case 'remove_dependency':
        $depId = $_GET['id'] ?? 0;
        $db->execute("DELETE FROM task_dependencies WHERE id = ?", [$depId]);
        echo json_encode(['success' => true]);
        break;
    
    case 'start_pomodoro':
        $data = json_decode(file_get_contents('php://input'), true);
        
        $sessionId = $db->execute(
            "INSERT INTO pomodoro_sessions (user_id, task_id, duration_minutes, session_type) 
             VALUES (?, ?, ?, ?) RETURNING id",
            [
                $userId,
                $data['task_id'] ?? null,
                $data['duration'] ?? 25,
                $data['type'] ?? 'work'
            ]
        );
        
        echo json_encode(['success' => true, 'session_id' => $sessionId]);
        break;
    
    case 'complete_pomodoro':
        $sessionId = $_GET['session_id'] ?? 0;
        
        $db->execute(
            "UPDATE pomodoro_sessions SET completed = TRUE, completed_at = NOW() WHERE id = ? AND user_id = ?",
            [$sessionId, $userId]
        );
        
        echo json_encode(['success' => true]);
        break;
    
    case 'get_pomodoro_stats':
        $taskId = $_GET['task_id'] ?? null;
        
        $query = "SELECT COUNT(*) as total_sessions, 
                         SUM(CASE WHEN completed THEN 1 ELSE 0 END) as completed_sessions,
                         SUM(CASE WHEN completed THEN duration_minutes ELSE 0 END) as total_minutes
                  FROM pomodoro_sessions 
                  WHERE user_id = ?";
        $params = [$userId];
        
        if ($taskId) {
            $query .= " AND task_id = ?";
            $params[] = $taskId;
        }
        
        $stats = $db->fetchOne($query, $params);
        echo json_encode(['success' => true, 'stats' => $stats]);
        break;
    
    case 'smart_schedule':
        $preferences = $db->fetchOne(
            "SELECT settings FROM users WHERE id = ?",
            [$userId]
        );
        
        $settings = json_decode($preferences['settings'] ?? '{}', true);
        $workHoursStart = $settings['work_hours_start'] ?? 9;
        $workHoursEnd = $settings['work_hours_end'] ?? 17;
        
        $pendingTasks = $db->fetchAll(
            "SELECT * FROM tasks 
             WHERE user_id = ? 
             AND status = 'pending' 
             AND (due_date IS NULL OR due_date >= CURRENT_DATE)
             ORDER BY 
                CASE priority 
                    WHEN 'high' THEN 1 
                    WHEN 'medium' THEN 2 
                    WHEN 'low' THEN 3 
                END,
                due_date ASC NULLS LAST
             LIMIT 10",
            [$userId]
        );
        
        $suggestions = [];
        $currentDate = new DateTime();
        $currentHour = (int)$currentDate->format('H');
        
        if ($currentHour < $workHoursStart) {
            $currentDate->setTime($workHoursStart, 0);
        } elseif ($currentHour >= $workHoursEnd) {
            $currentDate->modify('+1 day');
            $currentDate->setTime($workHoursStart, 0);
        }
        
        foreach ($pendingTasks as $task) {
            $estimatedDuration = 1;
            
            if ($currentDate->format('H') >= $workHoursEnd) {
                $currentDate->modify('+1 day');
                $currentDate->setTime($workHoursStart, 0);
            }
            
            $suggestions[] = [
                'task_id' => $task['id'],
                'title' => $task['title'],
                'suggested_time' => $currentDate->format('Y-m-d H:i'),
                'duration' => $estimatedDuration,
                'priority' => $task['priority']
            ];
            
            $currentDate->modify("+{$estimatedDuration} hour");
        }
        
        echo json_encode(['success' => true, 'suggestions' => $suggestions]);
        break;
    
    case 'process_recurring_tasks':
        $recurringTasks = $db->fetchAll(
            "SELECT * FROM tasks 
             WHERE user_id = ? 
             AND recurring_pattern IS NOT NULL 
             AND (recurring_until IS NULL OR recurring_until >= CURRENT_DATE)
             AND due_date <= CURRENT_DATE 
             AND status = 'completed'",
            [$userId]
        );
        
        $created = 0;
        foreach ($recurringTasks as $task) {
            $pattern = $task['recurring_pattern'];
            $nextDueDate = new DateTime($task['due_date']);
            
            switch ($pattern) {
                case 'daily':
                    $nextDueDate->modify('+1 day');
                    break;
                case 'weekly':
                    $nextDueDate->modify('+1 week');
                    break;
                case 'monthly':
                    $nextDueDate->modify('+1 month');
                    break;
                case 'yearly':
                    $nextDueDate->modify('+1 year');
                    break;
            }
            
            $db->execute(
                "INSERT INTO tasks (user_id, title, description, category, priority, due_date, status, recurring_pattern, recurring_until) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $userId,
                    $task['title'],
                    $task['description'],
                    $task['category'],
                    $task['priority'],
                    $nextDueDate->format('Y-m-d'),
                    'pending',
                    $task['recurring_pattern'],
                    $task['recurring_until']
                ]
            );
            
            $db->execute("UPDATE tasks SET recurring_pattern = NULL WHERE id = ?", [$task['id']]);
            $created++;
        }
        
        echo json_encode(['success' => true, 'created' => $created]);
        break;
    
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
