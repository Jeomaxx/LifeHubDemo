<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/ai_config.php';

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
        case 'generate':
            $todayBriefing = $db->fetchOne("SELECT * FROM life_advisor_briefings WHERE user_id = ? AND briefing_date = CURRENT_DATE", [$userId]);
            
            if ($todayBriefing) {
                echo json_encode(['success' => false, 'message' => 'Briefing already generated for today']);
                exit;
            }
            
            $aiConfig = new AIConfig();
            
            $finances = $db->fetchAll("SELECT * FROM finance WHERE user_id = ? AND EXTRACT(MONTH FROM date) = EXTRACT(MONTH FROM CURRENT_DATE)", [$userId]) ?: [];
            $bills = $db->fetchAll("SELECT * FROM bills WHERE user_id = ? AND due_date >= CURRENT_DATE AND due_date <= CURRENT_DATE + INTERVAL '7 days'", [$userId]) ?: [];
            $goals = $db->fetchAll("SELECT * FROM smart_goals WHERE user_id = ? AND status = 'active'", [$userId]) ?: [];
            $tasks = $db->fetchAll("SELECT * FROM tasks WHERE user_id = ? AND status != 'completed'", [$userId]) ?: [];
            $mood = $db->fetchOne("SELECT * FROM mood_entries WHERE user_id = ? ORDER BY mood_date DESC LIMIT 1", [$userId]);
            $health = $db->fetchOne("SELECT * FROM health WHERE user_id = ? ORDER BY date DESC LIMIT 1", [$userId]);
            
            $totalIncome = array_sum(array_column(array_filter($finances, fn($f) => $f['type'] == 'income'), 'amount'));
            $totalExpense = array_sum(array_column(array_filter($finances, fn($f) => $f['type'] == 'expense'), 'amount'));
            
            $prompt = "You are an AI Life Advisor. Generate a comprehensive daily briefing for the user based on their data. Return JSON with: daily_summary (string), action_items (array of objects with 'text' and 'type'), ai_recommendations (string), priority_score (1-100). Today is " . date('Y-m-d') . ".\n\n";
            $prompt .= "Financial Summary: Income this month: $" . $totalIncome . ", Expenses: $" . $totalExpense . ", Balance: $" . ($totalIncome - $totalExpense) . "\n";
            $prompt .= "Upcoming Bills (" . count($bills) . "): " . implode(', ', array_map(fn($b) => $b['name'] . " ($" . $b['amount'] . " due " . $b['due_date'] . ")", array_slice($bills, 0, 3))) . "\n";
            $prompt .= "Active Goals (" . count($goals) . "): " . implode(', ', array_map(fn($g) => $g['goal_title'] . " (" . $g['current_progress'] . "%)", array_slice($goals, 0, 3))) . "\n";
            $prompt .= "Pending Tasks (" . count($tasks) . "): " . implode(', ', array_map(fn($t) => $t['title'], array_slice($tasks, 0, 3))) . "\n";
            
            if ($mood) {
                $prompt .= "Recent Mood: " . $mood['mood_rating'] . "/10 (" . $mood['mood_emoji'] . ") on " . $mood['mood_date'] . "\n";
            }
            
            if ($health) {
                $prompt .= "Health: Weight: " . $health['weight'] . " kg, Steps: " . $health['steps'] . "\n";
            }
            
            $prompt .= "\nGenerate a motivating daily briefing with 3-5 actionable items prioritized by importance. Include specific recommendations based on the data.";
            
            $aiResponse = $aiConfig->generateContent($prompt);
            
            $parsedResponse = json_decode($aiResponse, true);
            if (!$parsedResponse) {
                $parsedResponse = [
                    'daily_summary' => $aiResponse,
                    'action_items' => [
                        ['text' => 'Review your tasks', 'type' => 'normal'],
                        ['text' => 'Check upcoming bills', 'type' => 'normal']
                    ],
                    'ai_recommendations' => 'Stay focused on your goals today.',
                    'priority_score' => 50
                ];
            }
            
            $dailySummary = $parsedResponse['daily_summary'] ?? $aiResponse;
            $recommendations = $parsedResponse['ai_recommendations'] ?? 'Stay focused and productive!';
            $priorityScore = $parsedResponse['priority_score'] ?? 50;
            
            $stmt = $db->execute("INSERT INTO life_advisor_briefings (user_id, briefing_date, daily_summary, ai_recommendations, priority_score) VALUES (?, CURRENT_DATE, ?, ?, ?) RETURNING id", 
                [$userId, $dailySummary, $recommendations, $priorityScore]);
            $briefingId = $stmt->fetchColumn();
            
            if (isset($parsedResponse['action_items']) && is_array($parsedResponse['action_items'])) {
                foreach ($parsedResponse['action_items'] as $item) {
                    $db->insert('life_advisor_actions', [
                        'briefing_id' => $briefingId,
                        'action_text' => $item['text'] ?? $item,
                        'action_type' => $item['type'] ?? 'normal'
                    ]);
                }
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Daily briefing generated successfully',
                'briefing_id' => $briefingId
            ]);
            break;
            
        case 'toggle_action':
            $actionId = $_POST['action_id'] ?? 0;
            $isCompleted = $_POST['is_completed'] ?? false;
            
            $db->execute("UPDATE life_advisor_actions SET is_completed = ?, completed_at = ? WHERE id = ?",
                [$isCompleted ? true : false, $isCompleted ? date('Y-m-d H:i:s') : null, $actionId]);
            
            echo json_encode(['success' => true]);
            break;
            
        case 'export':
            $briefingId = $_GET['briefing_id'] ?? 0;
            $briefing = $db->fetchOne("SELECT * FROM life_advisor_briefings WHERE id = ? AND user_id = ?", [$briefingId, $userId]);
            
            if (!$briefing) {
                echo json_encode(['success' => false, 'message' => 'Briefing not found']);
                exit;
            }
            
            $actions = $db->fetchAll("SELECT * FROM life_advisor_actions WHERE briefing_id = ?", [$briefingId]) ?: [];
            
            header('Content-Type: text/plain');
            header('Content-Disposition: attachment; filename="daily-briefing-' . $briefing['briefing_date'] . '.txt"');
            
            echo "AI LIFE ADVISOR - DAILY BRIEFING\n";
            echo "Date: " . $briefing['briefing_date'] . "\n";
            echo "="  . str_repeat("=", 50) . "\n\n";
            echo "DAILY SUMMARY:\n" . $briefing['daily_summary'] . "\n\n";
            echo "AI RECOMMENDATIONS:\n" . $briefing['ai_recommendations'] . "\n\n";
            echo "ACTION ITEMS:\n";
            foreach ($actions as $action) {
                echo "  " . ($action['is_completed'] ? '[x]' : '[ ]') . " " . $action['action_text'] . " (" . $action['action_type'] . ")\n";
            }
            exit;
            
        case 'view':
            $briefingId = $_GET['briefing_id'] ?? 0;
            $briefing = $db->fetchOne("SELECT * FROM life_advisor_briefings WHERE id = ? AND user_id = ?", [$briefingId, $userId]);
            
            if (!$briefing) {
                echo json_encode(['success' => false, 'message' => 'Briefing not found']);
                exit;
            }
            
            $actions = $db->fetchAll("SELECT * FROM life_advisor_actions WHERE briefing_id = ?", [$briefingId]) ?: [];
            $briefing['actions'] = $actions;
            
            echo json_encode(['success' => true, 'briefing' => $briefing]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    error_log("Life Advisor API Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}
