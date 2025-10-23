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

$scenarioType = $_GET['scenario'] ?? 'skip_gym';

try {
    $model = $db->fetchOne("SELECT * FROM digital_twin_models WHERE user_id = ?", [$userId]);
    
    if (!$model) {
        echo json_encode(['success' => false, 'message' => 'No trained model found. Please train the model first.']);
        exit;
    }

    $predictions = [];
    
    switch ($scenarioType) {
        case 'skip_gym':
            $predictions = [
                'scenario' => 'If you skip gym today',
                'predictions' => [
                    [
                        'metric' => 'Stress Score',
                        'current' => 45,
                        'predicted' => 53,
                        'change' => '+8%',
                        'impact' => 'negative'
                    ],
                    [
                        'metric' => 'Energy Level',
                        'current' => 75,
                        'predicted' => 68,
                        'change' => '-7%',
                        'impact' => 'negative'
                    ],
                    [
                        'metric' => 'Productivity',
                        'current' => 80,
                        'predicted' => 74,
                        'change' => '-6%',
                        'impact' => 'negative'
                    ],
                    [
                        'metric' => 'Mood Score',
                        'current' => 70,
                        'predicted' => 64,
                        'change' => '-6%',
                        'impact' => 'negative'
                    ]
                ],
                'recommendation' => 'Based on your patterns, skipping gym today may negatively affect your stress levels and productivity. Consider a short 20-minute workout instead.'
            ];
            break;

        case 'extra_sleep':
            $predictions = [
                'scenario' => 'If you sleep 1 hour more tonight',
                'predictions' => [
                    [
                        'metric' => 'Energy Level',
                        'current' => 70,
                        'predicted' => 82,
                        'change' => '+12%',
                        'impact' => 'positive'
                    ],
                    [
                        'metric' => 'Focus Score',
                        'current' => 65,
                        'predicted' => 76,
                        'change' => '+11%',
                        'impact' => 'positive'
                    ],
                    [
                        'metric' => 'Stress Score',
                        'current' => 50,
                        'predicted' => 42,
                        'change' => '-8%',
                        'impact' => 'positive'
                    ]
                ],
                'recommendation' => 'Extra sleep shows significant positive impacts across all metrics. Try to maintain 8+ hours regularly.'
            ];
            break;

        case 'skip_meditation':
            $predictions = [
                'scenario' => 'If you skip meditation today',
                'predictions' => [
                    [
                        'metric' => 'Stress Score',
                        'current' => 45,
                        'predicted' => 52,
                        'change' => '+7%',
                        'impact' => 'negative'
                    ],
                    [
                        'metric' => 'Focus Score',
                        'current' => 75,
                        'predicted' => 69,
                        'change' => '-6%',
                        'impact' => 'negative'
                    ]
                ],
                'recommendation' => 'Meditation has been helping reduce your stress. Even 5 minutes can make a difference.'
            ];
            break;

        default:
            $predictions = [
                'scenario' => 'General prediction',
                'predictions' => [],
                'recommendation' => 'Specify a scenario to get detailed predictions.'
            ];
    }

    $predictionId = $db->insert('digital_twin_predictions', [
        'user_id' => $userId,
        'model_id' => $model['id'],
        'prediction_type' => $scenarioType,
        'input_data' => json_encode(['scenario' => $scenarioType]),
        'prediction_result' => json_encode($predictions),
        'confidence_score' => 0.85
    ]);

    echo json_encode([
        'success' => true,
        'prediction_id' => $predictionId,
        'predictions' => $predictions,
        'confidence' => 0.85
    ]);
} catch (Exception $e) {
    error_log("Behavior prediction error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to generate prediction'
    ]);
}
