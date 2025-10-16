<?php
/**
 * Bills Budget Impact API
 * Check impact of bills on linked budgets
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$action = $_GET['action'] ?? 'check';

switch ($action) {
    case 'check':
        checkBudgetImpact($userId, $db);
        break;
    case 'summary':
        getBudgetSummary($userId, $db);
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
}

function checkBudgetImpact($userId, $db) {
    $billId = $_GET['bill_id'] ?? null;
    $budgetId = $_GET['budget_id'] ?? null;
    
    if (!$billId && !$budgetId) {
        http_response_code(400);
        echo json_encode(['error' => 'Either bill_id or budget_id is required']);
        return;
    }
    
    if ($billId) {
        // Check specific bill impact
        $bill = $db->fetchOne("SELECT * FROM bills WHERE id = ? AND user_id = ?", [$billId, $userId]);
        
        if (!$bill || !$bill['budget_id']) {
            echo json_encode(['has_budget' => false, 'impact' => null]);
            return;
        }
        
        $budgetId = $bill['budget_id'];
    }
    
    // Get budget info
    $budget = $db->fetchOne("SELECT * FROM budgets WHERE id = ? AND user_id = ?", [$budgetId, $userId]);
    
    if (!$budget) {
        http_response_code(404);
        echo json_encode(['error' => 'Budget not found']);
        return;
    }
    
    // Calculate total bills for this budget this month
    $currentMonth = date('Y-m');
    $totalBills = $db->fetchColumn(
        "SELECT COALESCE(SUM(amount), 0) 
         FROM bills 
         WHERE user_id = ? 
         AND budget_id = ? 
         AND TO_CHAR(due_date, 'YYYY-MM') = ?
         AND payment_status != 'paid'",
        [$userId, $budgetId, $currentMonth]
    );
    
    // Calculate actual spending from finance table
    $actualSpent = $db->fetchColumn(
        "SELECT COALESCE(SUM(amount), 0) 
         FROM finance 
         WHERE user_id = ? 
         AND category = ? 
         AND type = 'expense'
         AND TO_CHAR(date, 'YYYY-MM') = ?",
        [$userId, $budget['category'], $currentMonth]
    ) ?? 0;
    
    // Calculate total committed (actual + pending bills)
    $totalCommitted = $actualSpent + $totalBills;
    
    // Calculate remaining budget
    $remaining = $budget['monthly_limit'] - $totalCommitted;
    $percentUsed = ($totalCommitted / $budget['monthly_limit']) * 100;
    
    // Determine warning level
    $warningLevel = 'safe';
    $message = 'Budget is healthy';
    
    if ($percentUsed >= 100) {
        $warningLevel = 'critical';
        $message = 'Budget exceeded! Over by ' . formatCurrency(abs($remaining));
    } elseif ($percentUsed >= 90) {
        $warningLevel = 'danger';
        $message = 'Budget nearly exceeded! Only ' . formatCurrency($remaining) . ' remaining';
    } elseif ($percentUsed >= 75) {
        $warningLevel = 'warning';
        $message = 'Budget usage high. ' . formatCurrency($remaining) . ' remaining';
    } else {
        $message = formatCurrency($remaining) . ' remaining in budget';
    }
    
    echo json_encode([
        'has_budget' => true,
        'budget' => [
            'id' => $budget['id'],
            'category' => $budget['category'],
            'monthly_limit' => $budget['monthly_limit']
        ],
        'impact' => [
            'actual_spent' => $actualSpent,
            'pending_bills' => $totalBills,
            'total_committed' => $totalCommitted,
            'remaining' => $remaining,
            'percent_used' => round($percentUsed, 1),
            'warning_level' => $warningLevel,
            'message' => $message
        ]
    ]);
}

function getBudgetSummary($userId, $db) {
    $currentMonth = date('Y-m');
    
    // Get all budgets with their bill impacts
    $budgets = $db->fetchAll("SELECT * FROM budgets WHERE user_id = ? ORDER BY category", [$userId]);
    
    $summary = [];
    
    foreach ($budgets as $budget) {
        // Get pending bills for this budget
        $bills = $db->fetchAll(
            "SELECT id, name, amount, due_date, payment_status 
             FROM bills 
             WHERE user_id = ? 
             AND budget_id = ? 
             AND TO_CHAR(due_date, 'YYYY-MM') = ?
             AND payment_status != 'paid'
             ORDER BY due_date",
            [$userId, $budget['id'], $currentMonth]
        );
        
        $totalBills = array_sum(array_column($bills, 'amount'));
        
        // Get actual spending
        $actualSpent = $db->fetchColumn(
            "SELECT COALESCE(SUM(amount), 0) 
             FROM finance 
             WHERE user_id = ? 
             AND category = ? 
             AND type = 'expense'
             AND TO_CHAR(date, 'YYYY-MM') = ?",
            [$userId, $budget['category'], $currentMonth]
        ) ?? 0;
        
        $totalCommitted = $actualSpent + $totalBills;
        $remaining = $budget['monthly_limit'] - $totalCommitted;
        $percentUsed = ($totalCommitted / $budget['monthly_limit']) * 100;
        
        // Warning level
        $warningLevel = 'safe';
        if ($percentUsed >= 100) $warningLevel = 'critical';
        elseif ($percentUsed >= 90) $warningLevel = 'danger';
        elseif ($percentUsed >= 75) $warningLevel = 'warning';
        
        $summary[] = [
            'budget' => $budget,
            'bills' => $bills,
            'metrics' => [
                'actual_spent' => $actualSpent,
                'pending_bills' => $totalBills,
                'total_committed' => $totalCommitted,
                'remaining' => $remaining,
                'percent_used' => round($percentUsed, 1),
                'warning_level' => $warningLevel
            ]
        ];
    }
    
    echo json_encode([
        'success' => true,
        'month' => date('F Y'),
        'budgets' => $summary
    ]);
}

function formatCurrency($amount) {
    return '$' . number_format($amount, 2);
}
