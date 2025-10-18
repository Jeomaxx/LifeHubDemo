<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/ai_config.php';
require_once '../includes/notifications.php';

header('Content-Type: application/json');

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = $auth->getUserId();
$db = Database::getInstance();
$ai = AIConfig::getInstance();

$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';

try {
    switch ($action) {
        case 'generate_weekly':
            $report = generateWeeklyReport($userId, $db, $ai);
            
            // Save report
            $db->execute(
                "INSERT INTO ai_reports (user_id, report_type, report_period, content, metrics, generated_at) VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP)",
                [$userId, 'weekly', 'week', $report['content'], json_encode($report['metrics'])]
            );
            
            // Send via configured channels
            $user = $db->fetchOne("SELECT email, telegram_chat_id FROM users WHERE id = ?", [$userId]);
            
            if (!empty($user['telegram_chat_id'])) {
                sendTelegramMessage($user['telegram_chat_id'], $report['content']);
                $sentVia = 'telegram';
            } elseif (!empty($user['email'])) {
                sendEmail($user['email'], 'Weekly Life Atlas Report', $report['content']);
                $sentVia = 'email';
            }
            
            $db->execute(
                "UPDATE ai_reports SET sent_via = ? WHERE user_id = ? AND report_type = 'weekly' ORDER BY generated_at DESC LIMIT 1",
                [$sentVia ?? null, $userId]
            );
            
            echo json_encode([
                'success' => true,
                'message' => 'Weekly report generated and sent',
                'report' => $report['content']
            ]);
            break;
            
        case 'generate_monthly':
            $report = generateMonthlyReport($userId, $db, $ai);
            
            $db->execute(
                "INSERT INTO ai_reports (user_id, report_type, report_period, content, metrics, generated_at) VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP)",
                [$userId, 'monthly', 'month', $report['content'], json_encode($report['metrics'])]
            );
            
            $user = $db->fetchOne("SELECT email, telegram_chat_id FROM users WHERE id = ?", [$userId]);
            
            if (!empty($user['telegram_chat_id'])) {
                sendTelegramMessage($user['telegram_chat_id'], $report['content']);
            } elseif (!empty($user['email'])) {
                sendEmail($user['email'], 'Monthly Life Atlas Report', $report['content']);
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Monthly report generated and sent',
                'report' => $report['content']
            ]);
            break;
            
        case 'get_history':
            $reports = $db->fetchAll(
                "SELECT * FROM ai_reports WHERE user_id = ? ORDER BY generated_at DESC LIMIT 20",
                [$userId]
            );
            
            echo json_encode(['success' => true, 'reports' => $reports]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    error_log('AI Report Generator error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error generating report']);
}

function generateWeeklyReport($userId, $db, $ai) {
    // Gather weekly data
    $tasks = $db->fetchAll(
        "SELECT * FROM tasks WHERE user_id = ? AND created_at >= CURRENT_DATE - INTERVAL '7 days'",
        [$userId]
    );
    $completedTasks = array_filter($tasks, fn($t) => $t['status'] == 'completed');
    
    $finance = $db->fetchOne(
        "SELECT 
            SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income,
            SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expenses
         FROM finance 
         WHERE user_id = ? AND date >= CURRENT_DATE - INTERVAL '7 days'",
        [$userId]
    );
    
    $health = $db->fetchAll(
        "SELECT * FROM health WHERE user_id = ? AND date >= CURRENT_DATE - INTERVAL '7 days'",
        [$userId]
    );
    
    $avgSteps = !empty($health) ? array_sum(array_column($health, 'steps')) / count($health) : 0;
    
    // Build report
    $report = "📊 Weekly Life Atlas Report\n";
    $report .= "Week of " . date('M d', strtotime('-7 days')) . " - " . date('M d, Y') . "\n\n";
    
    $report .= "✅ Productivity:\n";
    $report .= "• Tasks Completed: " . count($completedTasks) . " / " . count($tasks) . "\n";
    $report .= "• Completion Rate: " . (count($tasks) > 0 ? round((count($completedTasks) / count($tasks)) * 100) : 0) . "%\n\n";
    
    $report .= "💰 Finance:\n";
    $report .= "• Income: $" . number_format($finance['income'] ?? 0, 2) . "\n";
    $report .= "• Expenses: $" . number_format($finance['expenses'] ?? 0, 2) . "\n";
    $report .= "• Net: $" . number_format(($finance['income'] ?? 0) - ($finance['expenses'] ?? 0), 2) . "\n\n";
    
    $report .= "🏃 Health:\n";
    $report .= "• Avg Daily Steps: " . number_format($avgSteps) . "\n";
    $report .= "• Active Days: " . count($health) . " / 7\n\n";
    
    $report .= "💡 AI Insights:\n";
    $report .= "• Keep up the momentum with your task completion!\n";
    $report .= "• Consider increasing your step count for better health.\n";
    
    return [
        'content' => $report,
        'metrics' => [
            'tasks_completed' => count($completedTasks),
            'total_tasks' => count($tasks),
            'income' => $finance['income'] ?? 0,
            'expenses' => $finance['expenses'] ?? 0,
            'avg_steps' => round($avgSteps)
        ]
    ];
}

function generateMonthlyReport($userId, $db, $ai) {
    // Similar to weekly but for 30 days
    $report = "📈 Monthly Life Atlas Report\n";
    $report .= date('F Y') . "\n\n";
    
    // Add monthly statistics
    $tasks = $db->fetchAll(
        "SELECT * FROM tasks WHERE user_id = ? AND EXTRACT(MONTH FROM created_at) = EXTRACT(MONTH FROM CURRENT_DATE)",
        [$userId]
    );
    $completedTasks = array_filter($tasks, fn($t) => $t['status'] == 'completed');
    
    $report .= "✅ Productivity Summary:\n";
    $report .= "• Tasks Completed: " . count($completedTasks) . "\n";
    $report .= "• Success Rate: " . (count($tasks) > 0 ? round((count($completedTasks) / count($tasks)) * 100) : 0) . "%\n\n";
    
    // Add more sections...
    
    return [
        'content' => $report,
        'metrics' => [
            'tasks_completed' => count($completedTasks),
            'total_tasks' => count($tasks)
        ]
    ];
}
