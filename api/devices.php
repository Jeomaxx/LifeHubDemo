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
                $devices = $db->fetchAll(
                    "SELECT * FROM user_devices WHERE user_id = ? ORDER BY last_accessed DESC",
                    [$userId]
                );
                echo json_encode(['success' => true, 'devices' => $devices]);
            } elseif ($action === 'sessions') {
                $sessions = $db->fetchAll(
                    "SELECT s.*, d.device_name, d.device_type FROM user_sessions s 
                    LEFT JOIN user_devices d ON s.device_id = d.id 
                    WHERE s.user_id = ? AND s.expires_at > CURRENT_TIMESTAMP 
                    ORDER BY s.created_at DESC",
                    [$userId]
                );
                echo json_encode(['success' => true, 'sessions' => $sessions]);
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            if ($action === 'register_device') {
                // Check if device already exists
                $existing = $db->fetchOne(
                    "SELECT id FROM user_devices WHERE user_id = ? AND ip_address = ? AND browser = ?",
                    [$userId, $data['ip_address'], $data['browser']]
                );
                
                if ($existing) {
                    // Update last accessed
                    $db->execute(
                        "UPDATE user_devices SET last_accessed = CURRENT_TIMESTAMP WHERE id = ?",
                        [$existing['id']]
                    );
                    echo json_encode(['success' => true, 'device_id' => $existing['id'], 'message' => 'Device updated']);
                } else {
                    $id = $db->insert('user_devices', [
                        'user_id' => $userId,
                        'device_name' => $data['device_name'] ?? 'Unknown Device',
                        'device_type' => $data['device_type'] ?? 'Unknown',
                        'browser' => $data['browser'] ?? 'Unknown',
                        'ip_address' => $data['ip_address'],
                        'last_accessed' => date('Y-m-d H:i:s')
                    ]);
                    echo json_encode(['success' => true, 'device_id' => $id, 'message' => 'Device registered']);
                }
            } elseif ($action === 'revoke_device' && isset($data['device_id'])) {
                $db->execute(
                    "UPDATE user_devices SET is_active = FALSE WHERE id = ? AND user_id = ?",
                    [$data['device_id'], $userId]
                );
                // Also delete all sessions for this device
                $db->execute(
                    "DELETE FROM user_sessions WHERE device_id = ? AND user_id = ?",
                    [$data['device_id'], $userId]
                );
                echo json_encode(['success' => true, 'message' => 'Device revoked successfully']);
            } elseif ($action === 'revoke_session' && isset($data['session_id'])) {
                $db->execute(
                    "DELETE FROM user_sessions WHERE id = ? AND user_id = ?",
                    [$data['session_id'], $userId]
                );
                echo json_encode(['success' => true, 'message' => 'Session revoked successfully']);
            }
            break;

        case 'DELETE':
            if (isset($_GET['id'])) {
                $db->execute("DELETE FROM user_devices WHERE id = ? AND user_id = ?", [$_GET['id'], $userId]);
                echo json_encode(['success' => true, 'message' => 'Device deleted successfully']);
            }
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
