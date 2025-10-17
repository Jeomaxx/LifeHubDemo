<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = $auth->getUserId();
$db = Database::getInstance();
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            $type = $_GET['type'] ?? 'trips';
            
            if ($type === 'trips') {
                $trips = $db->fetchAll("SELECT * FROM trips WHERE user_id = ? ORDER BY start_date DESC", [$userId]);
                echo json_encode(['success' => true, 'data' => $trips]);
            } elseif ($type === 'itinerary' && isset($_GET['trip_id'])) {
                $itinerary = $db->fetchAll("SELECT * FROM trip_itinerary WHERE trip_id = ? ORDER BY day_number", [$_GET['trip_id']]);
                echo json_encode(['success' => true, 'data' => $itinerary]);
            } elseif ($type === 'packing' && isset($_GET['trip_id'])) {
                $packing = $db->fetchAll("SELECT * FROM packing_lists WHERE trip_id = ? ORDER BY category", [$_GET['trip_id']]);
                echo json_encode(['success' => true, 'data' => $packing]);
            } elseif ($type === 'journal' && isset($_GET['trip_id'])) {
                $journal = $db->fetchAll("SELECT * FROM travel_journal WHERE trip_id = ? ORDER BY entry_date DESC", [$_GET['trip_id']]);
                echo json_encode(['success' => true, 'data' => $journal]);
            }
            break;
            
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $type = $data['type'] ?? 'trip';
            
            if ($type === 'trip') {
                $id = $db->insert('trips', [
                    'user_id' => $userId,
                    'destination' => $data['destination'],
                    'country' => $data['country'] ?? '',
                    'start_date' => $data['start_date'],
                    'end_date' => $data['end_date'],
                    'budget' => $data['budget'] ?? 0,
                    'trip_type' => $data['trip_type'] ?? '',
                    'status' => $data['status'] ?? 'planned',
                    'notes' => $data['notes'] ?? ''
                ]);
                echo json_encode(['success' => true, 'id' => $id]);
            } elseif ($type === 'itinerary') {
                $id = $db->insert('trip_itinerary', [
                    'trip_id' => $data['trip_id'],
                    'day_number' => $data['day_number'],
                    'date' => $data['date'],
                    'title' => $data['title'] ?? '',
                    'description' => $data['description'] ?? '',
                    'location' => $data['location'] ?? '',
                    'cost' => $data['cost'] ?? 0
                ]);
                echo json_encode(['success' => true, 'id' => $id]);
            } elseif ($type === 'journal') {
                $id = $db->insert('travel_journal', [
                    'trip_id' => $data['trip_id'],
                    'user_id' => $userId,
                    'entry_date' => $data['entry_date'],
                    'title' => $data['title'] ?? '',
                    'content' => $data['content'],
                    'location' => $data['location'] ?? '',
                    'mood' => $data['mood'] ?? ''
                ]);
                echo json_encode(['success' => true, 'id' => $id]);
            }
            break;
            
        case 'DELETE':
            $id = (int)($_GET['id'] ?? 0);
            $type = $_GET['type'] ?? 'trip';
            
            if ($type === 'trip') $db->delete('trips', $id);
            elseif ($type === 'itinerary') $db->delete('trip_itinerary', $id);
            elseif ($type === 'journal') $db->delete('travel_journal', $id);
            
            echo json_encode(['success' => true]);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
