<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

$db = Database::getInstance();

$users = $db->fetchAll("SELECT id FROM users WHERE is_active = true");

foreach ($users as $user) {
    $userId = $user['id'];
    
    try {
        $taskCount = $db->fetchColumn("SELECT COUNT(*) FROM tasks WHERE user_id = ? AND created_at >= (CURRENT_TIMESTAMP - INTERVAL '7 days')", [$userId]) ?? 0;
        $completedTasks = $db->fetchColumn("SELECT COUNT(*) FROM tasks WHERE user_id = ? AND status = 'completed' AND completed_at >= (CURRENT_TIMESTAMP - INTERVAL '7 days')", [$userId]) ?? 0;
        $goalProgress = $db->fetchColumn("SELECT AVG(progress_percentage) FROM goals WHERE user_id = ?", [$userId]) ?? 0;
        $financialHealth = $db->fetchColumn("SELECT AVG(balance) FROM accounts WHERE user_id = ?", [$userId]) ?? 0;
        $habitStreak = $db->fetchColumn("SELECT MAX(current_streak) FROM habits WHERE user_id = ?", [$userId]) ?? 0;
        
        $productivityScore = min(100, ($completedTasks / max(1, $taskCount)) * 100);
        $goalScore = $goalProgress;
        $financialScore = min(100, max(0, ($financialHealth / 1000) * 50));
        $habitScore = min(100, $habitStreak * 2);
        
        $lifeBalanceScore = ($productivityScore + $goalScore + $financialScore + $habitScore) / 4;
        
        $analytics = [
            'productivity_score' => round($productivityScore, 2),
            'goal_progress' => round($goalScore, 2),
            'financial_health' => round($financialScore, 2),
            'habit_consistency' => round($habitScore, 2),
            'tasks_completed' => $completedTasks,
            'total_tasks' => $taskCount
        ];
        
        $insights = "Your life balance score is " . round($lifeBalanceScore, 1) . "/100. ";
        if ($productivityScore > 80) {
            $insights .= "Great productivity this week! ";
        }
        if ($habitScore > 70) {
            $insights .= "Strong habit consistency. ";
        }
        
        $reportExists = $db->fetchOne("
            SELECT id FROM life_reports 
            WHERE user_id = ? AND report_period = 'weekly' 
            AND created_at >= (CURRENT_TIMESTAMP - INTERVAL '7 days')
        ", [$userId]);
        
        if (!$reportExists) {
            $db->insert('life_reports', [
                'user_id' => $userId,
                'report_period' => 'weekly',
                'life_balance_score' => round($lifeBalanceScore, 2),
                'ai_insights' => $insights,
                'report_data' => json_encode($analytics)
            ]);
            
            echo "Generated weekly report for user {$userId}\n";
        }
        
    } catch (Exception $e) {
        error_log("Analytics generator error for user {$userId}: " . $e->getMessage());
    }
}

echo "Analytics generator completed. Processed " . count($users) . " users.\n";
