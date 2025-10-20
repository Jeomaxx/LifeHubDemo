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

try {
    switch ($method) {
        case 'GET':
            if ($action === 'stats') {
                $activeRules = $db->fetchColumn("SELECT COUNT(*) FROM automation_rules WHERE user_id = ? AND is_active = true", [$userId]) ?: 0;
                
                $todayExecutions = $db->fetchColumn("SELECT COUNT(*) FROM automation_execution_log WHERE rule_id IN (SELECT id FROM automation_rules WHERE user_id = ?) AND executed_at >= CURRENT_DATE", [$userId]) ?: 0;
                
                $totalExecutions = $db->fetchColumn("SELECT COUNT(*) FROM automation_execution_log WHERE rule_id IN (SELECT id FROM automation_rules WHERE user_id = ?)", [$userId]) ?: 0;
                
                $successfulExecutions = $db->fetchColumn("SELECT COUNT(*) FROM automation_execution_log WHERE rule_id IN (SELECT id FROM automation_rules WHERE user_id = ?) AND status = 'success'", [$userId]) ?: 0;
                
                $successRate = $totalExecutions > 0 ? round(($successfulExecutions / $totalExecutions) * 100, 1) : 0;
                
                $stats = [
                    'active_rules' => $activeRules,
                    'today_executions' => $todayExecutions,
                    'success_rate' => $successRate,
                    'time_saved' => round($todayExecutions * 0.5, 1)
                ];
                
                echo json_encode(['success' => true, 'data' => $stats]);
            } elseif ($action === 'rules') {
                $rules = $db->fetchAll("SELECT * FROM automation_rules WHERE user_id = ? ORDER BY priority DESC, created_at DESC", [$userId]);
                echo json_encode(['success' => true, 'data' => $rules]);
            } elseif ($action === 'execution-log') {
                $logs = $db->fetchAll("SELECT l.*, r.rule_name FROM automation_execution_log l JOIN automation_rules r ON l.rule_id = r.id WHERE r.user_id = ? ORDER BY l.executed_at DESC LIMIT 50", [$userId]);
                echo json_encode(['success' => true, 'data' => $logs]);
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            if ($action === 'create-rule') {
                $id = $db->insert('automation_rules', [
                    'user_id' => $userId,
                    'rule_name' => $data['rule_name'],
                    'description' => $data['description'] ?? null,
                    'trigger_type' => $data['trigger_type'],
                    'trigger_conditions' => json_encode($data['trigger_conditions']),
                    'action_type' => $data['action_type'],
                    'action_parameters' => json_encode($data['action_parameters']),
                    'is_active' => $data['is_active'] ?? true,
                    'priority' => $data['priority'] ?? 0
                ]);
                echo json_encode(['success' => true, 'id' => $id, 'message' => 'Automation rule created successfully']);
            } elseif ($action === 'toggle-rule') {
                $id = $data['id'];
                $affected = $db->execute("UPDATE automation_rules SET is_active = NOT is_active WHERE id = ? AND user_id = ?", [$id, $userId]);
                
                if ($affected === 0) {
                    throw new Exception('Rule not found or access denied');
                }
                echo json_encode(['success' => true, 'message' => 'Rule toggled']);
            } elseif ($action === 'execute-rule') {
                $id = $data['id'];
                $rule = $db->fetchOne("SELECT * FROM automation_rules WHERE id = ? AND user_id = ?", [$id, $userId]);
                
                if (!$rule) throw new Exception('Rule not found');
                
                $logId = $db->insert('automation_execution_log', [
                    'rule_id' => $id,
                    'status' => 'success',
                    'result_data' => json_encode(['manual_execution' => true, 'timestamp' => date('Y-m-d H:i:s')])
                ]);
                
                $db->execute("UPDATE automation_rules SET last_executed = CURRENT_TIMESTAMP, execution_count = COALESCE(execution_count, 0) + 1 WHERE id = ?", [$id]);
                
                echo json_encode(['success' => true, 'log_id' => $logId, 'message' => 'Rule executed successfully']);
            }
            break;

        case 'DELETE':
            $id = $_GET['id'] ?? null;
            if (!$id) throw new Exception('ID required');
            
            $affected = $db->execute("DELETE FROM automation_rules WHERE id = ? AND user_id = ?", [$id, $userId]);
            if ($affected === 0) {
                throw new Exception('Automation rule not found or access denied');
            }
            echo json_encode(['success' => true, 'message' => 'Automation rule deleted']);
            break;

        default:
            throw new Exception('Invalid request method');
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
