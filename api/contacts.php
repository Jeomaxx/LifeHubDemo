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
            $contacts = $db->fetchAll("SELECT * FROM contacts WHERE user_id = ? ORDER BY name", [$userId]);
            jsonResponse(['success' => true, 'contacts' => $contacts]);
            break;
            
        case 'add':
            if ($method !== 'POST') jsonResponse(['success' => false, 'message' => 'Invalid method'], 405);
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            $id = $db->insert('contacts', [
                'user_id' => $userId,
                'name' => sanitize($data['name']),
                'relationship' => $data['relationship'],
                'email' => sanitize($data['email'] ?? ''),
                'phone' => sanitize($data['phone'] ?? ''),
                'birthday' => $data['birthday'] ?? null,
                'address' => sanitize($data['address'] ?? ''),
                'company' => sanitize($data['company'] ?? ''),
                'job_title' => sanitize($data['job_title'] ?? ''),
                'notes' => sanitize($data['notes'] ?? ''),
                'importance' => $data['importance'] ?? 'medium',
                'tags' => $data['tags'] ?? '',
                'is_favorite' => isset($data['is_favorite']) ? (bool)$data['is_favorite'] : false
            ]);
            
            jsonResponse(['success' => true, 'id' => $id, 'message' => 'Contact added successfully']);
            break;
            
        case 'update':
            if ($method !== 'POST') jsonResponse(['success' => false, 'message' => 'Invalid method'], 405);
            
            $data = json_decode(file_get_contents('php://input'), true);
            $id = (int)$data['id'];
            
            $db->update('contacts', [
                'name' => sanitize($data['name']),
                'relationship' => $data['relationship'],
                'email' => sanitize($data['email'] ?? ''),
                'phone' => sanitize($data['phone'] ?? ''),
                'birthday' => $data['birthday'] ?? null,
                'address' => sanitize($data['address'] ?? ''),
                'company' => sanitize($data['company'] ?? ''),
                'job_title' => sanitize($data['job_title'] ?? ''),
                'notes' => sanitize($data['notes'] ?? ''),
                'importance' => $data['importance'] ?? 'medium',
                'is_favorite' => isset($data['is_favorite']) ? (bool)$data['is_favorite'] : false,
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = ? AND user_id = ?', [$id, $userId]);
            
            jsonResponse(['success' => true, 'message' => 'Contact updated successfully']);
            break;
            
        case 'delete':
            if ($method !== 'POST') jsonResponse(['success' => false, 'message' => 'Invalid method'], 405);
            
            $data = json_decode(file_get_contents('php://input'), true);
            $id = (int)$data['id'];
            
            $db->query("DELETE FROM contacts WHERE id = ? AND user_id = ?", [$id, $userId]);
            jsonResponse(['success' => true, 'message' => 'Contact deleted successfully']);
            break;
            
        case 'log_interaction':
            if ($method !== 'POST') jsonResponse(['success' => false, 'message' => 'Invalid method'], 405);
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            $id = $db->insert('contact_interactions', [
                'contact_id' => $data['contact_id'],
                'user_id' => $userId,
                'interaction_date' => $data['interaction_date'] ?? date('Y-m-d'),
                'interaction_type' => $data['interaction_type'],
                'notes' => sanitize($data['notes'] ?? '')
            ]);
            
            jsonResponse(['success' => true, 'id' => $id, 'message' => 'Interaction logged successfully']);
            break;
            
        default:
            jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
    }
} catch (Exception $e) {
    error_log("Contacts API Error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'An error occurred'], 500);
}
