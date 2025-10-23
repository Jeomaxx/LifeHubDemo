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

if (!verifyCsrfToken($_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

$db = getDB();
$userId = $auth->getUserId();

$ruleId = intval($_POST['id'] ?? $_GET['id'] ?? 0);

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

try {
    $db->delete('automation_rules', ['id' => $ruleId, 'user_id' => $userId]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Automation rule deleted successfully'
    ]);
} catch (Exception $e) {
    error_log("Automation rule deletion error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to delete automation rule'
    ]);
}
