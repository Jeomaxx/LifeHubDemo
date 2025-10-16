<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';

header('Content-Type: application/json');

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = $auth->getUserId();
$db = Database::getInstance();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($method) {
        case 'GET':
            if ($action === 'month') {
                $month = $_GET['month'] ?? date('m');
                $year = $_GET['year'] ?? date('Y');
                
                $events = $db->fetchAll(
                    "SELECT * FROM calendar_events 
                    WHERE user_id = ? 
                    AND DATE_PART('month', start_time) = ? 
                    AND DATE_PART('year', start_time) = ?
                    ORDER BY start_time",
                    [$userId, $month, $year]
                );
                echo json_encode(['success' => true, 'events' => $events]);
            } elseif ($action === 'day') {
                $date = $_GET['date'] ?? date('Y-m-d');
                
                $events = $db->fetchAll(
                    "SELECT * FROM calendar_events 
                    WHERE user_id = ? 
                    AND DATE(start_time) = ?
                    ORDER BY start_time",
                    [$userId, $date]
                );
                echo json_encode(['success' => true, 'events' => $events]);
            } elseif ($action === 'upcoming') {
                $days = (int)($_GET['days'] ?? 7);
                // Validate and cap the days value for safety
                if ($days < 1) $days = 1;
                if ($days > 365) $days = 365;
                
                $events = $db->fetchAll(
                    "SELECT * FROM calendar_events 
                    WHERE user_id = ? 
                    AND start_time BETWEEN CURRENT_TIMESTAMP AND CURRENT_TIMESTAMP + INTERVAL '1 day' * ?
                    ORDER BY start_time LIMIT 20",
                    [$userId, $days]
                );
                echo json_encode(['success' => true, 'events' => $events]);
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            if ($action === 'create') {
                $id = $db->insert('calendar_events', [
                    'user_id' => $userId,
                    'title' => $data['title'],
                    'description' => $data['description'] ?? null,
                    'event_type' => $data['event_type'] ?? null,
                    'start_time' => $data['start_time'],
                    'end_time' => $data['end_time'] ?? null,
                    'is_all_day' => $data['is_all_day'] ?? false,
                    'recurring' => $data['recurring'] ?? false,
                    'recurrence_pattern' => $data['recurrence_pattern'] ?? null,
                    'reminder_minutes' => $data['reminder_minutes'] ?? null
                ]);
                echo json_encode(['success' => true, 'id' => $id, 'message' => 'Event created successfully']);
            } elseif ($action === 'update' && isset($data['id'])) {
                $db->execute(
                    "UPDATE calendar_events SET 
                        title = ?, description = ?, event_type = ?, start_time = ?, end_time = ?,
                        is_all_day = ?, recurring = ?, recurrence_pattern = ?, reminder_minutes = ?,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE id = ? AND user_id = ?",
                    [
                        $data['title'], $data['description'] ?? null, $data['event_type'] ?? null,
                        $data['start_time'], $data['end_time'] ?? null, $data['is_all_day'] ?? false,
                        $data['recurring'] ?? false, $data['recurrence_pattern'] ?? null,
                        $data['reminder_minutes'] ?? null, $data['id'], $userId
                    ]
                );
                echo json_encode(['success' => true, 'message' => 'Event updated successfully']);
            }
            break;

        case 'DELETE':
            if (isset($_GET['id'])) {
                $db->execute("DELETE FROM calendar_events WHERE id = ? AND user_id = ?", [$_GET['id'], $userId]);
                echo json_encode(['success' => true, 'message' => 'Event deleted successfully']);
            }
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
