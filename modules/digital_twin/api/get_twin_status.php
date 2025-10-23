<?php
require_once '../../../includes/config.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/db.php';

header('Content-Type: application/json');

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$db = getDB();
$userId = $auth->getUserId();

try {
    $model = $db->fetchOne(
        "SELECT * FROM digital_twin_models WHERE user_id = ? AND is_active = true ORDER BY created_at DESC LIMIT 1",
        [$userId]
    );

    if (!$model) {
        $modelId = $db->insert('digital_twin_models', [
            'user_id' => $userId,
            'model_version' => '1.0',
            'training_data' => json_encode([]),
            'prediction_accuracy' => 85.0,
            'is_active' => true
        ]);
        
        $model = $db->fetchOne("SELECT * FROM digital_twin_models WHERE id = ?", [$modelId]);
    }

    echo json_encode([
        'success' => true,
        'model' => $model
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch digital twin status'
    ]);
}
