<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'get':
            $events = $db->fetchAll("SELECT * FROM events WHERE user_id = ? ORDER BY event_date DESC", [$userId]);
            jsonResponse(['success' => true, 'events' => $events]);
            break;
            
        case 'add':
            if ($method !== 'POST') jsonResponse(['success' => false, 'message' => 'Invalid method'], 405);
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            $id = $db->insert('events', [
                'user_id' => $userId,
                'name' => sanitize($data['name']),
                'event_type' => $data['event_type'],
                'description' => sanitize($data['description'] ?? ''),
                'event_date' => $data['event_date'],
                'event_time' => $data['event_time'] ?? null,
                'location' => sanitize($data['location'] ?? ''),
                'budget' => $data['budget'] ?? 0,
                'status' => 'planning'
            ]);
            
            jsonResponse(['success' => true, 'id' => $id, 'message' => 'Event created successfully']);
            break;
            
        case 'update':
            if ($method !== 'POST') jsonResponse(['success' => false, 'message' => 'Invalid method'], 405);
            
            $data = json_decode(file_get_contents('php://input'), true);
            $id = (int)$data['id'];
            
            $db->update('events', [
                'name' => sanitize($data['name']),
                'event_type' => $data['event_type'],
                'description' => sanitize($data['description'] ?? ''),
                'event_date' => $data['event_date'],
                'event_time' => $data['event_time'] ?? null,
                'location' => sanitize($data['location'] ?? ''),
                'budget' => $data['budget'] ?? 0,
                'status' => $data['status'] ?? 'planning',
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = ? AND user_id = ?', [$id, $userId]);
            
            jsonResponse(['success' => true, 'message' => 'Event updated successfully']);
            break;
            
        case 'delete':
            if ($method !== 'POST') jsonResponse(['success' => false, 'message' => 'Invalid method'], 405);
            
            $data = json_decode(file_get_contents('php://input'), true);
            $id = (int)$data['id'];
            
            $db->query("DELETE FROM events WHERE id = ? AND user_id = ?", [$id, $userId]);
            jsonResponse(['success' => true, 'message' => 'Event deleted successfully']);
            break;
            
        case 'add_checklist_item':
            if ($method !== 'POST') jsonResponse(['success' => false, 'message' => 'Invalid method'], 405);
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            $id = $db->insert('event_checklists', [
                'event_id' => $data['event_id'],
                'item_text' => sanitize($data['item_text']),
                'due_date' => $data['due_date'] ?? null,
                'position' => $data['position'] ?? 0
            ]);
            
            jsonResponse(['success' => true, 'id' => $id, 'message' => 'Checklist item added']);
            break;
            
        case 'add_guest':
            if ($method !== 'POST') jsonResponse(['success' => false, 'message' => 'Invalid method'], 405);
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            $id = $db->insert('event_guests', [
                'event_id' => $data['event_id'],
                'contact_id' => $data['contact_id'] ?? null,
                'name' => sanitize($data['name']),
                'email' => sanitize($data['email'] ?? ''),
                'phone' => sanitize($data['phone'] ?? ''),
                'rsvp_status' => 'pending'
            ]);
            
            jsonResponse(['success' => true, 'id' => $id, 'message' => 'Guest added']);
            break;
            
        default:
            jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
    }
} catch (Exception $e) {
    error_log("Events API Error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'An error occurred'], 500);
}
