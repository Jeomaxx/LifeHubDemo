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
            $medications = $db->fetchAll("SELECT * FROM medications WHERE user_id = ? AND is_active = TRUE ORDER BY name", [$userId]);
            jsonResponse(['success' => true, 'medications' => $medications]);
            break;
            
        case 'add':
            if ($method !== 'POST') jsonResponse(['success' => false, 'message' => 'Invalid method'], 405);
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            $id = $db->insert('medications', [
                'user_id' => $userId,
                'name' => sanitize($data['name']),
                'medication_type' => $data['medication_type'],
                'dosage' => sanitize($data['dosage']),
                'frequency' => $data['frequency'],
                'time_of_day' => sanitize($data['time_of_day'] ?? ''),
                'start_date' => $data['start_date'] ?? null,
                'end_date' => $data['end_date'] ?? null,
                'prescribing_doctor' => sanitize($data['prescribing_doctor'] ?? ''),
                'purpose' => sanitize($data['purpose'] ?? ''),
                'current_quantity' => $data['current_quantity'] ?? null,
                'refill_reminder_quantity' => $data['refill_reminder_quantity'] ?? null
            ]);
            
            jsonResponse(['success' => true, 'id' => $id, 'message' => 'Medication added successfully']);
            break;
            
        case 'update':
            if ($method !== 'POST') jsonResponse(['success' => false, 'message' => 'Invalid method'], 405);
            
            $data = json_decode(file_get_contents('php://input'), true);
            $id = (int)$data['id'];
            
            $db->update('medications', [
                'name' => sanitize($data['name']),
                'medication_type' => $data['medication_type'],
                'dosage' => sanitize($data['dosage']),
                'frequency' => $data['frequency'],
                'time_of_day' => sanitize($data['time_of_day'] ?? ''),
                'purpose' => sanitize($data['purpose'] ?? ''),
                'current_quantity' => $data['current_quantity'] ?? null,
                'refill_reminder_quantity' => $data['refill_reminder_quantity'] ?? null,
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = ? AND user_id = ?', [$id, $userId]);
            
            jsonResponse(['success' => true, 'message' => 'Medication updated successfully']);
            break;
            
        case 'delete':
            if ($method !== 'POST') jsonResponse(['success' => false, 'message' => 'Invalid method'], 405);
            
            $data = json_decode(file_get_contents('php://input'), true);
            $id = (int)$data['id'];
            
            $db->update('medications', ['is_active' => false], 'id = ? AND user_id = ?', [$id, $userId]);
            jsonResponse(['success' => true, 'message' => 'Medication deleted successfully']);
            break;
            
        case 'log_intake':
            if ($method !== 'POST') jsonResponse(['success' => false, 'message' => 'Invalid method'], 405);
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            $id = $db->insert('medication_logs', [
                'medication_id' => $data['medication_id'],
                'user_id' => $userId,
                'log_date' => $data['log_date'] ?? date('Y-m-d'),
                'log_time' => $data['log_time'] ?? date('H:i:s'),
                'taken' => isset($data['taken']) ? (bool)$data['taken'] : true,
                'notes' => sanitize($data['notes'] ?? '')
            ]);
            
            jsonResponse(['success' => true, 'id' => $id, 'message' => 'Intake logged successfully']);
            break;
            
        default:
            jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
    }
} catch (Exception $e) {
    error_log("Medications API Error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'An error occurred'], 500);
}
