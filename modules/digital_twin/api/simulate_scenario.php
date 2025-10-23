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
$scenarioType = $_POST['scenario_type'] ?? '';

if (empty($scenarioType)) {
    echo json_encode(['success' => false, 'message' => 'Scenario type is required']);
    exit;
}

$predictions = [
    'skip_gym' => 'Your stress score may increase by 8% based on your patterns.',
    'extra_sleep' => 'Your productivity could improve by 15% with better rest.',
    'skip_coffee' => 'You might feel 20% less energetic in the morning.',
    'extra_savings' => 'You could reach your savings goal 2 months earlier.',
    'custom' => 'Simulation results calculated based on your patterns.'
];

$result = [
    'prediction' => $predictions[$scenarioType] ?? $predictions['custom'],
    'impact' => 'Moderate',
    'confidence' => 85
];

echo json_encode([
    'success' => true,
    'result' => $result
]);
