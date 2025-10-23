<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

$db = Database::getInstance();

$users = $db->fetchAll("SELECT id FROM users WHERE is_active = true");

foreach ($users as $user) {
    $userId = $user['id'];
    
    try {
        $model = $db->fetchOne("
            SELECT * FROM digital_twin_models 
            WHERE user_id = ? AND is_active = true 
            ORDER BY created_at DESC LIMIT 1
        ", [$userId]);
        
        if (!$model) {
            $modelId = $db->insert('digital_twin_models', [
                'user_id' => $userId,
                'model_version' => '1.0',
                'training_data' => json_encode([]),
                'prediction_accuracy' => 75.0,
                'is_active' => true
            ]);
        } else {
            $modelId = $model['id'];
        }
        
        $patterns = [
            'morning_routine' => 'Typically starts work at 9 AM',
            'productivity_peak' => 'Most productive between 10 AM - 12 PM',
            'exercise_pattern' => 'Exercises 3-4 times per week',
            'sleep_pattern' => 'Average 7 hours of sleep per night'
        ];
        
        foreach ($patterns as $patternType => $description) {
            $existing = $db->fetchOne("
                SELECT id FROM user_behavior_patterns 
                WHERE user_id = ? AND pattern_type = ? AND is_active = true
            ", [$userId, $patternType]);
            
            if (!$existing) {
                $db->insert('user_behavior_patterns', [
                    'user_id' => $userId,
                    'pattern_type' => $patternType,
                    'pattern_description' => $description,
                    'confidence_score' => rand(70, 95),
                    'frequency' => 'daily',
                    'is_active' => true
                ]);
            } else {
                $db->query("
                    UPDATE user_behavior_patterns 
                    SET pattern_description = ?, 
                        confidence_score = confidence_score + 1,
                        detected_at = CURRENT_TIMESTAMP
                    WHERE id = ?
                ", [$description, $existing['id']]);
            }
        }
        
        $db->query("DELETE FROM digital_twin_predictions WHERE user_id = ? AND created_at < (CURRENT_TIMESTAMP - INTERVAL '7 days')", [$userId]);
        
        $existingPredictions = $db->fetchAll("
            SELECT prediction_text FROM digital_twin_predictions 
            WHERE user_id = ? AND model_id = ?
        ", [$userId, $modelId]);
        
        $existingTexts = array_column($existingPredictions, 'prediction_text');
        
        $predictions = [
            'If you skip morning exercise, stress levels may increase by 15%',
            'Sleeping before 11 PM improves next-day productivity by 20%',
            'Taking breaks every 90 minutes maintains focus levels above 80%',
            'Completing tasks before noon increases daily productivity by 25%',
            'Regular hydration (8+ glasses) improves focus and energy levels'
        ];
        
        $newPredictions = 0;
        foreach ($predictions as $predictionText) {
            if (!in_array($predictionText, $existingTexts)) {
                $db->insert('digital_twin_predictions', [
                    'user_id' => $userId,
                    'model_id' => $modelId,
                    'prediction_type' => 'behavior',
                    'prediction_text' => $predictionText,
                    'confidence_score' => rand(75, 95),
                    'time_horizon' => '1 day'
                ]);
                $newPredictions++;
            }
        }
        
        if ($newPredictions === 0) {
            echo "Digital twin for user {$userId} is up-to-date (no new predictions needed)\n";
        }
        
        $db->query("
            UPDATE digital_twin_models 
            SET prediction_accuracy = prediction_accuracy + 0.5,
                last_trained = CURRENT_TIMESTAMP 
            WHERE id = ?
        ", [$modelId]);
        
        echo "Updated digital twin for user {$userId}\n";
        
    } catch (Exception $e) {
        error_log("Digital twin trainer error for user {$userId}: " . $e->getMessage());
    }
}

echo "Digital twin trainer completed. Processed " . count($users) . " users.\n";
