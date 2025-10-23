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

$serviceName = trim($_POST['service_name'] ?? '');
$serviceType = trim($_POST['service_type'] ?? '');
$credentials = $_POST['credentials'] ?? '';
$settings = $_POST['settings'] ?? '{}';

if (empty($serviceName) || empty($serviceType)) {
    echo json_encode(['success' => false, 'message' => 'Service name and type are required']);
    exit;
}

try {
    if (!is_string($credentials)) {
        $credentials = json_encode($credentials);
    }
    if (!is_string($settings)) {
        $settings = json_encode($settings);
    }

    $integrationId = $db->insert('integration_connections', [
        'user_id' => $userId,
        'service_name' => $serviceName,
        'service_type' => $serviceType,
        'credentials' => $credentials,
        'settings' => $settings,
        'is_active' => true
    ]);

    echo json_encode([
        'success' => true,
        'integration_id' => $integrationId,
        'message' => 'Integration created successfully'
    ]);
} catch (Exception $e) {
    error_log("Integration creation error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to create integration'
    ]);
}
