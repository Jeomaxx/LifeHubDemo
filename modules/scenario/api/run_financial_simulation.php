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
$monthlyInvestment = floatval($_POST['monthly_investment'] ?? 0);
$annualReturn = floatval($_POST['annual_return'] ?? 7);
$years = intval($_POST['years'] ?? 5);
$currentSavings = floatval($_POST['current_savings'] ?? 0);

if ($scenarioId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid scenario ID']);
    exit;
}

try {
    $monthlyRate = ($annualReturn / 100) / 12;
    $months = $years * 12;
    
    $projections = [];
    $balance = $currentSavings;
    
    for ($month = 0; $month <= $months; $month++) {
        if ($month > 0) {
            $balance = $balance * (1 + $monthlyRate) + $monthlyInvestment;
        }
        
        if ($month % 12 === 0) {
            $year = $month / 12;
            $projections[] = [
                'year' => $year,
                'month' => $month,
                'balance' => round($balance, 2),
                'total_invested' => round($currentSavings + ($monthlyInvestment * $month), 2),
                'total_returns' => round($balance - ($currentSavings + ($monthlyInvestment * $month)), 2)
            ];
        }
    }
    
    $results = [
        'final_balance' => round($balance, 2),
        'total_invested' => round($currentSavings + ($monthlyInvestment * $months), 2),
        'total_returns' => round($balance - ($currentSavings + ($monthlyInvestment * $months)), 2),
        'return_percentage' => round((($balance - ($currentSavings + ($monthlyInvestment * $months))) / ($currentSavings + ($monthlyInvestment * $months))) * 100, 2),
        'projections' => $projections,
        'parameters' => [
            'monthly_investment' => $monthlyInvestment,
            'annual_return' => $annualReturn,
            'years' => $years,
            'current_savings' => $currentSavings
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
        'message' => 'Financial simulation completed'
    ]);
} catch (Exception $e) {
    error_log("Financial simulation error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Simulation failed'
    ]);
}
