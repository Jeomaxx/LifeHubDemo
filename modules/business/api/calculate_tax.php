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

$year = intval($_GET['year'] ?? date('Y'));
$businessType = $_GET['business_type'] ?? 'self_employed';

try {
    $income = $db->fetchOne(
        "SELECT COALESCE(SUM(total_amount), 0) as total_income
         FROM business_invoices
         WHERE user_id = ? 
         AND status = 'paid' 
         AND EXTRACT(YEAR FROM created_at) = ?",
        [$userId, $year]
    );

    $expenses = $db->fetchOne(
        "SELECT COALESCE(SUM(amount), 0) as total_expenses
         FROM business_expenses
         WHERE user_id = ? 
         AND EXTRACT(YEAR FROM expense_date) = ?",
        [$userId, $year]
    );

    $totalIncome = floatval($income['total_income'] ?? 0);
    $totalExpenses = floatval($expenses['total_expenses'] ?? 0);
    $netIncome = $totalIncome - $totalExpenses;

    $standardDeduction = 13850;
    
    $taxableIncome = max(0, $netIncome - $standardDeduction);

    $taxBrackets = [
        ['min' => 0, 'max' => 11000, 'rate' => 0.10],
        ['min' => 11001, 'max' => 44725, 'rate' => 0.12],
        ['min' => 44726, 'max' => 95375, 'rate' => 0.22],
        ['min' => 95376, 'max' => 182100, 'rate' => 0.24],
        ['min' => 182101, 'max' => 231250, 'rate' => 0.32],
        ['min' => 231251, 'max' => 578125, 'rate' => 0.35],
        ['min' => 578126, 'max' => PHP_INT_MAX, 'rate' => 0.37]
    ];

    $federalTax = 0;
    $remainingIncome = $taxableIncome;

    foreach ($taxBrackets as $bracket) {
        if ($remainingIncome <= 0) break;

        $bracketMin = $bracket['min'];
        $bracketMax = $bracket['max'];
        $rate = $bracket['rate'];

        $incomeInBracket = min($remainingIncome, $bracketMax - $bracketMin + 1);
        $taxInBracket = $incomeInBracket * $rate;
        
        $federalTax += $taxInBracket;
        $remainingIncome -= $incomeInBracket;
    }

    $selfEmploymentTax = $businessType === 'self_employed' ? $netIncome * 0.153 : 0;

    $estimatedStateTax = $taxableIncome * 0.05;

    $totalTax = $federalTax + $selfEmploymentTax + $estimatedStateTax;

    $effectiveTaxRate = $netIncome > 0 ? ($totalTax / $netIncome) * 100 : 0;

    $quarterlyEstimate = $totalTax / 4;

    $breakdown = [
        'year' => $year,
        'income' => [
            'gross_income' => round($totalIncome, 2),
            'business_expenses' => round($totalExpenses, 2),
            'net_income' => round($netIncome, 2),
            'standard_deduction' => round($standardDeduction, 2),
            'taxable_income' => round($taxableIncome, 2)
        ],
        'taxes' => [
            'federal_income_tax' => round($federalTax, 2),
            'self_employment_tax' => round($selfEmploymentTax, 2),
            'estimated_state_tax' => round($estimatedStateTax, 2),
            'total_tax_liability' => round($totalTax, 2)
        ],
        'rates' => [
            'effective_tax_rate' => round($effectiveTaxRate, 2) . '%',
            'quarterly_estimate' => round($quarterlyEstimate, 2)
        ],
        'recommendations' => generateTaxRecommendations($netIncome, $totalExpenses, $selfEmploymentTax)
    ];

    echo json_encode([
        'success' => true,
        'tax_calculation' => $breakdown
    ]);
} catch (Exception $e) {
    error_log("Tax calculation error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to calculate tax'
    ]);
}

function generateTaxRecommendations($netIncome, $expenses, $selfEmploymentTax) {
    $recommendations = [];

    if ($expenses < $netIncome * 0.3) {
        $recommendations[] = "Track all business expenses - you may be eligible for more deductions";
    }

    if ($selfEmploymentTax > 0) {
        $recommendations[] = "Consider setting up a SEP IRA or Solo 401(k) to reduce taxable income";
    }

    $recommendations[] = "Make quarterly estimated tax payments to avoid penalties";

    if ($netIncome > 50000) {
        $recommendations[] = "Consult with a tax professional about S-Corp election to potentially reduce self-employment tax";
    }

    return $recommendations;
}
