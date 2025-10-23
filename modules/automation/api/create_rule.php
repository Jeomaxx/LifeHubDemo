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

$ruleName = trim($_POST['rule_name'] ?? '');
$description = trim($_POST['description'] ?? '');
$triggerType = trim($_POST['trigger_type'] ?? '');
$actionType = trim($_POST['action_type'] ?? '');
$triggerConfig = $_POST['trigger_config'] ?? '{}';
$actionConfig = $_POST['action_config'] ?? '{}';

if (empty($ruleName) || empty($triggerType) || empty($actionType)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

if (strlen($ruleName) > 255) {
    echo json_encode(['success' => false, 'message' => 'Rule name too long']);
    exit;
}

try {
    if (!is_string($triggerConfig)) {
        $triggerConfig = json_encode($triggerConfig);
    }
    if (!is_string($actionConfig)) {
        $actionConfig = json_encode($actionConfig);
    }
    
    $ruleId = $db->insert('automation_rules', [
        'user_id' => $userId,
        'rule_name' => $ruleName,
        'description' => $description,
        'trigger_type' => $triggerType,
        'trigger_config' => $triggerConfig,
        'action_type' => $actionType,
        'action_config' => $actionConfig,
        'is_active' => true
    ]);

    echo json_encode([
        'success' => true,
        'rule_id' => $ruleId,
        'message' => 'Automation rule created successfully'
    ]);
} catch (Exception $e) {
    error_log("Automation rule creation error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to create automation rule'
    ]);
}
