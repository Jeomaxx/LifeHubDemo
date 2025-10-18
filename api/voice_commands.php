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

$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';

try {
    switch ($action) {
        case 'add_task':
            $taskName = $data['name'] ?? '';
            if (empty($taskName)) {
                echo json_encode(['success' => false, 'message' => 'Task name is required']);
                exit;
            }
            
            $db->execute(
                "INSERT INTO tasks (user_id, title, status, priority, created_at) VALUES (?, ?, 'pending', 'medium', CURRENT_TIMESTAMP)",
                [$userId, $taskName]
            );
            
            echo json_encode(['success' => true, 'message' => "Task '$taskName' added successfully"]);
            break;
            
        case 'complete_task':
            $taskName = $data['name'] ?? '';
            if (empty($taskName)) {
                echo json_encode(['success' => false, 'message' => 'Task name is required']);
                exit;
            }
            
            $result = $db->execute(
                "UPDATE tasks SET status = 'completed', completed_at = CURRENT_TIMESTAMP WHERE user_id = ? AND title ILIKE ? AND status != 'completed' LIMIT 1",
                [$userId, "%$taskName%"]
            );
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => "Task completed"]);
            } else {
                echo json_encode(['success' => false, 'message' => "Task not found"]);
            }
            break;
            
        case 'track_habit':
            $habitName = $data['name'] ?? '';
            if (empty($habitName)) {
                echo json_encode(['success' => false, 'message' => 'Habit name is required']);
                exit;
            }
            
            // Find habit by name
            $habit = $db->fetchOne(
                "SELECT id FROM habits WHERE user_id = ? AND name ILIKE ? LIMIT 1",
                [$userId, "%$habitName%"]
            );
            
            if ($habit) {
                // Log habit completion for today
                $db->execute(
                    "INSERT INTO habit_logs (habit_id, user_id, log_date, completed) 
                     VALUES (?, ?, CURRENT_DATE, TRUE) 
                     ON CONFLICT (habit_id, log_date) DO UPDATE SET completed = TRUE",
                    [$habit['id'], $userId]
                );
                
                echo json_encode(['success' => true, 'message' => "Habit '$habitName' tracked for today"]);
            } else {
                echo json_encode(['success' => false, 'message' => "Habit not found"]);
            }
            break;
            
        case 'add_expense':
            $amount = $data['amount'] ?? 0;
            $category = $data['category'] ?? 'General';
            
            if ($amount <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid amount']);
                exit;
            }
            
            $db->execute(
                "INSERT INTO finance (user_id, type, amount, category, description, date) 
                 VALUES (?, 'expense', ?, ?, 'Added via voice command', CURRENT_DATE)",
                [$userId, $amount, $category]
            );
            
            echo json_encode(['success' => true, 'message' => "Expense of $$amount added to $category"]);
            break;
            
        case 'send_report':
            // Generate and send daily report
            require_once '../includes/notifications.php';
            
            // Get user email and telegram
            $user = $db->fetchOne("SELECT email, telegram_chat_id FROM users WHERE id = ?", [$userId]);
            
            // Generate report content
            $tasks = $db->fetchAll("SELECT * FROM tasks WHERE user_id = ? AND status != 'completed' ORDER BY priority DESC LIMIT 5", [$userId]);
            $taskCount = count($tasks);
            
            $finance = $db->fetchOne("SELECT SUM(CASE WHEN type = 'income' THEN amount ELSE -amount END) as balance FROM finance WHERE user_id = ? AND EXTRACT(MONTH FROM date) = EXTRACT(MONTH FROM CURRENT_DATE)", [$userId]);
            $balance = $finance['balance'] ?? 0;
            
            $message = "Daily Report\n\n";
            $message .= "Pending Tasks: $taskCount\n";
            $message .= "Monthly Balance: $" . number_format($balance, 2) . "\n\n";
            $message .= "Generated via voice command";
            
            $sent = false;
            if (!empty($user['telegram_chat_id'])) {
                $sent = sendTelegramMessage($user['telegram_chat_id'], $message);
            }
            
            if ($sent) {
                echo json_encode(['success' => true, 'message' => 'Daily report sent to Telegram']);
            } else {
                echo json_encode(['success' => true, 'message' => 'Report generated but could not send notification']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Unknown command']);
    }
} catch (Exception $e) {
    error_log('Voice command error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error executing command: ' . $e->getMessage()]);
}
