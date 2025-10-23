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

$description = trim($_POST['description'] ?? '');
$amount = floatval($_POST['amount'] ?? 0);
$category = trim($_POST['category'] ?? '');
$expenseDate = $_POST['expense_date'] ?? date('Y-m-d');
$receiptPath = trim($_POST['receipt_path'] ?? '');

if (empty($description) || $amount <= 0) {
    echo json_encode(['success' => false, 'message' => 'Description and valid amount are required']);
    exit;
}

try {
    $expenseId = $db->insert('business_expenses', [
        'user_id' => $userId,
        'description' => $description,
        'amount' => $amount,
        'category' => $category,
        'expense_date' => $expenseDate,
        'receipt_path' => $receiptPath,
        'is_deductible' => true
    ]);

    echo json_encode([
        'success' => true,
        'expense_id' => $expenseId,
        'message' => 'Business expense tracked successfully'
    ]);
} catch (Exception $e) {
    error_log("Expense tracking error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to track expense'
    ]);
}
