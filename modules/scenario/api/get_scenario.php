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
$scenarioId = $_GET['id'] ?? 0;

try {
    $scenario = $db->fetchOne(
        "SELECT * FROM scenario_simulations WHERE id = ? AND user_id = ?",
        [$scenarioId, $userId]
    );

    if (!$scenario) {
        echo json_encode(['success' => false, 'message' => 'Scenario not found']);
        exit;
    }

    echo json_encode([
        'success' => true,
        'scenario' => $scenario
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch scenario'
    ]);
}
