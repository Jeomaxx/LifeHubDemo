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
    $subscriptions = $db->fetchAll(
        "SELECT * FROM subscriptions 
         WHERE user_id = ? AND status = 'active'
         ORDER BY cost DESC",
        [$userId]
    );

    $totalMonthlyCost = 0;
    $totalAnnualCost = 0;
    $categoryBreakdown = [];
    $optimizationSuggestions = [];
    $unusedSubscriptions = [];

    foreach ($subscriptions as $subscription) {
        $cost = floatval($subscription['cost']);
        $billingCycle = $subscription['billing_cycle'] ?? 'monthly';
        $category = $subscription['category'] ?? 'other';
        $lastUsed = $subscription['last_used'] ?? null;

        $monthlyCost = $cost;
        if ($billingCycle === 'yearly' || $billingCycle === 'annual') {
            $monthlyCost = $cost / 12;
        }

        $totalMonthlyCost += $monthlyCost;
        $totalAnnualCost += $monthlyCost * 12;

        if (!isset($categoryBreakdown[$category])) {
            $categoryBreakdown[$category] = [
                'count' => 0,
                'monthly_cost' => 0,
                'subscriptions' => []
            ];
        }

        $categoryBreakdown[$category]['count']++;
        $categoryBreakdown[$category]['monthly_cost'] += $monthlyCost;
        $categoryBreakdown[$category]['subscriptions'][] = $subscription['name'];

        if ($lastUsed && strtotime($lastUsed) < strtotime('-60 days')) {
            $unusedSubscriptions[] = [
                'name' => $subscription['name'],
                'monthly_cost' => round($monthlyCost, 2),
                'last_used' => $lastUsed,
                'potential_savings' => round($monthlyCost * 12, 2)
            ];
        }

        if ($cost > 50 && $billingCycle === 'monthly') {
            $annualSavings = $cost * 12 * 0.15;
            $optimizationSuggestions[] = [
                'type' => 'switch_to_annual',
                'subscription' => $subscription['name'],
                'current_annual_cost' => round($cost * 12, 2),
                'estimated_annual_cost' => round($cost * 12 * 0.85, 2),
                'potential_savings' => round($annualSavings, 2),
                'recommendation' => "Switch to annual billing to save approximately $" . round($annualSavings, 2)
            ];
        }
    }

    $duplicateCategories = [];
    foreach ($categoryBreakdown as $category => $data) {
        if ($data['count'] > 1 && $category !== 'other') {
            $duplicateCategories[] = [
                'category' => $category,
                'count' => $data['count'],
                'monthly_cost' => round($data['monthly_cost'], 2),
                'subscriptions' => $data['subscriptions'],
                'suggestion' => "You have {$data['count']} {$category} subscriptions. Consider consolidating to save money."
            ];
        }
    }

    if (!empty($duplicateCategories)) {
        $optimizationSuggestions[] = [
            'type' => 'consolidate_services',
            'categories' => $duplicateCategories,
            'potential_savings' => round(array_sum(array_column($duplicateCategories, 'monthly_cost')) * 0.3, 2)
        ];
    }

    if (!empty($unusedSubscriptions)) {
        $totalUnusedCost = array_sum(array_column($unusedSubscriptions, 'potential_savings'));
        $optimizationSuggestions[] = [
            'type' => 'cancel_unused',
            'subscriptions' => $unusedSubscriptions,
            'potential_savings' => round($totalUnusedCost, 2),
            'recommendation' => "Cancel unused subscriptions to save $" . round($totalUnusedCost, 2) . " annually"
        ];
    }

    $analysis = [
        'summary' => [
            'total_subscriptions' => count($subscriptions),
            'total_monthly_cost' => round($totalMonthlyCost, 2),
            'total_annual_cost' => round($totalAnnualCost, 2)
        ],
        'category_breakdown' => $categoryBreakdown,
        'unused_subscriptions' => $unusedSubscriptions,
        'optimization_opportunities' => $optimizationSuggestions,
        'total_potential_savings' => round(
            array_sum(array_column($optimizationSuggestions, 'potential_savings')),
            2
        ),
        'analyzed_at' => date('Y-m-d H:i:s')
    ];

    echo json_encode([
        'success' => true,
        'analysis' => $analysis
    ]);
} catch (Exception $e) {
    error_log("Subscription analysis error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to analyze subscriptions'
    ]);
}
