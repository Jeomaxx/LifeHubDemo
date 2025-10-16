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
            if ($action === 'list') {
                $gifts = $db->fetchAll(
                    "SELECT * FROM gifts WHERE user_id = ? ORDER BY created_at DESC",
                    [$userId]
                );
                echo json_encode(['success' => true, 'gifts' => $gifts]);
            } elseif ($action === 'single' && isset($_GET['id'])) {
                $gift = $db->fetchOne(
                    "SELECT * FROM gifts WHERE id = ? AND user_id = ?",
                    [$_GET['id'], $userId]
                );
                echo json_encode(['success' => true, 'gift' => $gift]);
            } elseif ($action === 'by_recipient') {
                $recipient = $_GET['recipient'] ?? '';
                $gifts = $db->fetchAll(
                    "SELECT * FROM gifts WHERE user_id = ? AND recipient_name LIKE ? ORDER BY occasion",
                    [$userId, "%$recipient%"]
                );
                echo json_encode(['success' => true, 'gifts' => $gifts]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            if ($action === 'create') {
                $id = $db->insert('gifts', [
                    'user_id' => $userId,
                    'gift_name' => $data['gift_name'],
                    'recipient_name' => $data['recipient_name'],
                    'occasion' => $data['occasion'] ?? null,
                    'provider_link' => $data['provider_link'] ?? null,
                    'price' => $data['price'] ?? null,
                    'notes' => $data['notes'] ?? null,
                    'event_id' => $data['event_id'] ?? null,
                    'event_type' => $data['event_type'] ?? null,
                    'purchased' => $data['purchased'] ?? false
                ]);
                
                echo json_encode(['success' => true, 'id' => $id, 'message' => 'Gift added successfully']);
            } elseif ($action === 'update' && isset($data['id'])) {
                $db->execute(
                    "UPDATE gifts SET 
                        gift_name = ?, recipient_name = ?, occasion = ?, provider_link = ?,
                        price = ?, notes = ?, event_id = ?, event_type = ?, purchased = ?,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE id = ? AND user_id = ?",
                    [
                        $data['gift_name'], $data['recipient_name'], $data['occasion'] ?? null,
                        $data['provider_link'] ?? null, $data['price'] ?? null, $data['notes'] ?? null,
                        $data['event_id'] ?? null, $data['event_type'] ?? null, $data['purchased'] ?? false,
                        $data['id'], $userId
                    ]
                );
                echo json_encode(['success' => true, 'message' => 'Gift updated successfully']);
            } elseif ($action === 'toggle_purchased' && isset($data['id'])) {
                $db->execute(
                    "UPDATE gifts SET purchased = NOT purchased WHERE id = ? AND user_id = ?",
                    [$data['id'], $userId]
                );
                echo json_encode(['success' => true, 'message' => 'Purchase status updated']);
            }
            break;

        case 'DELETE':
            if (isset($_GET['id'])) {
                $db->execute("DELETE FROM gifts WHERE id = ? AND user_id = ?", [$_GET['id'], $userId]);
                echo json_encode(['success' => true, 'message' => 'Gift deleted successfully']);
            }
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
