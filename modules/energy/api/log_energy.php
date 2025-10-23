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

$energyLevel = intval($_POST['energy_level'] ?? 5);
$focusLevel = intval($_POST['focus_level'] ?? 5);

if ($energyLevel < 1 || $energyLevel > 10 || $focusLevel < 1 || $focusLevel > 10) {
    echo json_encode(['success' => false, 'message' => 'Energy and focus levels must be between 1 and 10']);
    exit;
}

try {
    $logId = $db->insert('energy_logs', [
        'user_id' => $userId,
        'log_date' => date('Y-m-d'),
        'log_time' => date('H:i:s'),
        'energy_level' => $energyLevel,
        'focus_level' => $focusLevel
    ]);

    echo json_encode([
        'success' => true,
        'log_id' => $logId,
        'message' => 'Energy logged successfully'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to log energy'
    ]);
}
