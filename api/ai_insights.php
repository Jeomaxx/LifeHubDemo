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
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get_all':
        try {
            $insights = [];
            
            $latestForecast = $db->fetchOne(
                "SELECT * FROM financial_forecasts WHERE user_id = ? ORDER BY created_at DESC LIMIT 1",
                [$userId]
            );
            if ($latestForecast) {
                $insights[] = [
                    'type' => 'financial',
                    'title' => 'Financial Forecast',
                    'message' => "Predicted balance: $" . number_format($latestForecast['predicted_balance'], 2),
                    'icon' => 'chart-line',
                    'color' => 'blue',
                    'link' => '/financial_forecast.php'
                ];
            }
            
            $avgMood = $db->fetchColumn(
                "SELECT COALESCE(AVG(mood_rating), 0) FROM mood_entries WHERE user_id = ? AND mood_date >= CURRENT_DATE - INTERVAL '7 days'",
                [$userId]
            );
            if ($avgMood > 0) {
                $moodStatus = $avgMood >= 7 ? 'great' : ($avgMood >= 5 ? 'good' : 'needs attention');
                $insights[] = [
                    'type' => 'mood',
                    'title' => 'Mood Insights',
                    'message' => "Your average mood is " . round($avgMood, 1) . "/10 - $moodStatus",
                    'icon' => 'smile',
                    'color' => $avgMood >= 7 ? 'green' : ($avgMood >= 5 ? 'yellow' : 'red'),
                    'link' => '/mood_tracker.php'
                ];
            }
            
            $activeGoals = $db->fetchAll(
                "SELECT * FROM smart_goals WHERE user_id = ? AND status = 'active' ORDER BY current_progress DESC LIMIT 3",
                [$userId]
            );
            if (!empty($activeGoals)) {
                $avgProgress = array_sum(array_column($activeGoals, 'current_progress')) / count($activeGoals);
                $insights[] = [
                    'type' => 'goals',
                    'title' => 'Goal Progress',
                    'message' => "You're " . round($avgProgress) . "% towards your goals on average",
                    'icon' => 'bullseye',
                    'color' => 'purple',
                    'link' => '/smart_goals.php'
                ];
            }
            
            $upcomingEvents = $db->fetchAll(
                "SELECT * FROM life_event_predictions WHERE user_id = ? AND is_confirmed = FALSE ORDER BY predicted_date ASC LIMIT 1",
                [$userId]
            );
            if (!empty($upcomingEvents)) {
                $event = $upcomingEvents[0];
                $insights[] = [
                    'type' => 'life_event',
                    'title' => 'Predicted Life Event',
                    'message' => $event['event_title'] . " (Confidence: " . $event['confidence_score'] . "%)",
                    'icon' => 'crystal-ball',
                    'color' => 'orange',
                    'link' => '/life_events.php'
                ];
            }
            
            $relationshipCount = $db->fetchColumn(
                "SELECT COUNT(*) FROM relationships WHERE user_id = ?",
                [$userId]
            );
            if ($relationshipCount > 0) {
                $avgHealth = $db->fetchColumn(
                    "SELECT COALESCE(AVG(health_score), 0) FROM relationships WHERE user_id = ?",
                    [$userId]
                );
                $insights[] = [
                    'type' => 'relationships',
                    'title' => 'Relationship Health',
                    'message' => "$relationshipCount relationships tracked with " . round($avgHealth) . "% avg health",
                    'icon' => 'users',
                    'color' => 'pink',
                    'link' => '/relationships.php'
                ];
            }
            
            echo json_encode(['success' => true, 'insights' => $insights]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to get insights: ' . $e->getMessage()]);
        }
        break;
    
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
