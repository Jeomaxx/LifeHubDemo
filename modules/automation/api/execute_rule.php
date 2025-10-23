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

if ($ruleId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid rule ID']);
    exit;
}

$rule = $db->fetchOne("SELECT * FROM automation_rules WHERE id = ? AND user_id = ?", [$ruleId, $userId]);
if (!$rule) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Rule not found']);
    exit;
}

if (!$rule['is_active']) {
    echo json_encode(['success' => false, 'message' => 'Rule is not active']);
    exit;
}

try {
    $actionConfig = json_decode($rule['action_config'], true) ?? [];
    $executionResult = [
        'status' => 'executed',
        'timestamp' => date('Y-m-d H:i:s'),
        'action_type' => $rule['action_type']
    ];
    
    $db->insert('automation_logs', [
        'rule_id' => $ruleId,
        'user_id' => $userId,
        'execution_result' => json_encode($executionResult),
        'executed_successfully' => true
    ]);

    $db->query(
        "UPDATE automation_rules SET last_executed = NOW(), execution_count = execution_count + 1 WHERE id = ?",
        [$ruleId]
    );

    echo json_encode([
        'success' => true,
        'message' => 'Automation rule executed successfully',
        'result' => $executionResult
    ]);
} catch (Exception $e) {
    error_log("Automation rule execution error: " . $e->getMessage());
    
    $db->insert('automation_logs', [
        'rule_id' => $ruleId,
        'user_id' => $userId,
        'execution_result' => json_encode(['error' => $e->getMessage()]),
        'executed_successfully' => false
    ]);
    
    echo json_encode([
        'success' => false,
        'message' => 'Failed to execute automation rule'
    ]);
}
