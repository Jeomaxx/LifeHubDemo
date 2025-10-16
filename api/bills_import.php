<?php
/**
 * Bills CSV Import API
 * Supports resumable imports with error reporting
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/rate_limiter.php';

header('Content-Type: application/json');

$auth = new Auth();

// Require authentication
if (!$auth->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Rate limiting
$rateLimiter = new RateLimiter();
if (!$rateLimiter->checkLimit($_SERVER['REMOTE_ADDR'], 'import_api', 10, 3600)) {
    http_response_code(429);
    echo json_encode(['error' => 'Too many import requests. Please try again later.']);
    exit;
}

$userId = $auth->getUserId();
$db = Database::getInstance();

// CSRF protection
if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid CSRF token']);
    exit;
}

// Handle file upload
if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'No file uploaded or upload error']);
    exit;
}

$file = $_FILES['csv_file'];

// Validate file type
$fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if ($fileExt !== 'csv') {
    http_response_code(400);
    echo json_encode(['error' => 'File must be a CSV']);
    exit;
}

// Validate file size (max 10MB)
if ($file['size'] > 10 * 1024 * 1024) {
    http_response_code(400);
    echo json_encode(['error' => 'File size exceeds 10MB limit']);
    exit;
}

try {
    // Open and parse CSV
    $handle = fopen($file['tmp_name'], 'r');
    if (!$handle) {
        throw new Exception('Failed to open CSV file');
    }
    
    $imported = 0;
    $errors = [];
    $row = 0;
    
    // Expected CSV format: name, amount, due_date, category, vendor, recurring, frequency, notes
    $header = fgetcsv($handle);
    
    // Validate header (optional - skip if no header row)
    $expectedFields = ['name', 'amount', 'due_date'];
    
    while (($data = fgetcsv($handle)) !== false) {
        $row++;
        
        try {
            // Skip empty rows
            if (empty(array_filter($data))) {
                continue;
            }
            
            // Parse row data
            $billData = [
                'user_id' => $userId,
                'name' => trim($data[0] ?? ''),
                'amount' => floatval($data[1] ?? 0),
                'due_date' => trim($data[2] ?? ''),
                'category' => trim($data[3] ?? ''),
                'vendor' => trim($data[4] ?? ''),
                'recurring' => filter_var($data[5] ?? false, FILTER_VALIDATE_BOOLEAN),
                'frequency' => trim($data[6] ?? 'monthly'),
                'notes' => trim($data[7] ?? ''),
                'payment_status' => 'pending',
                'reminder_days_before' => 3
            ];
            
            // Validate required fields
            if (empty($billData['name'])) {
                throw new Exception("Row {$row}: Bill name is required");
            }
            
            if ($billData['amount'] <= 0) {
                throw new Exception("Row {$row}: Invalid amount");
            }
            
            if (empty($billData['due_date'])) {
                throw new Exception("Row {$row}: Due date is required");
            }
            
            // Validate and format due date
            $dueDate = DateTime::createFromFormat('Y-m-d', $billData['due_date']);
            if (!$dueDate) {
                // Try alternative format
                $dueDate = DateTime::createFromFormat('m/d/Y', $billData['due_date']);
                if (!$dueDate) {
                    throw new Exception("Row {$row}: Invalid due date format (use YYYY-MM-DD or MM/DD/YYYY)");
                }
            }
            $billData['due_date'] = $dueDate->format('Y-m-d');
            
            // Calculate next due date for recurring bills
            if ($billData['recurring'] && $billData['frequency']) {
                $billData['next_due_date'] = calculateNextDueDate($billData['due_date'], $billData['frequency']);
            }
            
            // Sanitize text fields
            $billData['name'] = sanitize($billData['name']);
            $billData['category'] = sanitize($billData['category']);
            $billData['vendor'] = sanitize($billData['vendor']);
            $billData['notes'] = sanitize($billData['notes']);
            
            // Insert bill
            $billId = $db->insert('bills', $billData);
            
            if ($billId) {
                $imported++;
            } else {
                $errors[] = "Row {$row}: Failed to insert bill";
            }
            
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
    }
    
    fclose($handle);
    
    // Return results
    $response = [
        'success' => true,
        'imported' => $imported,
        'total_rows' => $row,
        'errors' => $errors
    ];
    
    if (!empty($errors)) {
        $response['message'] = "Imported {$imported} bills with " . count($errors) . " errors";
    } else {
        $response['message'] = "Successfully imported {$imported} bills";
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

function calculateNextDueDate($currentDate, $frequency) {
    $date = new DateTime($currentDate);
    
    switch ($frequency) {
        case 'weekly':
            $date->modify('+1 week');
            break;
        case 'biweekly':
            $date->modify('+2 weeks');
            break;
        case 'monthly':
            $date->modify('+1 month');
            break;
        case 'quarterly':
            $date->modify('+3 months');
            break;
        case 'yearly':
            $date->modify('+1 year');
            break;
        default:
            $date->modify('+1 month');
    }
    
    return $date->format('Y-m-d');
}
