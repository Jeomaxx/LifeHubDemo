<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';

header('Content-Type: application/json');

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = $auth->getUserId();
$db = Database::getInstance();
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'log_sleep':
            $sleepDate = $_POST['sleep_date'] ?? date('Y-m-d');
            $sleepStartTime = $_POST['sleep_start_time'] ?? null;
            $sleepEndTime = $_POST['sleep_end_time'] ?? null;
            $sleepQualityRating = $_POST['sleep_quality_rating'] ?? 7;
            $notes = $_POST['notes'] ?? '';
            
            $sleepDuration = 0;
            if ($sleepStartTime && $sleepEndTime) {
                $start = new DateTime($sleepDate . ' ' . $sleepStartTime);
                $end = new DateTime($sleepDate . ' ' . $sleepEndTime);
                
                if ($end < $start) {
                    $end->modify('+1 day');
                }
                
                $interval = $start->diff($end);
                $sleepDuration = $interval->h + ($interval->i / 60);
            }
            
            $logId = $db->insert("INSERT INTO sleep_logs (user_id, sleep_date, sleep_start_time, sleep_end_time, sleep_duration_hours, sleep_quality_rating, notes) VALUES (?, ?, ?, ?, ?, ?, ?)",
                [$userId, $sleepDate, $sleepStartTime, $sleepEndTime, $sleepDuration, $sleepQualityRating, $notes]);
            
            echo json_encode(['success' => true, 'log_id' => $logId]);
            break;
            
        case 'log_meditation':
            $sessionDate = $_POST['session_date'] ?? date('Y-m-d');
            $durationMinutes = $_POST['duration_minutes'] ?? 10;
            $meditationType = $_POST['meditation_type'] ?? 'Mindfulness';
            $moodBefore = $_POST['mood_before'] ?? null;
            $moodAfter = $_POST['mood_after'] ?? null;
            $notes = $_POST['notes'] ?? '';
            
            $sessionId = $db->insert("INSERT INTO meditation_sessions (user_id, session_date, duration_minutes, meditation_type, mood_before, mood_after, notes) VALUES (?, ?, ?, ?, ?, ?, ?)",
                [$userId, $sessionDate, $durationMinutes, $meditationType, $moodBefore, $moodAfter, $notes]);
            
            echo json_encode(['success' => true, 'session_id' => $sessionId]);
            break;
            
        case 'get_sleep_data':
            $days = $_GET['days'] ?? 7;
            $sleepData = $db->fetchAll("SELECT * FROM sleep_logs WHERE user_id = ? AND sleep_date >= CURRENT_DATE - INTERVAL '$days days' ORDER BY sleep_date ASC", [$userId]) ?: [];
            
            echo json_encode(['success' => true, 'sleep_data' => $sleepData]);
            break;
            
        case 'insights':
            require_once '../includes/ai_config.php';
            $aiConfig = AIConfig::getInstance();
            
            $sleepData = $db->fetchAll("SELECT * FROM sleep_logs WHERE user_id = ? ORDER BY sleep_date DESC LIMIT 14", [$userId]) ?: [];
            $meditationData = $db->fetchAll("SELECT * FROM meditation_sessions WHERE user_id = ? ORDER BY session_date DESC LIMIT 14", [$userId]) ?: [];
            $moodData = $db->fetchAll("SELECT * FROM mood_entries WHERE user_id = ? ORDER BY mood_date DESC LIMIT 14", [$userId]) ?: [];
            
            $prompt = "Analyze this wellness data and provide insights. Return JSON with: summary (string), sleep_insights (string), meditation_insights (string), recommendations (array of strings), correlation_findings (string).\n\n";
            $prompt .= "Sleep Data (last 14 days): Average quality: " . (empty($sleepData) ? 0 : array_sum(array_column($sleepData, 'sleep_quality_rating')) / count($sleepData)) . "/10, Average duration: " . (empty($sleepData) ? 0 : array_sum(array_column($sleepData, 'sleep_duration_hours')) / count($sleepData)) . " hours\n";
            $prompt .= "Meditation: Total sessions: " . count($meditationData) . ", Total minutes: " . array_sum(array_column($meditationData, 'duration_minutes')) . "\n";
            
            if (!empty($moodData)) {
                $prompt .= "Mood: Average: " . (array_sum(array_column($moodData, 'mood_rating')) / count($moodData)) . "/10\n";
            }
            
            $prompt .= "\nProvide actionable insights about sleep quality, meditation practice effectiveness, and correlations with mood.";
            
            $aiResponse = $aiConfig->generateContent($prompt);
            $insights = json_decode($aiResponse, true);
            
            if (!$insights) {
                $insights = [
                    'summary' => 'Your wellness data looks good! Keep up the practice.',
                    'sleep_insights' => 'Your sleep quality is consistent.',
                    'meditation_insights' => 'Regular meditation practice is helping your well-being.',
                    'recommendations' => ['Maintain your current sleep schedule', 'Try different meditation techniques'],
                    'correlation_findings' => 'Sleep and meditation appear to positively impact your mood.'
                ];
            }
            
            echo json_encode(['success' => true, 'insights' => $insights]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    error_log("Mindfulness Sleep API Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}
