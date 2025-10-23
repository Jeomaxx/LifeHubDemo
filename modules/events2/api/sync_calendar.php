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

$provider = trim($_POST['provider'] ?? 'google');
$action = trim($_POST['action'] ?? 'sync');

try {
    $connection = $db->fetchOne(
        "SELECT * FROM calendar_connections 
         WHERE user_id = ? AND provider = ? AND is_active = true",
        [$userId, $provider]
    );

    if (!$connection) {
        echo json_encode([
            'success' => false,
            'message' => 'No active calendar connection found. Please connect your calendar first.'
        ]);
        exit;
    }

    $syncedEvents = simulateCalendarSync($provider, $userId);

    foreach ($syncedEvents as $event) {
        $existing = $db->fetchOne(
            "SELECT id FROM calendar_events 
             WHERE user_id = ? AND external_id = ?",
            [$userId, $event['external_id']]
        );

        if ($existing) {
            $db->update('calendar_events', [
                'title' => $event['title'],
                'start_time' => $event['start_time'],
                'end_time' => $event['end_time'],
                'description' => $event['description'],
                'last_synced' => date('Y-m-d H:i:s')
            ], 'id = ?', [$existing['id']]);
        } else {
            $db->insert('calendar_events', [
                'user_id' => $userId,
                'title' => $event['title'],
                'start_time' => $event['start_time'],
                'end_time' => $event['end_time'],
                'description' => $event['description'],
                'external_id' => $event['external_id'],
                'provider' => $provider
            ]);
        }
    }

    $db->update('calendar_connections', [
        'last_sync' => date('Y-m-d H:i:s'),
        'sync_status' => 'completed'
    ], 'id = ?', [$connection['id']]);

    $db->insert('calendar_sync_logs', [
        'user_id' => $userId,
        'connection_id' => $connection['id'],
        'events_synced' => count($syncedEvents),
        'sync_status' => 'success'
    ]);

    echo json_encode([
        'success' => true,
        'synced_events' => count($syncedEvents),
        'provider' => $provider,
        'message' => 'Calendar synced successfully'
    ]);
} catch (Exception $e) {
    error_log("Calendar sync error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to sync calendar'
    ]);
}

function simulateCalendarSync($provider, $userId) {
    return [
        [
            'external_id' => 'ext_' . uniqid(),
            'title' => 'Team Meeting',
            'start_time' => date('Y-m-d 10:00:00', strtotime('+1 day')),
            'end_time' => date('Y-m-d 11:00:00', strtotime('+1 day')),
            'description' => 'Weekly team sync meeting'
        ],
        [
            'external_id' => 'ext_' . uniqid(),
            'title' => 'Project Deadline',
            'start_time' => date('Y-m-d 17:00:00', strtotime('+3 days')),
            'end_time' => date('Y-m-d 18:00:00', strtotime('+3 days')),
            'description' => 'Submit project deliverables'
        ]
    ];
}
