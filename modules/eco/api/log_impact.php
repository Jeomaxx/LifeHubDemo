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

$category = trim($_POST['impact_category'] ?? '');
$description = trim($_POST['activity_description'] ?? '');
$carbonFootprint = floatval($_POST['carbon_footprint'] ?? 0);
$waterUsage = floatval($_POST['water_usage'] ?? 0);
$energyUsage = floatval($_POST['energy_usage'] ?? 0);
$wasteGenerated = floatval($_POST['waste_generated'] ?? 0);

if (empty($category)) {
    echo json_encode(['success' => false, 'message' => 'Impact category is required']);
    exit;
}

try {
    $ecoScore = 100 - ($carbonFootprint * 0.5 + $wasteGenerated * 0.3 + $energyUsage * 0.2);
    $ecoScore = max(0, min(100, $ecoScore));
    
    $logId = $db->insert('eco_impact_logs', [
        'user_id' => $userId,
        'log_date' => date('Y-m-d'),
        'impact_category' => $category,
        'activity_description' => $description,
        'carbon_footprint' => $carbonFootprint,
        'water_usage' => $waterUsage,
        'energy_usage' => $energyUsage,
        'waste_generated' => $wasteGenerated,
        'eco_score' => $ecoScore
    ]);

    echo json_encode([
        'success' => true,
        'log_id' => $logId,
        'eco_score' => $ecoScore,
        'message' => 'Impact logged successfully'
    ]);
} catch (Exception $e) {
    error_log("Eco impact log error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to log impact'
    ]);
}
