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

$scenarioId = intval($_POST['scenario_id'] ?? 0);
$currentSleep = floatval($_POST['current_sleep'] ?? 7);
$targetSleep = floatval($_POST['target_sleep'] ?? 8);
$currentExercise = intval($_POST['current_exercise'] ?? 2);
$targetExercise = intval($_POST['target_exercise'] ?? 5);
$weeks = intval($_POST['weeks'] ?? 12);

if ($scenarioId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid scenario ID']);
    exit;
}

try {
    $projections = [];
    $stressScore = 70;
    $energyScore = 60;
    $productivityScore = 65;
    
    for ($week = 0; $week <= $weeks; $week++) {
        $progress = $week / $weeks;
        
        $currentWeekSleep = $currentSleep + ($targetSleep - $currentSleep) * $progress;
        $currentWeekExercise = $currentExercise + ($targetExercise - $currentExercise) * $progress;
        
        $sleepImpact = ($currentWeekSleep - 6) * 8;
        $exerciseImpact = $currentWeekExercise * 3;
        
        $stressScore = max(0, min(100, 70 - ($sleepImpact + $exerciseImpact) * 0.5));
        $energyScore = min(100, 60 + $sleepImpact + $exerciseImpact);
        $productivityScore = min(100, 65 + ($sleepImpact + $exerciseImpact) * 0.8);
        
        $projections[] = [
            'week' => $week,
            'sleep_hours' => round($currentWeekSleep, 1),
            'exercise_days' => round($currentWeekExercise, 1),
            'stress_score' => round($stressScore, 1),
            'energy_score' => round($energyScore, 1),
            'productivity_score' => round($productivityScore, 1)
        ];
    }
    
    $results = [
        'projections' => $projections,
        'final_metrics' => [
            'stress_reduction' => round(70 - $stressScore, 1),
            'energy_increase' => round($energyScore - 60, 1),
            'productivity_increase' => round($productivityScore - 65, 1)
        ],
        'recommendations' => [
            'Increasing sleep from ' . $currentSleep . ' to ' . $targetSleep . ' hours can reduce stress by ' . round((70 - $stressScore) / 70 * 100, 1) . '%',
            'Adding ' . ($targetExercise - $currentExercise) . ' exercise days per week can boost energy by ' . round(($energyScore - 60) / 60 * 100, 1) . '%',
            'These changes could improve productivity by ' . round(($productivityScore - 65) / 65 * 100, 1) . '%'
        ],
        'parameters' => [
            'current_sleep' => $currentSleep,
            'target_sleep' => $targetSleep,
            'current_exercise' => $currentExercise,
            'target_exercise' => $targetExercise,
            'weeks' => $weeks
        ]
    ];

    $db->update('scenario_simulations', [
        'status' => 'completed',
        'results' => json_encode($results),
        'run_at' => date('Y-m-d H:i:s')
    ], 'id = ? AND user_id = ?', [$scenarioId, $userId]);

    echo json_encode([
        'success' => true,
        'results' => $results,
        'message' => 'Health simulation completed'
    ]);
} catch (Exception $e) {
    error_log("Health simulation error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Simulation failed'
    ]);
}
