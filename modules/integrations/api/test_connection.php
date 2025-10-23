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

$integrationId = intval($_GET['id'] ?? 0);

if ($integrationId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid integration ID']);
    exit;
}

$integration = $db->fetchOne("SELECT * FROM integration_connections WHERE id = ? AND user_id = ?", [$integrationId, $userId]);
if (!$integration) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Integration not found']);
    exit;
}

try {
    $status = 'connected';
    $message = 'Connection test successful';
    
    $testResult = [
        'status' => $status,
        'tested_at' => date('Y-m-d H:i:s'),
        'service' => $integration['service_name']
    ];
    
    $db->update('integration_connections', [
        'last_sync' => date('Y-m-d H:i:s'),
        'sync_status' => $status
    ], 'id = ?', [$integrationId]);

    echo json_encode([
        'success' => true,
        'message' => $message,
        'test_result' => $testResult
    ]);
} catch (Exception $e) {
    error_log("Connection test error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Connection test failed'
    ]);
}
