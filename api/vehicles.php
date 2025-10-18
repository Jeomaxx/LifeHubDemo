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
            $vehicles = $db->fetchAll("SELECT * FROM vehicles WHERE user_id = ? ORDER BY created_at DESC", [$userId]);
            jsonResponse(['success' => true, 'vehicles' => $vehicles]);
            break;
            
        case 'add':
            if ($method !== 'POST') jsonResponse(['success' => false, 'message' => 'Invalid method'], 405);
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            $id = $db->insert('vehicles', [
                'user_id' => $userId,
                'make' => sanitize($data['make']),
                'model' => sanitize($data['model']),
                'year' => $data['year'] ?? null,
                'vin' => sanitize($data['vin'] ?? ''),
                'license_plate' => sanitize($data['license_plate'] ?? ''),
                'current_mileage' => $data['current_mileage'] ?? 0,
                'purchase_date' => $data['purchase_date'] ?? null,
                'color' => sanitize($data['color'] ?? ''),
                'notes' => sanitize($data['notes'] ?? '')
            ]);
            
            jsonResponse(['success' => true, 'id' => $id, 'message' => 'Vehicle added successfully']);
            break;
            
        case 'update':
            if ($method !== 'POST') jsonResponse(['success' => false, 'message' => 'Invalid method'], 405);
            
            $data = json_decode(file_get_contents('php://input'), true);
            $id = (int)$data['id'];
            
            $db->update('vehicles', [
                'make' => sanitize($data['make']),
                'model' => sanitize($data['model']),
                'year' => $data['year'] ?? null,
                'vin' => sanitize($data['vin'] ?? ''),
                'license_plate' => sanitize($data['license_plate'] ?? ''),
                'current_mileage' => $data['current_mileage'] ?? 0,
                'color' => sanitize($data['color'] ?? ''),
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = ? AND user_id = ?', [$id, $userId]);
            
            jsonResponse(['success' => true, 'message' => 'Vehicle updated successfully']);
            break;
            
        case 'delete':
            if ($method !== 'POST') jsonResponse(['success' => false, 'message' => 'Invalid method'], 405);
            
            $data = json_decode(file_get_contents('php://input'), true);
            $id = (int)$data['id'];
            
            $db->query("DELETE FROM vehicles WHERE id = ? AND user_id = ?", [$id, $userId]);
            jsonResponse(['success' => true, 'message' => 'Vehicle deleted successfully']);
            break;
            
        case 'add_maintenance':
            if ($method !== 'POST') jsonResponse(['success' => false, 'message' => 'Invalid method'], 405);
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            $id = $db->insert('vehicle_maintenance', [
                'vehicle_id' => $data['vehicle_id'],
                'user_id' => $userId,
                'service_type' => sanitize($data['service_type']),
                'service_date' => $data['service_date'],
                'mileage' => $data['mileage'] ?? 0,
                'cost' => $data['cost'] ?? 0,
                'service_provider' => sanitize($data['service_provider'] ?? ''),
                'next_service_date' => $data['next_service_date'] ?? null,
                'next_service_mileage' => $data['next_service_mileage'] ?? null,
                'notes' => sanitize($data['notes'] ?? '')
            ]);
            
            jsonResponse(['success' => true, 'id' => $id, 'message' => 'Maintenance record added']);
            break;
            
        case 'get_maintenance':
            $vehicleId = (int)($_GET['vehicle_id'] ?? 0);
            $maintenance = $db->fetchAll(
                "SELECT * FROM vehicle_maintenance WHERE vehicle_id = ? AND user_id = ? ORDER BY service_date DESC", 
                [$vehicleId, $userId]
            );
            jsonResponse(['success' => true, 'maintenance' => $maintenance]);
            break;
            
        default:
            jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
    }
} catch (Exception $e) {
    error_log("Vehicles API Error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'An error occurred'], 500);
}
