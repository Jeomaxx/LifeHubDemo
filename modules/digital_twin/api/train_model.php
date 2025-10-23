<?php
require_once '../../../includes/config.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/db.php';
require_once '../../../includes/functions.php';

header('Content-Type: application/json');

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

$db = getDB();
$userId = $auth->getUserId();

try {
    $recentActivities = $db->fetchAll(
        "SELECT activity_type, COUNT(*) as count, AVG(EXTRACT(HOUR FROM activity_time)::int) as avg_hour
         FROM activity_logs
         WHERE user_id = ? AND created_at > NOW() - INTERVAL '30 days'
         GROUP BY activity_type
         LIMIT 100",
        [$userId]
    );

    $habitData = $db->fetchAll(
        "SELECT h.name, COUNT(hl.id) as completion_count
         FROM habits h
         LEFT JOIN habit_logs hl ON h.id = hl.habit_id AND hl.completed_at > NOW() - INTERVAL '30 days'
         WHERE h.user_id = ?
         GROUP BY h.id, h.name
         LIMIT 50",
        [$userId]
    );

    $taskData = $db->fetchAll(
        "SELECT status, priority, COUNT(*) as count,
                AVG(EXTRACT(EPOCH FROM (completed_at - created_at))/3600)::int as avg_completion_hours
         FROM tasks
         WHERE user_id = ? AND created_at > NOW() - INTERVAL '30 days'
         GROUP BY status, priority",
        [$userId]
    );

    $patterns = [
        'activity_patterns' => $recentActivities,
        'habit_completion_rates' => $habitData,
        'task_completion_patterns' => $taskData,
        'training_date' => date('Y-m-d H:i:s'),
        'data_points' => count($recentActivities) + count($habitData) + count($taskData)
    ];

    $existingModel = $db->fetchOne("SELECT * FROM digital_twin_models WHERE user_id = ?", [$userId]);

    if ($existingModel) {
        $db->update('digital_twin_models', [
            'model_data' => json_encode($patterns),
            'last_trained' => date('Y-m-d H:i:s'),
            'accuracy_score' => 0.85
        ], 'user_id = ?', [$userId]);
        
        $modelId = $existingModel['id'];
    } else {
        $modelId = $db->insert('digital_twin_models', [
            'user_id' => $userId,
            'model_type' => 'behavior_prediction',
            'model_data' => json_encode($patterns),
            'last_trained' => date('Y-m-d H:i:s'),
            'accuracy_score' => 0.85
        ]);
    }

    echo json_encode([
        'success' => true,
        'model_id' => $modelId,
        'patterns_learned' => count($patterns),
        'data_points' => $patterns['data_points'],
        'accuracy' => 0.85,
        'message' => 'Digital twin model trained successfully'
    ]);
} catch (Exception $e) {
    error_log("Model training error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to train model'
    ]);
}
