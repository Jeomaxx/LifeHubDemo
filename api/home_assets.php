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
$type = $_GET['type'] ?? 'assets'; // assets or maintenance

try {
    switch ($method) {
        case 'GET':
            if ($type === 'assets') {
                $category = $_GET['category'] ?? '';
                if ($category) {
                    $assets = $db->fetchAll(
                        "SELECT * FROM home_assets WHERE user_id = ? AND category = ? ORDER BY created_at DESC",
                        [$userId, $category]
                    );
                } else {
                    $assets = $db->fetchAll(
                        "SELECT * FROM home_assets WHERE user_id = ? ORDER BY created_at DESC",
                        [$userId]
                    );
                }
                echo json_encode(['success' => true, 'assets' => $assets]);
            } elseif ($type === 'maintenance' && isset($_GET['asset_id'])) {
                $logs = $db->fetchAll(
                    "SELECT * FROM maintenance_logs WHERE asset_id = ? AND user_id = ? ORDER BY maintenance_date DESC",
                    [$_GET['asset_id'], $userId]
                );
                echo json_encode(['success' => true, 'logs' => $logs]);
            } elseif ($action === 'upcoming_maintenance') {
                $assets = $db->fetchAll(
                    "SELECT * FROM home_assets 
                    WHERE user_id = ? AND next_maintenance IS NOT NULL 
                    AND next_maintenance <= CURRENT_DATE + INTERVAL '30 days'
                    ORDER BY next_maintenance",
                    [$userId]
                );
                echo json_encode(['success' => true, 'assets' => $assets]);
            } elseif ($action === 'expiring_warranties') {
                $assets = $db->fetchAll(
                    "SELECT * FROM home_assets 
                    WHERE user_id = ? AND warranty_expiry IS NOT NULL 
                    AND warranty_expiry <= CURRENT_DATE + INTERVAL '60 days'
                    AND warranty_expiry >= CURRENT_DATE
                    ORDER BY warranty_expiry",
                    [$userId]
                );
                echo json_encode(['success' => true, 'assets' => $assets]);
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            if ($type === 'assets' && $action === 'create') {
                $id = $db->insert('home_assets', [
                    'user_id' => $userId,
                    'asset_name' => $data['asset_name'],
                    'category' => $data['category'] ?? null,
                    'purchase_date' => $data['purchase_date'] ?? null,
                    'purchase_price' => $data['purchase_price'] ?? null,
                    'warranty_expiry' => $data['warranty_expiry'] ?? null,
                    'maintenance_schedule' => $data['maintenance_schedule'] ?? null,
                    'last_maintenance' => $data['last_maintenance'] ?? null,
                    'next_maintenance' => $data['next_maintenance'] ?? null,
                    'location' => $data['location'] ?? null,
                    'serial_number' => $data['serial_number'] ?? null,
                    'notes' => $data['notes'] ?? null
                ]);
                echo json_encode(['success' => true, 'id' => $id, 'message' => 'Asset added successfully']);
            } elseif ($type === 'maintenance' && $action === 'log') {
                $id = $db->insert('maintenance_logs', [
                    'asset_id' => $data['asset_id'],
                    'user_id' => $userId,
                    'maintenance_date' => $data['maintenance_date'],
                    'description' => $data['description'] ?? null,
                    'cost' => $data['cost'] ?? null,
                    'performed_by' => $data['performed_by'] ?? null,
                    'notes' => $data['notes'] ?? null
                ]);
                
                // Update asset last_maintenance
                $db->execute(
                    "UPDATE home_assets SET last_maintenance = ? WHERE id = ? AND user_id = ?",
                    [$data['maintenance_date'], $data['asset_id'], $userId]
                );
                
                echo json_encode(['success' => true, 'id' => $id, 'message' => 'Maintenance logged successfully']);
            }
            break;

        case 'DELETE':
            if ($type === 'assets' && isset($_GET['id'])) {
                $db->execute("DELETE FROM home_assets WHERE id = ? AND user_id = ?", [$_GET['id'], $userId]);
                echo json_encode(['success' => true, 'message' => 'Asset deleted successfully']);
            } elseif ($type === 'maintenance' && isset($_GET['id'])) {
                $db->execute("DELETE FROM maintenance_logs WHERE id = ? AND user_id = ?", [$_GET['id'], $userId]);
                echo json_encode(['success' => true, 'message' => 'Maintenance log deleted successfully']);
            }
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
