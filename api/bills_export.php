<?php
/**
 * Bills Export API
 * Export bills to CSV or JSON
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$format = $_GET['format'] ?? 'csv';
$status = $_GET['status'] ?? '';
$category = $_GET['category'] ?? '';
$fromDate = $_GET['from_date'] ?? '';
$toDate = $_GET['to_date'] ?? '';

// Build query
$query = "SELECT * FROM bills WHERE user_id = ?";
$params = [$userId];

if ($status) {
    $query .= " AND payment_status = ?";
    $params[] = $status;
}

if ($category) {
    $query .= " AND category = ?";
    $params[] = $category;
}

if ($fromDate) {
    $query .= " AND due_date >= ?";
    $params[] = $fromDate;
}

if ($toDate) {
    $query .= " AND due_date <= ?";
    $params[] = $toDate;
}

$query .= " ORDER BY due_date ASC";
$bills = $db->fetchAll($query, $params);

if ($format === 'json') {
    exportAsJSON($bills);
} else {
    exportAsCSV($bills);
}

function exportAsCSV($bills) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="bills_export_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Headers
    fputcsv($output, [
        'ID', 'Name', 'Amount', 'Due Date', 'Payment Status', 
        'Category', 'Vendor', 'Recurring', 'Frequency', 
        'Payment Method', 'Notes', 'Created At'
    ]);
    
    // Data rows
    foreach ($bills as $bill) {
        fputcsv($output, [
            $bill['id'],
            $bill['name'],
            $bill['amount'],
            $bill['due_date'],
            $bill['payment_status'],
            $bill['category'] ?? '',
            $bill['vendor'] ?? '',
            $bill['recurring'] ? 'Yes' : 'No',
            $bill['frequency'] ?? '',
            $bill['payment_method'] ?? '',
            $bill['notes'] ?? '',
            $bill['created_at']
        ]);
    }
    
    fclose($output);
}

function exportAsJSON($bills) {
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="bills_export_' . date('Y-m-d') . '.json"');
    
    echo json_encode([
        'exported_at' => date('Y-m-d H:i:s'),
        'total_bills' => count($bills),
        'bills' => $bills
    ], JSON_PRETTY_PRINT);
}
