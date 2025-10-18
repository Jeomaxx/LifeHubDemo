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
            $symptoms = $db->fetchAll("SELECT * FROM symptoms WHERE user_id = ? ORDER BY name", [$userId]);
            jsonResponse(['success' => true, 'symptoms' => $symptoms]);
            break;
            
        case 'add':
            if ($method !== 'POST') jsonResponse(['success' => false, 'message' => 'Invalid method'], 405);
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            $id = $db->insert('symptoms', [
                'user_id' => $userId,
                'name' => sanitize($data['name']),
                'category' => $data['category']
            ]);
            
            jsonResponse(['success' => true, 'id' => $id, 'message' => 'Symptom added successfully']);
            break;
            
        case 'delete':
            if ($method !== 'POST') jsonResponse(['success' => false, 'message' => 'Invalid method'], 405);
            
            $data = json_decode(file_get_contents('php://input'), true);
            $id = (int)$data['id'];
            
            $db->query("DELETE FROM symptoms WHERE id = ? AND user_id = ?", [$id, $userId]);
            jsonResponse(['success' => true, 'message' => 'Symptom deleted successfully']);
            break;
            
        case 'log':
            if ($method !== 'POST') jsonResponse(['success' => false, 'message' => 'Invalid method'], 405);
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            $id = $db->insert('symptom_logs', [
                'symptom_id' => $data['symptom_id'],
                'user_id' => $userId,
                'log_date' => $data['log_date'] ?? date('Y-m-d'),
                'log_time' => $data['log_time'] ?? date('H:i:s'),
                'severity' => $data['severity'],
                'duration_minutes' => $data['duration_minutes'] ?? null,
                'triggers' => sanitize($data['triggers'] ?? ''),
                'notes' => sanitize($data['notes'] ?? '')
            ]);
            
            jsonResponse(['success' => true, 'id' => $id, 'message' => 'Symptom logged successfully']);
            break;
            
        case 'get_logs':
            $symptomId = (int)($_GET['symptom_id'] ?? 0);
            $logs = $db->fetchAll(
                "SELECT * FROM symptom_logs WHERE symptom_id = ? AND user_id = ? ORDER BY log_date DESC, log_time DESC LIMIT 50", 
                [$symptomId, $userId]
            );
            jsonResponse(['success' => true, 'logs' => $logs]);
            break;
            
        default:
            jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
    }
} catch (Exception $e) {
    error_log("Symptoms API Error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'An error occurred'], 500);
}
