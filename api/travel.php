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

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($action === 'add_entry' || $action === 'update_entry') {
        $id = $_POST['id'] ?? null;
        $title = $_POST['title'] ?? '';
        $city = $_POST['city'] ?? '';
        $country = $_POST['country'] ?? '';
        $entryDate = $_POST['entry_date'] ?? date('Y-m-d');
        $rating = $_POST['rating'] ?? null;
        $description = $_POST['description'] ?? '';
        $highlights = $_POST['highlights'] ?? '';
        $expenses = $_POST['expenses'] ?? null;
        
        $location = $city . ($country ? ', ' . $country : '');
        $content = $description;
        if ($highlights) {
            $content .= "\n\nHighlights:\n" . $highlights;
        }
        if ($expenses) {
            $content .= "\n\nExpenses: $" . $expenses;
        }
        
        if ($id) {
            $db->execute("UPDATE travel_journal SET title = ?, entry_date = ?, content = ?, location = ?, mood = ?, updated_at = NOW() WHERE id = ? AND user_id = ?",
                [$title, $entryDate, $content, $location, $rating, $id, $userId]);
        } else {
            $stmt = $db->execute("INSERT INTO travel_journal (user_id, entry_date, title, content, location, mood, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW()) RETURNING id",
                [$userId, $entryDate, $title, $content, $location, $rating]);
            $id = $stmt->fetchColumn();
        }
        
        echo json_encode(['success' => true, 'id' => $id]);
        
    } elseif ($action === 'get_entry') {
        $id = $_GET['id'] ?? 0;
        $entry = $db->fetchOne("SELECT * FROM travel_journal WHERE id = ? AND user_id = ?", [$id, $userId]);
        
        if ($entry) {
            $parts = explode(', ', $entry['location'], 2);
            $entry['city'] = $parts[0] ?? '';
            $entry['country'] = $parts[1] ?? '';
            $entry['rating'] = $entry['mood'] ?? '';
            
            preg_match('/^(.*?)(?:\n\nHighlights:\n(.*?))?(?:\n\nExpenses: \$([0-9.]+))?$/s', $entry['content'], $matches);
            $entry['description'] = $matches[1] ?? $entry['content'];
            $entry['highlights'] = $matches[2] ?? '';
            $entry['expenses'] = $matches[3] ?? '';
        }
        
        echo json_encode(['success' => true, 'entry' => $entry]);
        
    } elseif ($action === 'delete_entry') {
        $id = $_POST['id'] ?? 0;
        $db->execute("DELETE FROM travel_journal WHERE id = ? AND user_id = ?", [$id, $userId]);
        echo json_encode(['success' => true]);
        
    } elseif ($method === 'GET') {
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
        
    } elseif ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $type = $data['type'] ?? 'trip';
        
        if ($type === 'trip') {
            $stmt = $db->execute("INSERT INTO trips (user_id, destination, country, start_date, end_date, budget, trip_type, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?) RETURNING id",
                [$userId, $data['destination'], $data['country'] ?? '', $data['start_date'], $data['end_date'], $data['budget'] ?? 0, $data['trip_type'] ?? '', $data['status'] ?? 'planned', $data['notes'] ?? '']);
            $id = $stmt->fetchColumn();
            echo json_encode(['success' => true, 'id' => $id]);
        } elseif ($type === 'itinerary') {
            $stmt = $db->execute("INSERT INTO trip_itinerary (trip_id, day_number, date, title, description, location, cost) VALUES (?, ?, ?, ?, ?, ?, ?) RETURNING id",
                [$data['trip_id'], $data['day_number'], $data['date'], $data['title'] ?? '', $data['description'] ?? '', $data['location'] ?? '', $data['cost'] ?? 0]);
            $id = $stmt->fetchColumn();
            echo json_encode(['success' => true, 'id' => $id]);
        } elseif ($type === 'journal') {
            $stmt = $db->execute("INSERT INTO travel_journal (trip_id, user_id, entry_date, title, content, location, mood, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW()) RETURNING id",
                [$data['trip_id'], $userId, $data['entry_date'], $data['title'] ?? '', $data['content'], $data['location'] ?? '', $data['mood'] ?? '']);
            $id = $stmt->fetchColumn();
            echo json_encode(['success' => true, 'id' => $id]);
        }
        
    } elseif ($method === 'DELETE') {
        $id = (int)($_GET['id'] ?? 0);
        $type = $_GET['type'] ?? 'trip';
        
        if ($type === 'trip') $db->execute("DELETE FROM trips WHERE id = ? AND user_id = ?", [$id, $userId]);
        elseif ($type === 'itinerary') $db->execute("DELETE FROM trip_itinerary WHERE id = ?", [$id]);
        elseif ($type === 'journal') $db->execute("DELETE FROM travel_journal WHERE id = ? AND user_id = ?", [$id, $userId]);
        
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
