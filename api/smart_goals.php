<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/ai_config.php';

header('Content-Type: application/json');

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = $auth->getUserId();
$db = Database::getInstance();
$ai = AIConfig::getInstance();
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'add':
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            $db->execute(
                "INSERT INTO smart_goals (user_id, goal_type, title, description, specific_target, measurable_metric, achievable_plan, relevant_reason, time_bound_deadline, current_progress, milestones, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $userId,
                    $data['goal_type'],
                    $data['title'],
                    $data['description'] ?? '',
                    $data['specific_target'],
                    $data['measurable_metric'],
                    $data['achievable_plan'],
                    $data['relevant_reason'],
                    $data['time_bound_deadline'],
                    0,
                    json_encode($data['milestones'] ?? []),
                    'active'
                ]
            );
            
            echo json_encode(['success' => true, 'message' => 'Goal created successfully']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to create goal: ' . $e->getMessage()]);
        }
        break;
    
    case 'get_goals':
        try {
            $status = $_GET['status'] ?? 'active';
            $goals = $db->fetchAll(
                "SELECT * FROM smart_goals WHERE user_id = ? AND status = ? ORDER BY time_bound_deadline ASC",
                [$userId, $status]
            );
            
            echo json_encode(['success' => true, 'goals' => $goals]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to get goals']);
        }
        break;
    
    case 'update_progress':
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $goalId = $data['goal_id'];
            $progress = $data['progress'];
            
            $goal = $db->fetch("SELECT * FROM smart_goals WHERE id = ? AND user_id = ?", [$goalId, $userId]);
            
            if ($goal) {
                $activities = $db->fetchAll(
                    "SELECT * FROM goal_activities WHERE goal_id = ? ORDER BY logged_at DESC LIMIT 10",
                    [$goalId]
                );
                
                try {
                    $aiResponse = $ai->analyzeGoalProgress($goal, $activities);
                    $aiData = json_decode($aiResponse, true);
                } catch (Exception $e) {
                    $aiData = null;
                }
                
                $db->execute(
                    "UPDATE smart_goals SET current_progress = ?, ai_success_likelihood = ?, ai_feedback = ? WHERE id = ? AND user_id = ?",
                    [
                        $progress,
                        $aiData['success_likelihood'] ?? null,
                        $aiData['feedback'] ?? null,
                        $goalId,
                        $userId
                    ]
                );
                
                $db->execute(
                    "INSERT INTO goal_activities (goal_id, user_id, activity_type, activity_data, progress_impact) 
                    VALUES (?, ?, ?, ?, ?)",
                    [$goalId, $userId, 'progress_update', json_encode(['progress' => $progress]), $progress - $goal['current_progress']]
                );
                
                if ($progress >= 100) {
                    $db->execute(
                        "UPDATE smart_goals SET status = 'completed', completed_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ?",
                        [$goalId, $userId]
                    );
                }
                
                echo json_encode(['success' => true, 'ai_feedback' => $aiData]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Goal not found']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to update progress']);
        }
        break;
    
    case 'delete':
        try {
            $id = $_GET['id'] ?? 0;
            $db->execute("DELETE FROM smart_goals WHERE id = ? AND user_id = ?", [$id, $userId]);
            echo json_encode(['success' => true, 'message' => 'Goal deleted']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to delete goal']);
        }
        break;
    
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
