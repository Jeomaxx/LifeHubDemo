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

$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$reminderType = trim($_POST['reminder_type'] ?? 'time_based');

if (empty($title)) {
    echo json_encode(['success' => false, 'message' => 'Title is required']);
    exit;
}

if (strlen($title) > 255) {
    echo json_encode(['success' => false, 'message' => 'Title too long']);
    exit;
}

try {
    $reminderId = $db->insert('smart_reminders', [
        'user_id' => $userId,
        'title' => $title,
        'description' => $description,
        'reminder_type' => $reminderType,
        'trigger_config' => json_encode([]),
        'is_active' => true
    ]);

    echo json_encode([
        'success' => true,
        'reminder_id' => $reminderId,
        'message' => 'Reminder created successfully'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to create reminder'
    ]);
}
