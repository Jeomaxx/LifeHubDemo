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

$title = trim($_POST['title'] ?? '');
$scenarioType = trim($_POST['scenario_type'] ?? '');
$description = trim($_POST['description'] ?? '');
$parameters = $_POST['parameters'] ?? '{}';

if (empty($title) || empty($scenarioType)) {
    echo json_encode(['success' => false, 'message' => 'Title and scenario type are required']);
    exit;
}

try {
    if (!is_string($parameters)) {
        $parameters = json_encode($parameters);
    }

    $scenarioId = $db->insert('scenario_simulations', [
        'user_id' => $userId,
        'scenario_title' => $title,
        'scenario_type' => $scenarioType,
        'description' => $description,
        'parameters' => $parameters,
        'status' => 'draft'
    ]);

    echo json_encode([
        'success' => true,
        'scenario_id' => $scenarioId,
        'message' => 'Scenario created successfully'
    ]);
} catch (Exception $e) {
    error_log("Scenario creation error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to create scenario'
    ]);
}
