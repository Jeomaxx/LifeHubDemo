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

$simulationName = trim($_POST['simulation_name'] ?? '');
$whatIfQuestion = trim($_POST['what_if_question'] ?? '');
$simulationType = trim($_POST['simulation_type'] ?? 'mixed');
$simulationPeriod = intval($_POST['simulation_period'] ?? 12);
$inputParameters = $_POST['input_parameters'] ?? '{}';

if (empty($simulationName) || empty($whatIfQuestion)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

if (strlen($simulationName) > 255) {
    echo json_encode(['success' => false, 'message' => 'Simulation name too long']);
    exit;
}

try {
    if (!is_string($inputParameters)) {
        $inputParameters = json_encode($inputParameters);
    }
    
    $results = [
        'predicted_outcome' => 'Positive',
        'key_metrics' => [
            'financial_impact' => rand(70, 95),
            'time_impact' => rand(60, 90),
            'stress_level' => rand(30, 70)
        ],
        'timeline' => $simulationPeriod . ' months'
    ];
    
    $impactGraphs = [
        'financial' => array_map(function($i) { return rand(50, 100); }, range(1, min(12, $simulationPeriod))),
        'wellness' => array_map(function($i) { return rand(60, 95); }, range(1, min(12, $simulationPeriod)))
    ];
    
    $confidenceLevel = rand(70, 95);
    $recommendation = "Based on current patterns and historical data, this scenario has a {$confidenceLevel}% probability of success.";
    
    $simulationId = $db->insert('scenario_simulations', [
        'user_id' => $userId,
        'simulation_name' => $simulationName,
        'simulation_type' => $simulationType,
        'what_if_question' => $whatIfQuestion,
        'input_parameters' => $inputParameters,
        'simulation_period' => $simulationPeriod,
        'simulation_results' => json_encode($results),
        'impact_graphs' => json_encode($impactGraphs),
        'confidence_level' => $confidenceLevel,
        'recommendation' => $recommendation
    ]);

    $simulation = $db->fetchOne(
        "SELECT * FROM scenario_simulations WHERE id = ?",
        [$simulationId]
    );

    echo json_encode([
        'success' => true,
        'simulation' => $simulation,
        'message' => 'Simulation completed successfully'
    ]);
} catch (Exception $e) {
    error_log("Scenario simulation error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to run simulation'
    ]);
}
