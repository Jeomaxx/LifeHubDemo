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
$reminderType = trim($_POST['reminder_type'] ?? 'time_based');
$triggerTime = $_POST['trigger_time'] ?? null;
$location = trim($_POST['location'] ?? '');
$contextCondition = trim($_POST['context_condition'] ?? '');
$isRecurring = isset($_POST['is_recurring']) ? (bool)$_POST['is_recurring'] : false;
$recurrencePattern = trim($_POST['recurrence_pattern'] ?? '');

if (empty($title)) {
    echo json_encode(['success' => false, 'message' => 'Title is required']);
    exit;
}

try {
    $reminderConfig = [
        'reminder_type' => $reminderType,
        'trigger_time' => $triggerTime,
        'location' => $location,
        'context_condition' => $contextCondition,
        'is_recurring' => $isRecurring,
        'recurrence_pattern' => $recurrencePattern
    ];

    $reminderId = $db->insert('smart_reminders', [
        'user_id' => $userId,
        'title' => $title,
        'reminder_type' => $reminderType,
        'config' => json_encode($reminderConfig),
        'is_active' => true
    ]);

    $reminderDescription = generateReminderDescription($reminderType, $reminderConfig);

    echo json_encode([
        'success' => true,
        'reminder_id' => $reminderId,
        'description' => $reminderDescription,
        'message' => 'Smart reminder created successfully'
    ]);
} catch (Exception $e) {
    error_log("Smart reminder creation error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to create smart reminder'
    ]);
}

function generateReminderDescription($type, $config) {
    switch ($type) {
        case 'time_based':
            return "Reminder set for " . ($config['trigger_time'] ?? 'specific time');
        case 'location_based':
            return "Reminder when you arrive at " . ($config['location'] ?? 'location');
        case 'context_based':
            return "Reminder when " . ($config['context_condition'] ?? 'condition is met');
        default:
            return "Smart reminder configured";
    }
}
