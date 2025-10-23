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
    $summary = $db->fetchOne(
        "SELECT 
            AVG(eco_score) as eco_score,
            SUM(carbon_footprint) as carbon_footprint,
            SUM(water_usage) as water_usage,
            SUM(energy_usage) as energy_usage,
            SUM(waste_generated) as waste_generated
         FROM eco_impact_logs 
         WHERE user_id = ? 
         AND log_date >= CURRENT_DATE - INTERVAL '30 days'",
        [$userId]
    );

    if (!$summary || $summary['eco_score'] === null) {
        $summary = [
            'eco_score' => 75,
            'carbon_footprint' => 0,
            'water_usage' => 0,
            'energy_usage' => 0,
            'waste_generated' => 0
        ];
    }

    echo json_encode([
        'success' => true,
        'summary' => $summary
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch eco summary'
    ]);
}
