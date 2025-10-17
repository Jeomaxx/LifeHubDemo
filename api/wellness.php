<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = $auth->getUserId();
$db = Database::getInstance();
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            $type = $_GET['type'] ?? 'meditation';
            
            if ($type === 'meditation') {
                $sessions = $db->fetchAll("SELECT * FROM meditation_sessions WHERE user_id = ? ORDER BY session_date DESC LIMIT 50", [$userId]);
                echo json_encode(['success' => true, 'data' => $sessions]);
            } elseif ($type === 'breathing') {
                $exercises = $db->fetchAll("SELECT * FROM breathing_exercises WHERE user_id = ? ORDER BY exercise_date DESC LIMIT 50", [$userId]);
                echo json_encode(['success' => true, 'data' => $exercises]);
            } elseif ($type === 'sleep') {
                $sleep = $db->fetchAll("SELECT * FROM sleep_tracking WHERE user_id = ? ORDER BY sleep_date DESC LIMIT 30", [$userId]);
                echo json_encode(['success' => true, 'data' => $sleep]);
            } elseif ($type === 'stats') {
                $stats = [
                    'total_meditation_mins' => $db->fetchOne("SELECT COALESCE(SUM(duration_minutes), 0) as total FROM meditation_sessions WHERE user_id = ?", [$userId])['total'] ?? 0,
                    'avg_sleep_hours' => $db->fetchOne("SELECT COALESCE(AVG(duration_hours), 0) as avg FROM sleep_tracking WHERE user_id = ? AND sleep_date >= CURRENT_DATE - INTERVAL '30 days'", [$userId])['avg'] ?? 0,
                    'meditation_sessions' => $db->fetchOne("SELECT COUNT(*) as count FROM meditation_sessions WHERE user_id = ?", [$userId])['count'] ?? 0
                ];
                echo json_encode(['success' => true, 'data' => $stats]);
            }
            break;
            
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $type = $data['type'] ?? 'meditation';
            
            if ($type === 'meditation') {
                $id = $db->insert('meditation_sessions', [
                    'user_id' => $userId,
                    'session_date' => $data['session_date'],
                    'duration_minutes' => $data['duration_minutes'],
                    'meditation_type' => $data['meditation_type'] ?? '',
                    'technique' => $data['technique'] ?? '',
                    'mood_before' => $data['mood_before'] ?? '',
                    'mood_after' => $data['mood_after'] ?? '',
                    'notes' => $data['notes'] ?? ''
                ]);
                echo json_encode(['success' => true, 'id' => $id]);
            } elseif ($type === 'breathing') {
                $id = $db->insert('breathing_exercises', [
                    'user_id' => $userId,
                    'exercise_date' => $data['exercise_date'],
                    'exercise_type' => $data['exercise_type'] ?? '',
                    'duration_minutes' => $data['duration_minutes'],
                    'rounds_completed' => $data['rounds_completed'] ?? 0,
                    'stress_level_before' => $data['stress_level_before'] ?? 0,
                    'stress_level_after' => $data['stress_level_after'] ?? 0
                ]);
                echo json_encode(['success' => true, 'id' => $id]);
            } elseif ($type === 'sleep') {
                $id = $db->insert('sleep_tracking', [
                    'user_id' => $userId,
                    'sleep_date' => $data['sleep_date'],
                    'bedtime' => $data['bedtime'] ?? null,
                    'wake_time' => $data['wake_time'] ?? null,
                    'duration_hours' => $data['duration_hours'] ?? 0,
                    'quality_rating' => $data['quality_rating'] ?? 0,
                    'sleep_notes' => $data['sleep_notes'] ?? ''
                ]);
                echo json_encode(['success' => true, 'id' => $id]);
            }
            break;
            
        case 'DELETE':
            $id = (int)($_GET['id'] ?? 0);
            $type = $_GET['type'] ?? 'meditation';
            
            if ($type === 'meditation') $db->delete('meditation_sessions', $id);
            elseif ($type === 'breathing') $db->delete('breathing_exercises', $id);
            elseif ($type === 'sleep') $db->delete('sleep_tracking', $id);
            
            echo json_encode(['success' => true]);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
