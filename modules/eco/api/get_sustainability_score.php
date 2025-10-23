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

$period = $_GET['period'] ?? 'month';

try {
    $interval = '30 days';
    if ($period === 'week') $interval = '7 days';
    if ($period === 'year') $interval = '365 days';

    $impactSummary = $db->fetchOne(
        "SELECT 
            SUM(carbon_footprint) as total_carbon,
            SUM(water_usage) as total_water,
            SUM(energy_consumption) as total_energy,
            COUNT(*) as total_activities
         FROM eco_impact_logs
         WHERE user_id = ? AND logged_at > NOW() - INTERVAL '$interval'",
        [$userId]
    );

    $totalCarbon = floatval($impactSummary['total_carbon'] ?? 0);
    $totalWater = floatval($impactSummary['total_water'] ?? 0);
    $totalEnergy = floatval($impactSummary['total_energy'] ?? 0);

    $averageMonthlyCarbon = 500;
    
    $carbonScore = max(0, min(100, 100 - ($totalCarbon / $averageMonthlyCarbon * 100)));
    
    $sustainabilityScore = round($carbonScore);

    $grade = 'C';
    if ($sustainabilityScore >= 90) $grade = 'A+';
    elseif ($sustainabilityScore >= 80) $grade = 'A';
    elseif ($sustainabilityScore >= 70) $grade = 'B';
    elseif ($sustainabilityScore >= 60) $grade = 'C';
    elseif ($sustainabilityScore >= 50) $grade = 'D';
    else $grade = 'F';

    $treesEquivalent = round($totalCarbon / 21, 1);

    $improvements = [];
    if ($totalCarbon > 400) {
        $improvements[] = "Your carbon footprint is above average. Consider using public transport more often.";
    }
    if ($totalWater > 100000) {
        $improvements[] = "High water usage detected. Try reducing shower time and fixing leaks.";
    }
    if ($totalEnergy > 300) {
        $improvements[] = "Electricity consumption is high. Switch to energy-efficient appliances.";
    }

    echo json_encode([
        'success' => true,
        'sustainability_score' => $sustainabilityScore,
        'grade' => $grade,
        'period' => $period,
        'impact_summary' => [
            'total_carbon_kg' => round($totalCarbon, 2),
            'total_water_liters' => round($totalWater, 2),
            'total_energy_kwh' => round($totalEnergy, 2),
            'trees_equivalent' => $treesEquivalent
        ],
        'comparison' => [
            'vs_average' => $totalCarbon < $averageMonthlyCarbon ? 'below' : 'above',
            'difference_percentage' => round(abs(($totalCarbon - $averageMonthlyCarbon) / $averageMonthlyCarbon * 100), 1)
        ],
        'improvements' => $improvements
    ]);
} catch (Exception $e) {
    error_log("Sustainability score error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to calculate sustainability score'
    ]);
}
