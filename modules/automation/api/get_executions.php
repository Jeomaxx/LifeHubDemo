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

try {
    $executions = $db->fetchAll(
        "SELECT al.*, ar.rule_name 
         FROM automation_logs al 
         JOIN automation_rules ar ON al.rule_id = ar.id
         WHERE al.user_id = ? 
         ORDER BY al.executed_at DESC 
         LIMIT 20",
        [$userId]
    );

    echo json_encode([
        'success' => true,
        'executions' => $executions
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch execution history'
    ]);
}
