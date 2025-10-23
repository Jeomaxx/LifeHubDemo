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

$ruleId = intval($_POST['rule_id'] ?? 0);
$ruleName = trim($_POST['rule_name'] ?? '');
$description = trim($_POST['description'] ?? '');
$triggerType = trim($_POST['trigger_type'] ?? '');
$actionType = trim($_POST['action_type'] ?? '');
$triggerConfig = $_POST['trigger_config'] ?? '{}';
$actionConfig = $_POST['action_config'] ?? '{}';
$isActive = isset($_POST['is_active']) ? filter_var($_POST['is_active'], FILTER_VALIDATE_BOOLEAN) : true;

if ($ruleId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid rule ID']);
    exit;
}

$existing = $db->fetchOne("SELECT * FROM automation_rules WHERE id = ? AND user_id = ?", [$ruleId, $userId]);
if (!$existing) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Rule not found']);
    exit;
}

if (empty($ruleName) || empty($triggerType) || empty($actionType)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

try {
    if (!is_string($triggerConfig)) {
        $triggerConfig = json_encode($triggerConfig);
    }
    if (!is_string($actionConfig)) {
        $actionConfig = json_encode($actionConfig);
    }
    
    $db->update('automation_rules', [
        'rule_name' => $ruleName,
        'description' => $description,
        'trigger_type' => $triggerType,
        'trigger_config' => $triggerConfig,
        'action_type' => $actionType,
        'action_config' => $actionConfig,
        'is_active' => $isActive
    ], ['id' => $ruleId, 'user_id' => $userId]);

    echo json_encode([
        'success' => true,
        'message' => 'Automation rule updated successfully'
    ]);
} catch (Exception $e) {
    error_log("Automation rule update error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update automation rule'
    ]);
}
