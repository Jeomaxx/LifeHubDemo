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

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$type = $_GET['type'] ?? 'routines'; // routines, exercises, sessions

try {
    switch ($method) {
        case 'GET':
            if ($type === 'routines') {
                $routines = $db->fetchAll(
                    "SELECT * FROM gym_routines WHERE user_id = ? ORDER BY created_at DESC",
                    [$userId]
                );
                echo json_encode(['success' => true, 'routines' => $routines]);
            } elseif ($type === 'exercises' && isset($_GET['routine_id'])) {
                $exercises = $db->fetchAll(
                    "SELECT * FROM gym_exercises WHERE routine_id = ? AND user_id = ? ORDER BY created_at",
                    [$_GET['routine_id'], $userId]
                );
                echo json_encode(['success' => true, 'exercises' => $exercises]);
            } elseif ($type === 'sessions') {
                $limit = $_GET['limit'] ?? 30;
                $sessions = $db->fetchAll(
                    "SELECT s.*, r.routine_name FROM gym_sessions s 
                    LEFT JOIN gym_routines r ON s.routine_id = r.id 
                    WHERE s.user_id = ? ORDER BY s.session_date DESC LIMIT ?",
                    [$userId, $limit]
                );
                echo json_encode(['success' => true, 'sessions' => $sessions]);
            } elseif ($action === 'stats') {
                $stats = [
                    'total_sessions' => $db->fetchColumn("SELECT COUNT(*) FROM gym_sessions WHERE user_id = ?", [$userId]),
                    'total_routines' => $db->fetchColumn("SELECT COUNT(*) FROM gym_routines WHERE user_id = ?", [$userId]),
                    'this_month_sessions' => $db->fetchColumn(
                        "SELECT COUNT(*) FROM gym_sessions WHERE user_id = ? AND DATE_TRUNC('month', session_date) = DATE_TRUNC('month', CURRENT_DATE)",
                        [$userId]
                    ),
                    'total_calories_burned' => $db->fetchColumn(
                        "SELECT COALESCE(SUM(calories_burned), 0) FROM gym_sessions WHERE user_id = ?",
                        [$userId]
                    )
                ];
                echo json_encode(['success' => true, 'stats' => $stats]);
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            if ($type === 'routines' && $action === 'create') {
                $id = $db->insert('gym_routines', [
                    'user_id' => $userId,
                    'routine_name' => $data['routine_name'],
                    'description' => $data['description'] ?? null
                ]);
                echo json_encode(['success' => true, 'id' => $id, 'message' => 'Routine created successfully']);
            } elseif ($type === 'exercises' && $action === 'create') {
                $id = $db->insert('gym_exercises', [
                    'routine_id' => $data['routine_id'],
                    'user_id' => $userId,
                    'exercise_name' => $data['exercise_name'],
                    'sets' => $data['sets'] ?? null,
                    'reps' => $data['reps'] ?? null,
                    'weight' => $data['weight'] ?? null,
                    'duration' => $data['duration'] ?? null,
                    'notes' => $data['notes'] ?? null
                ]);
                echo json_encode(['success' => true, 'id' => $id, 'message' => 'Exercise added successfully']);
            } elseif ($type === 'sessions' && $action === 'create') {
                $id = $db->insert('gym_sessions', [
                    'user_id' => $userId,
                    'routine_id' => $data['routine_id'] ?? null,
                    'session_date' => $data['session_date'],
                    'duration' => $data['duration'] ?? null,
                    'calories_burned' => $data['calories_burned'] ?? null,
                    'notes' => $data['notes'] ?? null
                ]);
                echo json_encode(['success' => true, 'id' => $id, 'message' => 'Session logged successfully']);
            }
            break;

        case 'DELETE':
            if ($type === 'routines' && isset($_GET['id'])) {
                $db->execute("DELETE FROM gym_routines WHERE id = ? AND user_id = ?", [$_GET['id'], $userId]);
                echo json_encode(['success' => true, 'message' => 'Routine deleted successfully']);
            } elseif ($type === 'exercises' && isset($_GET['id'])) {
                $db->execute("DELETE FROM gym_exercises WHERE id = ? AND user_id = ?", [$_GET['id'], $userId]);
                echo json_encode(['success' => true, 'message' => 'Exercise deleted successfully']);
            } elseif ($type === 'sessions' && isset($_GET['id'])) {
                $db->execute("DELETE FROM gym_sessions WHERE id = ? AND user_id = ?", [$_GET['id'], $userId]);
                echo json_encode(['success' => true, 'message' => 'Session deleted successfully']);
            }
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
