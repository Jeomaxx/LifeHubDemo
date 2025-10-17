<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/ai_config.php';

header('Content-Type: application/json');

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = $auth->getUserId();
$db = Database::getInstance();
$ai = AIConfig::getInstance();
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'add':
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            $db->execute(
                "INSERT INTO relationships (user_id, contact_name, relationship_type, contact_info, health_score, last_interaction_date, interaction_frequency, notes) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $userId,
                    $data['contact_name'],
                    $data['relationship_type'],
                    json_encode($data['contact_info'] ?? []),
                    80,
                    date('Y-m-d'),
                    'weekly',
                    $data['notes'] ?? ''
                ]
            );
            
            echo json_encode(['success' => true, 'message' => 'Relationship added']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to add relationship: ' . $e->getMessage()]);
        }
        break;
    
    case 'add_interaction':
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            $db->execute(
                "INSERT INTO relationship_interactions (relationship_id, user_id, interaction_type, interaction_date, notes, mood_impact) 
                VALUES (?, ?, ?, ?, ?, ?)",
                [
                    $data['relationship_id'],
                    $userId,
                    $data['interaction_type'],
                    $data['interaction_date'],
                    $data['notes'] ?? '',
                    $data['mood_impact'] ?? 0
                ]
            );
            
            $db->execute(
                "UPDATE relationships SET last_interaction_date = ? WHERE id = ? AND user_id = ?",
                [$data['interaction_date'], $data['relationship_id'], $userId]
            );
            
            echo json_encode(['success' => true, 'message' => 'Interaction logged']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to log interaction']);
        }
        break;
    
    case 'get_relationships':
        try {
            $relationships = $db->fetchAll(
                "SELECT * FROM relationships WHERE user_id = ? ORDER BY last_interaction_date DESC",
                [$userId]
            );
            
            echo json_encode(['success' => true, 'relationships' => $relationships]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to get relationships']);
        }
        break;
    
    case 'analyze':
        try {
            $relationships = $db->fetchAll("SELECT * FROM relationships WHERE user_id = ?", [$userId]);
            $interactions = $db->fetchAll(
                "SELECT * FROM relationship_interactions WHERE user_id = ? ORDER BY interaction_date DESC LIMIT 50",
                [$userId]
            );
            
            $aiResponse = $ai->analyzeRelationships($interactions, $relationships);
            $analysis = json_decode($aiResponse, true);
            
            echo json_encode(['success' => true, 'analysis' => $analysis]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to analyze relationships']);
        }
        break;
    
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
