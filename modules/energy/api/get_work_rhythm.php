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
    $latestInsight = $db->fetchOne(
        "SELECT * FROM work_rhythm_insights WHERE user_id = ? ORDER BY insight_date DESC LIMIT 1",
        [$userId]
    );

    if (!$latestInsight) {
        $insights = [
            'peak_hours' => '9:00 AM - 11:00 AM',
            'focus_times' => 'Early mornings',
            'break_schedule' => 'Take a break every 90 minutes'
        ];
    } else {
        $insights = [
            'peak_hours' => $latestInsight['peak_energy_hours'] ?? '9:00 AM - 11:00 AM',
            'focus_times' => $latestInsight['best_focus_times'] ?? 'Early mornings',
            'break_schedule' => $latestInsight['recommended_breaks'] ?? 'Take a break every 90 minutes'
        ];
    }

    echo json_encode([
        'success' => true,
        'insights' => $insights
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch work rhythm'
    ]);
}
