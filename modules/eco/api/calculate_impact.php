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

$activityType = trim($_POST['activity_type'] ?? '');
$quantity = floatval($_POST['quantity'] ?? 0);
$unit = trim($_POST['unit'] ?? '');

if (empty($activityType)) {
    echo json_encode(['success' => false, 'message' => 'Activity type is required']);
    exit;
}

try {
    $carbonFootprint = 0;
    $waterUsage = 0;
    $energyConsumption = 0;

    switch ($activityType) {
        case 'car_travel':
            $carbonFootprint = $quantity * 0.24;
            break;
        case 'public_transport':
            $carbonFootprint = $quantity * 0.08; 
            break;
        case 'flight':
            $carbonFootprint = $quantity * 0.18;
            break;
        case 'electricity':
            $carbonFootprint = $quantity * 0.5;
            $energyConsumption = $quantity;
            break;
        case 'meat_consumption':
            $carbonFootprint = $quantity * 6.61;
            $waterUsage = $quantity * 15000;
            break;
        case 'vegetarian_meal':
            $carbonFootprint = $quantity * 1.05;
            $waterUsage = $quantity * 1500;
            break;
        case 'water_usage':
            $waterUsage = $quantity;
            $energyConsumption = $quantity * 0.001;
            break;
        case 'online_shopping':
            $carbonFootprint = $quantity * 0.5;
            break;
        case 'recycling':
            $carbonFootprint = -$quantity * 0.1;
            break;
        default:
            $carbonFootprint = $quantity * 0.5;
    }

    $logId = $db->insert('eco_impact_logs', [
        'user_id' => $userId,
        'activity_type' => $activityType,
        'quantity' => $quantity,
        'unit' => $unit,
        'carbon_footprint' => $carbonFootprint,
        'water_usage' => $waterUsage,
        'energy_consumption' => $energyConsumption
    ]);

    $tips = generateEcoTips($activityType, $carbonFootprint);

    echo json_encode([
        'success' => true,
        'log_id' => $logId,
        'impact' => [
            'carbon_footprint_kg' => round($carbonFootprint, 2),
            'water_usage_liters' => round($waterUsage, 2),
            'energy_consumption_kwh' => round($energyConsumption, 2)
        ],
        'sustainability_tips' => $tips,
        'message' => 'Environmental impact logged successfully'
    ]);
} catch (Exception $e) {
    error_log("Eco impact calculation error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to calculate impact'
    ]);
}

function generateEcoTips($activityType, $carbonFootprint) {
    $tips = [];

    switch ($activityType) {
        case 'car_travel':
            $tips[] = "Consider carpooling to reduce your carbon footprint by up to 50%";
            $tips[] = "Public transport emits 67% less CO2 per passenger than private cars";
            $tips[] = "For short trips under 3km, walking or cycling is healthier and eco-friendly";
            break;
        case 'flight':
            $tips[] = "Choose direct flights when possible - takeoffs and landings produce the most emissions";
            $tips[] = "Consider video conferencing as an alternative for business meetings";
            $tips[] = "Offset your flight carbon footprint through verified carbon offset programs";
            break;
        case 'meat_consumption':
            $tips[] = "Reducing meat consumption by one day per week can cut your carbon footprint by 8 lbs";
            $tips[] = "Plant-based proteins produce 90% fewer greenhouse gases than beef";
            $tips[] = "Try 'Meatless Mondays' as an easy way to start";
            break;
        case 'electricity':
            $tips[] = "Switch to LED bulbs to reduce energy consumption by 75%";
            $tips[] = "Unplug devices when not in use - they consume power even when off";
            $tips[] = "Consider renewable energy sources like solar panels";
            break;
        default:
            $tips[] = "Small changes in daily habits can make a big environmental impact";
            $tips[] = "Track your eco footprint regularly to identify improvement areas";
    }

    return array_slice($tips, 0, 3);
}
