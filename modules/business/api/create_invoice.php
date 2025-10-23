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

$clientName = trim($_POST['client_name'] ?? '');
$invoiceNumber = trim($_POST['invoice_number'] ?? '');
$items = json_decode($_POST['items'] ?? '[]', true);
$dueDate = $_POST['due_date'] ?? null;
$notes = trim($_POST['notes'] ?? '');

if (empty($clientName) || empty($invoiceNumber) || empty($items)) {
    echo json_encode(['success' => false, 'message' => 'Client name, invoice number, and items are required']);
    exit;
}

try {
    $subtotal = 0;
    foreach ($items as $item) {
        $subtotal += floatval($item['quantity'] ?? 1) * floatval($item['rate'] ?? 0);
    }

    $taxRate = 0.10;
    $taxAmount = $subtotal * $taxRate;
    $total = $subtotal + $taxAmount;

    $invoiceId = $db->insert('business_invoices', [
        'user_id' => $userId,
        'client_name' => $clientName,
        'invoice_number' => $invoiceNumber,
        'items' => json_encode($items),
        'subtotal' => $subtotal,
        'tax_amount' => $taxAmount,
        'total_amount' => $total,
        'due_date' => $dueDate,
        'notes' => $notes,
        'status' => 'draft'
    ]);

    echo json_encode([
        'success' => true,
        'invoice_id' => $invoiceId,
        'invoice_details' => [
            'subtotal' => round($subtotal, 2),
            'tax' => round($taxAmount, 2),
            'total' => round($total, 2)
        ],
        'message' => 'Invoice created successfully'
    ]);
} catch (Exception $e) {
    error_log("Invoice creation error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to create invoice'
    ]);
}
