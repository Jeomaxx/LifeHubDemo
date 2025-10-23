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
    $latestReport = $db->fetchOne(
        "SELECT * FROM life_reports WHERE user_id = ? ORDER BY report_period DESC LIMIT 1",
        [$userId]
    );

    if ($latestReport) {
        $balance = [
            'overall_score' => $latestReport['life_balance_score'] ?? 75.0,
            'health_score' => $latestReport['health_score'] ?? 80.0,
            'finance_score' => $latestReport['finance_score'] ?? 70.0,
            'productivity_score' => $latestReport['productivity_score'] ?? 75.0,
            'mood_score' => $latestReport['mood_score'] ?? 78.0
        ];
    } else {
        $balance = [
            'overall_score' => 75.0,
            'health_score' => 80.0,
            'finance_score' => 70.0,
            'productivity_score' => 75.0,
            'mood_score' => 78.0
        ];
        
        $db->insert('life_reports', [
            'user_id' => $userId,
            'report_type' => 'weekly',
            'report_period' => date('Y-m-d'),
            'life_balance_score' => 75.0,
            'health_score' => 80.0,
            'finance_score' => 70.0,
            'productivity_score' => 75.0,
            'mood_score' => 78.0,
            'insights' => json_encode(['message' => 'Welcome to Life Analytics!']),
            'correlations' => json_encode([]),
            'recommendations' => json_encode([]),
            'metrics_data' => json_encode([])
        ]);
    }

    echo json_encode([
        'success' => true,
        'balance' => $balance
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch life balance data'
    ]);
}
