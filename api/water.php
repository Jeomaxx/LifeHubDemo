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
            if ($action === 'today') {
                $date = date('Y-m-d');
                $intake = $db->fetchAll(
                    "SELECT * FROM water_intake WHERE user_id = ? AND date = ? ORDER BY time_consumed DESC",
                    [$userId, $date]
                );
                $total = $db->fetchColumn(
                    "SELECT COALESCE(SUM(amount_ml), 0) FROM water_intake WHERE user_id = ? AND date = ?",
                    [$userId, $date]
                );
                echo json_encode(['success' => true, 'intake' => $intake, 'total_ml' => $total]);
            } elseif ($action === 'goal') {
                $goal = $db->fetchOne(
                    "SELECT * FROM water_goals WHERE user_id = ? LIMIT 1",
                    [$userId]
                );
                if (!$goal) {
                    // Create default goal
                    $db->insert('water_goals', ['user_id' => $userId]);
                    $goal = $db->fetchOne("SELECT * FROM water_goals WHERE user_id = ? LIMIT 1", [$userId]);
                }
                echo json_encode(['success' => true, 'goal' => $goal]);
            } elseif ($action === 'weekly_stats') {
                $stats = $db->fetchAll(
                    "SELECT date, COALESCE(SUM(amount_ml), 0) as total_ml 
                    FROM water_intake 
                    WHERE user_id = ? AND date >= CURRENT_DATE - INTERVAL '7 days'
                    GROUP BY date ORDER BY date",
                    [$userId]
                );
                echo json_encode(['success' => true, 'stats' => $stats]);
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            if ($action === 'log') {
                $id = $db->insert('water_intake', [
                    'user_id' => $userId,
                    'date' => $data['date'] ?? date('Y-m-d'),
                    'amount_ml' => $data['amount_ml'],
                    'time_consumed' => $data['time_consumed'] ?? date('Y-m-d H:i:s')
                ]);
                echo json_encode(['success' => true, 'id' => $id, 'message' => 'Water intake logged successfully']);
            } elseif ($action === 'update_goal') {
                $existing = $db->fetchOne("SELECT id FROM water_goals WHERE user_id = ?", [$userId]);
                if ($existing) {
                    $db->execute(
                        "UPDATE water_goals SET daily_goal_ml = ?, reminder_interval = ?, reminder_enabled = ?, updated_at = CURRENT_TIMESTAMP WHERE user_id = ?",
                        [$data['daily_goal_ml'], $data['reminder_interval'] ?? 60, $data['reminder_enabled'] ?? true, $userId]
                    );
                } else {
                    $db->insert('water_goals', [
                        'user_id' => $userId,
                        'daily_goal_ml' => $data['daily_goal_ml'],
                        'reminder_interval' => $data['reminder_interval'] ?? 60,
                        'reminder_enabled' => $data['reminder_enabled'] ?? true
                    ]);
                }
                echo json_encode(['success' => true, 'message' => 'Goal updated successfully']);
            }
            break;

        case 'DELETE':
            if (isset($_GET['id'])) {
                $db->execute("DELETE FROM water_intake WHERE id = ? AND user_id = ?", [$_GET['id'], $userId]);
                echo json_encode(['success' => true, 'message' => 'Entry deleted successfully']);
            }
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
