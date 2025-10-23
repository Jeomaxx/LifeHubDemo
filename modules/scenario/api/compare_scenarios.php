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

$scenarioIds = $_GET['ids'] ?? '';

if (empty($scenarioIds)) {
    echo json_encode(['success' => false, 'message' => 'Scenario IDs are required']);
    exit;
}

$ids = explode(',', $scenarioIds);
$ids = array_map('intval', $ids);

try {
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    
    $scenarios = $db->fetchAll(
        "SELECT * FROM scenario_simulations 
         WHERE id IN ($placeholders) AND user_id = ?
         ORDER BY created_at DESC",
        array_merge($ids, [$userId])
    );

    if (empty($scenarios)) {
        echo json_encode(['success' => false, 'message' => 'No scenarios found']);
        exit;
    }

    $comparison = [
        'scenarios' => [],
        'comparison_metrics' => []
    ];

    foreach ($scenarios as $scenario) {
        $results = json_decode($scenario['results'], true) ?? [];
        
        $comparison['scenarios'][] = [
            'id' => $scenario['id'],
            'title' => $scenario['scenario_title'],
            'type' => $scenario['scenario_type'],
            'results' => $results
        ];
    }

    echo json_encode([
        'success' => true,
        'comparison' => $comparison
    ]);
} catch (Exception $e) {
    error_log("Scenario comparison error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to compare scenarios'
    ]);
}
