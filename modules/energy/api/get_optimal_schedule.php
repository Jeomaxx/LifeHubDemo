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
    $energyLogs = $db->fetchAll(
        "SELECT EXTRACT(HOUR FROM logged_at)::int as hour, 
                AVG(energy_level) as avg_energy,
                AVG(focus_level) as avg_focus
         FROM energy_logs
         WHERE user_id = ? AND logged_at > NOW() - INTERVAL '30 days'
         GROUP BY EXTRACT(HOUR FROM logged_at)
         ORDER BY hour",
        [$userId]
    );

    $peakEnergyHours = [];
    $lowEnergyHours = [];
    $optimalFocusHours = [];

    foreach ($energyLogs as $log) {
        $hour = intval($log['hour']);
        $energy = floatval($log['avg_energy']);
        $focus = floatval($log['avg_focus']);

        if ($energy >= 75) {
            $peakEnergyHours[] = $hour;
        }
        if ($energy <= 40) {
            $lowEnergyHours[] = $hour;
        }
        if ($focus >= 75) {
            $optimalFocusHours[] = $hour;
        }
    }

    $recommendations = [
        'peak_performance_hours' => $peakEnergyHours,
        'low_energy_hours' => $lowEnergyHours,
        'optimal_focus_hours' => $optimalFocusHours,
        'suggested_schedule' => [
            [
                'task_type' => 'Deep Work / Complex Tasks',
                'recommended_hours' => array_slice($optimalFocusHours, 0, 3),
                'reason' => 'Your focus is highest during these times'
            ],
            [
                'task_type' => 'Meetings & Collaboration',
                'recommended_hours' => array_slice($peakEnergyHours, 0, 3),
                'reason' => 'High energy levels support social interaction'
            ],
            [
                'task_type' => 'Administrative Tasks',
                'recommended_hours' => $lowEnergyHours,
                'reason' => 'Low-energy tasks for low-energy times'
            ],
            [
                'task_type' => 'Breaks & Rest',
                'recommended_hours' => $lowEnergyHours,
                'reason' => 'Recharge during natural energy dips'
            ]
        ],
        'work_rhythm_insights' => [
            'Your peak performance window is typically ' . (count($peakEnergyHours) > 0 ? implode(', ', array_slice($peakEnergyHours, 0, 3)) . ':00' : 'not yet determined'),
            'Consider scheduling important work during ' . (count($optimalFocusHours) > 0 ? implode(', ', array_slice($optimalFocusHours, 0, 2)) . ':00' : 'your focus hours'),
            'You tend to have lower energy around ' . (count($lowEnergyHours) > 0 ? implode(', ', array_slice($lowEnergyHours, 0, 2)) . ':00' : 'certain hours')
        ]
    ];

    echo json_encode([
        'success' => true,
        'recommendations' => $recommendations,
        'energy_pattern' => $energyLogs
    ]);
} catch (Exception $e) {
    error_log("Optimal schedule error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to generate optimal schedule'
    ]);
}
