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
    case 'predict':
        try {
            $userData = [
                'finances' => $db->fetchAll("SELECT * FROM transactions WHERE user_id = ? ORDER BY date DESC LIMIT 30", [$userId]),
                'bills' => $db->fetchAll("SELECT * FROM bills WHERE user_id = ? AND due_date >= CURRENT_DATE LIMIT 20", [$userId]),
                'mood' => $db->fetchAll("SELECT * FROM mood_entries WHERE user_id = ? ORDER BY mood_date DESC LIMIT 14", [$userId]),
                'tasks' => $db->fetchAll("SELECT * FROM tasks WHERE user_id = ? AND status != 'completed' LIMIT 20", [$userId]),
                'habits' => $db->fetchAll("SELECT * FROM habits WHERE user_id = ? LIMIT 10", [$userId])
            ];
            
            $aiResponse = $ai->predictLifeEvents($userData);
            $predictions = json_decode($aiResponse, true);
            
            if ($predictions && isset($predictions['events'])) {
                foreach ($predictions['events'] as $event) {
                    $db->execute(
                        "INSERT INTO life_event_predictions (user_id, event_type, event_title, predicted_date, confidence_score, impact_level, preventive_actions, data_sources) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                        [
                            $userId,
                            $event['type'] ?? 'general',
                            $event['title'],
                            $event['predicted_date'] ?? date('Y-m-d', strtotime('+1 week')),
                            $event['confidence'] ?? 50,
                            $event['impact'] ?? 'medium',
                            $event['actions'] ?? '',
                            json_encode($event['sources'] ?? [])
                        ]
                    );
                }
            }
            
            echo json_encode(['success' => true, 'predictions' => $predictions]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to generate predictions: ' . $e->getMessage()]);
        }
        break;
    
    case 'get_predictions':
        try {
            $predictions = $db->fetchAll(
                "SELECT * FROM life_event_predictions WHERE user_id = ? AND is_confirmed = FALSE ORDER BY predicted_date ASC LIMIT 10",
                [$userId]
            );
            
            echo json_encode(['success' => true, 'predictions' => $predictions]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to get predictions']);
        }
        break;
    
    case 'confirm':
        try {
            $id = $_GET['id'] ?? 0;
            $db->execute("UPDATE life_event_predictions SET is_confirmed = TRUE WHERE id = ? AND user_id = ?", [$id, $userId]);
            echo json_encode(['success' => true, 'message' => 'Event confirmed']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to confirm event']);
        }
        break;
    
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
