<?php
require_once '../../../includes/config.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/db.php';

header('Content-Type: application/json');

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$db = getDB();
$userId = $auth->getUserId();

try {
    $taskStats = $db->fetchOne(
        "SELECT 
            COUNT(*) as total_tasks,
            COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_tasks
         FROM tasks
         WHERE user_id = ? AND created_at > NOW() - INTERVAL '30 days'",
        [$userId]
    );

    $productivityScore = 50;
    if ($taskStats && $taskStats['total_tasks'] > 0) {
        $completionRate = $taskStats['completed_tasks'] / $taskStats['total_tasks'];
        $productivityScore = min(100, $completionRate * 100);
    }

    $healthStats = $db->fetchOne(
        "SELECT AVG(mood_rating) as avg_mood
         FROM mood_logs
         WHERE user_id = ? AND logged_at > NOW() - INTERVAL '30 days'",
        [$userId]
    );

    $healthScore = $healthStats && $healthStats['avg_mood'] ? 
        min(100, floatval($healthStats['avg_mood']) * 20) : 60;

    $financeStats = $db->fetchOne(
        "SELECT 
            COALESCE(SUM(CASE WHEN amount > 0 THEN amount ELSE 0 END), 0) as total_income,
            COALESCE(SUM(CASE WHEN amount < 0 THEN ABS(amount) ELSE 0 END), 0) as total_expenses
         FROM transactions
         WHERE user_id = ? AND transaction_date > NOW() - INTERVAL '30 days'",
        [$userId]
    );

    $financeScore = 50;
    if ($financeStats) {
        $income = floatval($financeStats['total_income']);
        $expenses = floatval($financeStats['total_expenses']);
        
        if ($income > 0) {
            $savingsRate = ($income - $expenses) / $income;
            $financeScore = min(100, max(0, 50 + ($savingsRate * 100)));
        }
    }

    $habitStats = $db->fetchOne(
        "SELECT 
            COUNT(DISTINCT h.id) as total_habits,
            COUNT(hl.id) as completed_logs
         FROM habits h
         LEFT JOIN habit_logs hl ON h.id = hl.habit_id 
            AND hl.completed_at > NOW() - INTERVAL '30 days'
         WHERE h.user_id = ?",
        [$userId]
    );

    $habitsScore = 50;
    if ($habitStats && $habitStats['total_habits'] > 0) {
        $targetLogs = $habitStats['total_habits'] * 30;
        $completionRate = min(1, $habitStats['completed_logs'] / $targetLogs);
        $habitsScore = $completionRate * 100;
    }

    $relationshipScore = 70;

    $overallScore = round(
        ($productivityScore * 0.25) +
        ($healthScore * 0.25) +
        ($financeScore * 0.2) +
        ($habitsScore * 0.15) +
        ($relationshipScore * 0.15)
    );

    $balanceData = [
        'overall_score' => $overallScore,
        'scores' => [
            'productivity' => round($productivityScore),
            'health' => round($healthScore),
            'finance' => round($financeScore),
            'habits' => round($habitsScore),
            'relationships' => round($relationshipScore)
        ],
        'interpretation' => getScoreInterpretation($overallScore),
        'recommendations' => generateRecommendations([
            'productivity' => $productivityScore,
            'health' => $healthScore,
            'finance' => $financeScore,
            'habits' => $habitsScore
        ]),
        'calculated_at' => date('Y-m-d H:i:s')
    ];

    $db->query(
        "INSERT INTO life_analytics_data (user_id, metric_type, metric_value, metadata, recorded_at)
         VALUES (?, 'life_balance', ?, ?, NOW())
         ON CONFLICT (user_id, metric_type, recorded_at) 
         DO UPDATE SET metric_value = EXCLUDED.metric_value, metadata = EXCLUDED.metadata",
        [$userId, $overallScore, json_encode($balanceData)]
    );

    echo json_encode([
        'success' => true,
        'balance' => $balanceData
    ]);
} catch (Exception $e) {
    error_log("Life balance calculation error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to calculate life balance'
    ]);
}

function getScoreInterpretation($score) {
    if ($score >= 85) return 'Excellent - Your life is well-balanced across all areas';
    if ($score >= 70) return 'Good - You\'re maintaining balance in most areas';
    if ($score >= 55) return 'Fair - Some areas need attention';
    if ($score >= 40) return 'Needs Improvement - Focus on key areas';
    return 'Critical - Consider making significant lifestyle changes';
}

function generateRecommendations($scores) {
    $recommendations = [];
    
    if ($scores['productivity'] < 60) {
        $recommendations[] = "Boost productivity: Try time-blocking and the Pomodoro technique";
    }
    if ($scores['health'] < 60) {
        $recommendations[] = "Improve health: Regular exercise and better sleep can increase your wellness score";
    }
    if ($scores['finance'] < 60) {
        $recommendations[] = "Strengthen finances: Review your budget and consider increasing savings rate";
    }
    if ($scores['habits'] < 60) {
        $recommendations[] = "Build habits: Start small with 1-2 keystone habits and track consistency";
    }
    
    if (empty($recommendations)) {
        $recommendations[] = "Great job! Keep maintaining your current balance";
    }
    
    return $recommendations;
}
