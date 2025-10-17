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
            
            $context = [
                'date' => $data['mood_date'],
                'rating' => $data['mood_rating']
            ];
            
            $aiAnalysis = null;
            if (!empty($data['mood_notes'])) {
                try {
                    $aiAnalysis = $ai->analyzeMood($data['mood_notes'], $context);
                    $analysisData = json_decode($aiAnalysis, true);
                } catch (Exception $e) {
                    $analysisData = null;
                }
            }
            
            $db->execute(
                "INSERT INTO mood_entries (user_id, mood_date, mood_rating, mood_emoji, mood_notes, ai_sentiment, ai_emotions, ai_insights, linked_activities) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $userId,
                    $data['mood_date'],
                    $data['mood_rating'],
                    $data['mood_emoji'] ?? '😐',
                    $data['mood_notes'] ?? '',
                    $analysisData['sentiment'] ?? null,
                    $analysisData['emotions'] ?? null,
                    $analysisData['insight'] ?? null,
                    json_encode($data['linked_activities'] ?? [])
                ]
            );
            
            echo json_encode(['success' => true, 'message' => 'Mood entry added', 'ai_analysis' => $analysisData]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to add mood entry: ' . $e->getMessage()]);
        }
        break;
    
    case 'get_entries':
        try {
            $limit = (int)($_GET['limit'] ?? 30);
            $entries = $db->fetchAll(
                "SELECT * FROM mood_entries WHERE user_id = ? ORDER BY mood_date DESC LIMIT ?",
                [$userId, $limit]
            );
            
            echo json_encode(['success' => true, 'entries' => $entries]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to get mood entries']);
        }
        break;
    
    case 'get_trends':
        try {
            $days = (int)($_GET['days'] ?? 30);
            
            $trends = $db->fetchAll(
                "SELECT mood_date, AVG(mood_rating) as avg_rating, COUNT(*) as count 
                FROM mood_entries 
                WHERE user_id = ? AND mood_date >= CURRENT_DATE - INTERVAL '{$days} days'
                GROUP BY mood_date 
                ORDER BY mood_date ASC",
                [$userId]
            );
            
            $sentiments = $db->fetchAll(
                "SELECT ai_sentiment, COUNT(*) as count 
                FROM mood_entries 
                WHERE user_id = ? AND mood_date >= CURRENT_DATE - INTERVAL '{$days} days' AND ai_sentiment IS NOT NULL
                GROUP BY ai_sentiment",
                [$userId]
            );
            
            echo json_encode(['success' => true, 'trends' => $trends, 'sentiments' => $sentiments]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to get trends']);
        }
        break;
    
    case 'delete':
        try {
            $id = $_GET['id'] ?? 0;
            $db->execute("DELETE FROM mood_entries WHERE id = ? AND user_id = ?", [$id, $userId]);
            echo json_encode(['success' => true, 'message' => 'Mood entry deleted']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to delete mood entry']);
        }
        break;
    
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
