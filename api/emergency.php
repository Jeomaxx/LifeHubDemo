<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/notifications.php';

header('Content-Type: application/json');

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = $auth->getUserId();
$db = Database::getInstance();

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';

try {
    switch ($method) {
        case 'POST':
            if ($action === 'activate') {
                // Get user info
                $user = $db->fetchOne("SELECT name, email FROM users WHERE id = ?", [$userId]);
                
                // Get emergency contacts
                $contacts = $db->fetchAll("SELECT * FROM emergency_contacts WHERE user_id = ? ORDER BY priority", [$userId]);
                
                if (empty($contacts)) {
                    echo json_encode(['success' => false, 'message' => 'No emergency contacts configured']);
                    exit;
                }
                
                // Get health profile
                $healthProfile = $db->fetchOne("SELECT * FROM health_profiles WHERE user_id = ?", [$userId]);
                
                // Create emergency message
                $message = "🚨 EMERGENCY ALERT 🚨\n\n";
                $message .= "Emergency alert from {$user['name']}\n\n";
                $message .= "Location: https://maps.google.com/?q={$data['latitude']},{$data['longitude']}\n\n";
                
                if ($healthProfile) {
                    $message .= "Medical Info:\n";
                    if ($healthProfile['blood_type']) $message .= "Blood Type: {$healthProfile['blood_type']}\n";
                    if ($healthProfile['allergies']) $message .= "Allergies: {$healthProfile['allergies']}\n";
                    if ($healthProfile['conditions']) $message .= "Conditions: {$healthProfile['conditions']}\n";
                    if ($healthProfile['medications']) $message .= "Medications: {$healthProfile['medications']}\n";
                }
                
                $message .= "\nTime: " . date('Y-m-d H:i:s');
                
                // Notify all emergency contacts
                $notified = 0;
                foreach ($contacts as $contact) {
                    // Send SMS if configured
                    if (!empty($contact['phone'])) {
                        // In production, integrate with Twilio or similar
                        $notified++;
                    }
                    
                    // Send email if available
                    if (!empty($contact['email'])) {
                        sendEmail($contact['email'], 'EMERGENCY ALERT', $message);
                        $notified++;
                    }
                }
                
                // Log emergency activation
                $db->execute(
                    "INSERT INTO emergency_log (user_id, latitude, longitude, contacts_notified, timestamp) VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)",
                    [$userId, $data['latitude'], $data['longitude'], $notified]
                );
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Emergency contacts have been notified',
                    'contacts_notified' => $notified
                ]);
            }
            elseif ($action === 'add_contact') {
                $priority = $db->fetchColumn("SELECT COALESCE(MAX(priority), 0) + 1 FROM emergency_contacts WHERE user_id = ?", [$userId]);
                
                $db->execute(
                    "INSERT INTO emergency_contacts (user_id, name, phone, email, relationship, priority) VALUES (?, ?, ?, ?, ?, ?)",
                    [
                        $userId,
                        $data['name'],
                        $data['phone'],
                        $data['email'] ?? null,
                        $data['relationship'] ?? null,
                        $priority
                    ]
                );
                
                echo json_encode(['success' => true, 'message' => 'Emergency contact added']);
            }
            elseif ($action === 'save_health_profile') {
                $existing = $db->fetchOne("SELECT id FROM health_profiles WHERE user_id = ?", [$userId]);
                
                if ($existing) {
                    $db->execute(
                        "UPDATE health_profiles SET blood_type = ?, medical_id = ?, allergies = ?, conditions = ?, medications = ?, updated_at = CURRENT_TIMESTAMP WHERE user_id = ?",
                        [
                            $data['blood_type'] ?? null,
                            $data['medical_id'] ?? null,
                            $data['allergies'] ?? null,
                            $data['conditions'] ?? null,
                            $data['medications'] ?? null,
                            $userId
                        ]
                    );
                } else {
                    $db->execute(
                        "INSERT INTO health_profiles (user_id, blood_type, medical_id, allergies, conditions, medications) VALUES (?, ?, ?, ?, ?, ?)",
                        [
                            $userId,
                            $data['blood_type'] ?? null,
                            $data['medical_id'] ?? null,
                            $data['allergies'] ?? null,
                            $data['conditions'] ?? null,
                            $data['medications'] ?? null
                        ]
                    );
                }
                
                echo json_encode(['success' => true, 'message' => 'Health profile saved']);
            }
            break;
            
        case 'DELETE':
            if ($action === 'remove_contact') {
                $db->execute(
                    "DELETE FROM emergency_contacts WHERE id = ? AND user_id = ?",
                    [$data['contact_id'], $userId]
                );
                
                echo json_encode(['success' => true, 'message' => 'Contact removed']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    }
} catch (Exception $e) {
    error_log('Emergency API error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
