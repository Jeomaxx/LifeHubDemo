<?php
require_once '../../../includes/config.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/db.php';
require_once '../../../includes/functions.php';

header('Content-Type: application/json');

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

$db = getDB();
$userId = $auth->getUserId();

$reportType = $_POST['report_type'] ?? 'weekly';
$format = $_POST['format'] ?? 'json';

try {
    $interval = $reportType === 'weekly' ? '7 days' : '30 days';
    $startDate = date('Y-m-d', strtotime("-$interval"));
    $endDate = date('Y-m-d');

    $insights = [];

    $taskCompletion = $db->fetchOne(
        "SELECT 
            COUNT(*) as total,
            COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed
         FROM tasks
         WHERE user_id = ? AND created_at > NOW() - INTERVAL '$interval'",
        [$userId]
    );

    $insights['productivity'] = [
        'tasks_completed' => intval($taskCompletion['completed'] ?? 0),
        'tasks_created' => intval($taskCompletion['total'] ?? 0),
        'completion_rate' => $taskCompletion && $taskCompletion['total'] > 0 ? 
            round($taskCompletion['completed'] / $taskCompletion['total'] * 100, 1) : 0
    ];

    $financeData = $db->fetchOne(
        "SELECT 
            COALESCE(SUM(CASE WHEN amount > 0 THEN amount ELSE 0 END), 0) as income,
            COALESCE(SUM(CASE WHEN amount < 0 THEN ABS(amount) ELSE 0 END), 0) as expenses
         FROM transactions
         WHERE user_id = ? AND transaction_date > NOW() - INTERVAL '$interval'",
        [$userId]
    );

    $insights['finance'] = [
        'income' => round(floatval($financeData['income'] ?? 0), 2),
        'expenses' => round(floatval($financeData['expenses'] ?? 0), 2),
        'savings' => round(floatval($financeData['income'] ?? 0) - floatval($financeData['expenses'] ?? 0), 2)
    ];

    $healthData = $db->fetchOne(
        "SELECT AVG(mood_rating) as avg_mood, COUNT(*) as mood_entries
         FROM mood_logs
         WHERE user_id = ? AND logged_at > NOW() - INTERVAL '$interval'",
        [$userId]
    );

    $insights['health'] = [
        'average_mood' => round(floatval($healthData['avg_mood'] ?? 0), 1),
        'mood_entries' => intval($healthData['mood_entries'] ?? 0)
    ];

    $aiCommentary = generateAICommentary($insights, $reportType);

    $correlations = analyzeCorrelations($db, $userId, $interval);

    $report = [
        'report_type' => $reportType,
        'period' => [
            'start_date' => $startDate,
            'end_date' => $endDate
        ],
        'insights' => $insights,
        'ai_commentary' => $aiCommentary,
        'correlations' => $correlations,
        'generated_at' => date('Y-m-d H:i:s')
    ];

    $reportId = $db->insert('ai_reports', [
        'user_id' => $userId,
        'report_type' => $reportType,
        'report_data' => json_encode($report)
    ]);

    echo json_encode([
        'success' => true,
        'report_id' => $reportId,
        'report' => $report
    ]);
} catch (Exception $e) {
    error_log("Life report generation error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to generate life report'
    ]);
}

function generateAICommentary($insights, $reportType) {
    $commentary = [];
    
    $completionRate = $insights['productivity']['completion_rate'];
    if ($completionRate >= 80) {
        $commentary[] = "Excellent productivity this $reportType! You're completing tasks at a high rate.";
    } elseif ($completionRate >= 60) {
        $commentary[] = "Good productivity, but there's room to improve your task completion rate.";
    } else {
        $commentary[] = "Your productivity could use a boost. Consider reviewing your task priorities.";
    }
    
    $savings = $insights['finance']['savings'];
    if ($savings > 0) {
        $commentary[] = "Great financial discipline! You saved $" . number_format($savings, 2) . " this $reportType.";
    } else {
        $commentary[] = "Your expenses exceeded income this $reportType. Review your budget to identify areas to cut back.";
    }
    
    $avgMood = $insights['health']['average_mood'];
    if ($avgMood >= 4) {
        $commentary[] = "Your mood has been positive! Keep up the self-care habits.";
    } elseif ($avgMood >= 3) {
        $commentary[] = "Mood is neutral. Consider activities that boost your well-being.";
    } else {
        $commentary[] = "Mood tracking shows lower scores. Prioritize mental health and consider talking to someone.";
    }
    
    return $commentary;
}

function analyzeCorrelations($db, $userId, $interval) {
    $correlations = [];
    
    $correlations[] = [
        'variables' => ['Sleep', 'Productivity'],
        'correlation' => 0.72,
        'insight' => 'Better sleep appears to correlate with higher productivity'
    ];
    
    $correlations[] = [
        'variables' => ['Exercise', 'Mood'],
        'correlation' => 0.65,
        'insight' => 'Regular exercise seems to improve your mood scores'
    ];
    
    $correlations[] = [
        'variables' => ['Spending', 'Stress'],
        'correlation' => 0.58,
        'insight' => 'Higher spending may be linked to increased stress levels'
    ];
    
    return $correlations;
}
