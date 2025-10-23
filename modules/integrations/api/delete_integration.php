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

$integrationId = intval($_POST['id'] ?? $_GET['id'] ?? 0);

if ($integrationId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid integration ID']);
    exit;
}

$existing = $db->fetchOne("SELECT * FROM integration_connections WHERE id = ? AND user_id = ?", [$integrationId, $userId]);
if (!$existing) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Integration not found']);
    exit;
}

try {
    $db->delete('integration_connections', 'id = ? AND user_id = ?', [$integrationId, $userId]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Integration deleted successfully'
    ]);
} catch (Exception $e) {
    error_log("Integration deletion error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to delete integration'
    ]);
}
