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
    $lastBreak = $db->fetchOne(
        "SELECT * FROM energy_logs 
         WHERE user_id = ? AND activity_type = 'break'
         ORDER BY logged_at DESC 
         LIMIT 1",
        [$userId]
    );

    $currentHour = date('H');
    $timeSinceBreak = $lastBreak ? (time() - strtotime($lastBreak['logged_at'])) / 3600 : 3;

    $recentEnergy = $db->fetchOne(
        "SELECT AVG(energy_level) as avg_energy, AVG(focus_level) as avg_focus
         FROM energy_logs
         WHERE user_id = ? AND logged_at > NOW() - INTERVAL '2 hours'",
        [$userId]
    );

    $currentEnergy = $recentEnergy ? floatval($recentEnergy['avg_energy']) : 70;
    $currentFocus = $recentEnergy ? floatval($recentEnergy['avg_focus']) : 70;

    $shouldBreak = false;
    $breakType = '';
    $reason = '';

    if ($timeSinceBreak >= 2) {
        $shouldBreak = true;
        $breakType = 'Regular scheduled break';
        $reason = "You haven't taken a break in over 2 hours";
    } elseif ($currentEnergy < 40) {
        $shouldBreak = true;
        $breakType = 'Energy restoration break';
        $reason = 'Your energy levels are low';
    } elseif ($currentFocus < 50) {
        $shouldBreak = true;
        $breakType = 'Focus recovery break';
        $reason = 'Your focus is declining';
    }

    $breakSuggestions = [
        '5-minute walk outside',
        'Quick stretching exercises',
        'Breathing exercises',
        'Hydration break',
        'Power nap (10-15 minutes)',
        'Light snack',
        'Social chat with colleague',
        'Window gazing / eye rest'
    ];

    echo json_encode([
        'success' => true,
        'should_break' => $shouldBreak,
        'break_type' => $breakType,
        'reason' => $reason,
        'time_since_last_break_hours' => round($timeSinceBreak, 1),
        'current_energy' => round($currentEnergy, 1),
        'current_focus' => round($currentFocus, 1),
        'suggested_activities' => array_slice($breakSuggestions, 0, 3),
        'optimal_break_duration' => $shouldBreak ? ($currentEnergy < 40 ? 15 : 5) : 0
    ]);
} catch (Exception $e) {
    error_log("Break suggestion error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to suggest break'
    ]);
}
