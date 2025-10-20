<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';

header('Content-Type: application/json');

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = $auth->getUserId();
$db = Database::getInstance();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$type = $_GET['type'] ?? '';

try {
    switch ($method) {
        case 'GET':
            if ($action === 'stats') {
                $year = $_GET['year'] ?? date('Y');
                $stats = [
                    'total_income' => $db->fetchColumn("SELECT COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) FROM finance WHERE user_id = ? AND EXTRACT(YEAR FROM date) = ?", [$userId, $year]) ?: 0,
                    'total_deductions' => $db->fetchColumn("SELECT COALESCE(SUM(amount), 0) FROM tax_documents WHERE user_id = ? AND tax_year = ? AND category_id IN (SELECT id FROM tax_categories WHERE user_id = ? AND deductible = true)", [$userId, $year, $userId]) ?: 0,
                    'total_documents' => $db->fetchColumn("SELECT COUNT(*) FROM tax_documents WHERE user_id = ? AND tax_year = ?", [$userId, $year]) ?: 0,
                    'estimated_tax' => 0
                ];
                
                $stats['estimated_tax'] = ($stats['total_income'] - $stats['total_deductions']) * 0.22;
                
                echo json_encode(['success' => true, 'data' => $stats]);
            } elseif ($type === 'categories') {
                $categories = $db->fetchAll("SELECT * FROM tax_categories WHERE user_id = ? ORDER BY category_name", [$userId]);
                echo json_encode(['success' => true, 'data' => $categories]);
            } elseif ($type === 'documents') {
                $year = $_GET['year'] ?? date('Y');
                $documents = $db->fetchAll("SELECT d.*, c.category_name FROM tax_documents d LEFT JOIN tax_categories c ON d.category_id = c.id WHERE d.user_id = ? AND d.tax_year = ? ORDER BY d.upload_date DESC", [$userId, $year]);
                echo json_encode(['success' => true, 'data' => $documents]);
            } elseif ($type === 'reports') {
                $reports = $db->fetchAll("SELECT * FROM tax_reports WHERE user_id = ? ORDER BY generated_at DESC LIMIT 20", [$userId]);
                echo json_encode(['success' => true, 'data' => $reports]);
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            if ($type === 'category') {
                $id = $db->insert('tax_categories', [
                    'user_id' => $userId,
                    'category_name' => $data['category_name'],
                    'tax_year' => $data['tax_year'] ?? date('Y'),
                    'deductible' => $data['deductible'] ?? false,
                    'description' => $data['description'] ?? null
                ]);
                echo json_encode(['success' => true, 'id' => $id, 'message' => 'Tax category created']);
            } elseif ($type === 'document') {
                $id = $db->insert('tax_documents', [
                    'user_id' => $userId,
                    'document_type' => $data['document_type'],
                    'tax_year' => $data['tax_year'] ?? date('Y'),
                    'file_name' => $data['file_name'] ?? null,
                    'file_path' => $data['file_path'] ?? null,
                    'amount' => $data['amount'] ?? null,
                    'category_id' => $data['category_id'] ?? null,
                    'notes' => $data['notes'] ?? null
                ]);
                echo json_encode(['success' => true, 'id' => $id, 'message' => 'Document uploaded']);
            } elseif ($action === 'generate-report') {
                $year = $data['tax_year'] ?? date('Y');
                
                $income = $db->fetchColumn("SELECT COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) FROM finance WHERE user_id = ? AND EXTRACT(YEAR FROM date) = ?", [$userId, $year]) ?: 0;
                $expenses = $db->fetchColumn("SELECT COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) FROM finance WHERE user_id = ? AND EXTRACT(YEAR FROM date) = ?", [$userId, $year]) ?: 0;
                $deductions = $db->fetchColumn("SELECT COALESCE(SUM(amount), 0) FROM tax_documents WHERE user_id = ? AND tax_year = ? AND category_id IN (SELECT id FROM tax_categories WHERE user_id = ? AND deductible = true)", [$userId, $year, $userId]) ?: 0;
                
                $netIncome = $income - $deductions;
                $estimatedTax = $netIncome * 0.22;
                
                $reportId = $db->insert('tax_reports', [
                    'user_id' => $userId,
                    'report_type' => 'annual',
                    'tax_year' => $year,
                    'start_date' => "$year-01-01",
                    'end_date' => "$year-12-31",
                    'total_income' => $income,
                    'total_expenses' => $expenses,
                    'total_deductions' => $deductions,
                    'net_income' => $netIncome,
                    'estimated_tax' => $estimatedTax
                ]);
                
                echo json_encode([
                    'success' => true,
                    'report_id' => $reportId,
                    'message' => 'Tax report generated successfully',
                    'data' => [
                        'total_income' => $income,
                        'total_expenses' => $expenses,
                        'total_deductions' => $deductions,
                        'net_income' => $netIncome,
                        'estimated_tax' => $estimatedTax
                    ]
                ]);
            }
            break;

        case 'DELETE':
            $id = $_GET['id'] ?? null;
            if (!$id) throw new Exception('ID required');
            
            if ($type === 'category') {
                $affected = $db->execute("DELETE FROM tax_categories WHERE id = ? AND user_id = ?", [$id, $userId]);
                if ($affected === 0) {
                    throw new Exception('Category not found or access denied');
                }
                echo json_encode(['success' => true, 'message' => 'Category deleted']);
            } elseif ($type === 'document') {
                $affected = $db->execute("DELETE FROM tax_documents WHERE id = ? AND user_id = ?", [$id, $userId]);
                if ($affected === 0) {
                    throw new Exception('Document not found or access denied');
                }
                echo json_encode(['success' => true, 'message' => 'Document deleted']);
            }
            break;

        default:
            throw new Exception('Invalid request method');
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
