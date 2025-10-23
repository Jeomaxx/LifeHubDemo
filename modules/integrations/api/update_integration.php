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

$integrationId = intval($_POST['id'] ?? 0);
$isActive = isset($_POST['is_active']) ? (bool)$_POST['is_active'] : null;

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
    $updateData = [];
    
    if ($isActive !== null) {
        $updateData['is_active'] = $isActive;
    }
    if (isset($_POST['credentials'])) {
        $credentials = $_POST['credentials'];
        $updateData['credentials'] = is_string($credentials) ? $credentials : json_encode($credentials);
    }
    if (isset($_POST['settings'])) {
        $settings = $_POST['settings'];
        $updateData['settings'] = is_string($settings) ? $settings : json_encode($settings);
    }

    if (!empty($updateData)) {
        $db->update('integration_connections', $updateData, 'id = ? AND user_id = ?', [$integrationId, $userId]);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Integration updated successfully'
    ]);
} catch (Exception $e) {
    error_log("Integration update error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update integration'
    ]);
}
