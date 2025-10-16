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

$module = $_POST['module'] ?? '';

// Module configurations
$modules = [
    'gifts' => [
        'table' => 'gifts',
        'required_fields' => ['gift_name', 'recipient_name'],
        'field_mapping' => [
            'Gift Name' => 'gift_name',
            'Recipient' => 'recipient_name',
            'Occasion' => 'occasion',
            'Provider Link (URL)' => 'provider_link',
            'Price' => 'price',
            'Notes' => 'notes',
            'Purchased (TRUE/FALSE)' => 'purchased'
        ],
        'boolean_fields' => ['purchased'],
        'numeric_fields' => ['price']
    ],
    'bills' => [
        'table' => 'bills',
        'required_fields' => ['bill_name', 'amount'],
        'field_mapping' => [
            'Bill Name' => 'bill_name',
            'Amount' => 'amount',
            'Due Date (YYYY-MM-DD)' => 'due_date',
            'Category' => 'category',
            'Vendor' => 'vendor',
            'Payment Status' => 'payment_status',
            'Recurring (TRUE/FALSE)' => 'recurring',
            'Notes' => 'notes'
        ],
        'boolean_fields' => ['recurring'],
        'numeric_fields' => ['amount']
    ],
    'tasks' => [
        'table' => 'tasks',
        'required_fields' => ['title'],
        'field_mapping' => [
            'Title' => 'title',
            'Description' => 'description',
            'Priority (low/medium/high)' => 'priority',
            'Due Date (YYYY-MM-DD)' => 'due_date',
            'Category' => 'category',
            'Status' => 'status'
        ]
    ],
    'finance' => [
        'table' => 'finance',
        'required_fields' => ['description', 'amount', 'type'],
        'field_mapping' => [
            'Description' => 'description',
            'Amount' => 'amount',
            'Type (income/expense)' => 'type',
            'Category' => 'category',
            'Date (YYYY-MM-DD)' => 'date'
        ],
        'numeric_fields' => ['amount']
    ],
    'goals' => [
        'table' => 'goals',
        'required_fields' => ['title'],
        'field_mapping' => [
            'Goal Title' => 'title',
            'Description' => 'description',
            'Target Date (YYYY-MM-DD)' => 'target_date',
            'Category' => 'category',
            'Status' => 'status'
        ]
    ],
    'habits' => [
        'table' => 'habits',
        'required_fields' => ['habit_name'],
        'field_mapping' => [
            'Habit Name' => 'habit_name',
            'Frequency (daily/weekly)' => 'frequency',
            'Target Count' => 'target_count',
            'Category' => 'category'
        ],
        'numeric_fields' => ['target_count']
    ]
];

if (!isset($modules[$module])) {
    echo json_encode(['success' => false, 'message' => 'Invalid module']);
    exit;
}

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'File upload failed']);
    exit;
}

$config = $modules[$module];
$errors = [];
$imported = 0;
$skipped = 0;

try {
    $file = fopen($_FILES['file']['tmp_name'], 'r');
    $headers = fgetcsv($file);
    
    // Map headers to database fields
    $fieldMap = [];
    foreach ($headers as $index => $header) {
        $cleanHeader = trim(str_replace('*', '', $header));
        if (isset($config['field_mapping'][$cleanHeader])) {
            $fieldMap[$index] = $config['field_mapping'][$cleanHeader];
        }
    }
    
    $rowNumber = 1;
    while (($row = fgetcsv($file)) !== false) {
        $rowNumber++;
        
        // Skip empty rows
        if (empty(array_filter($row))) {
            continue;
        }
        
        $data = ['user_id' => $userId];
        $rowErrors = [];
        
        // Map data
        foreach ($row as $index => $value) {
            if (isset($fieldMap[$index])) {
                $field = $fieldMap[$index];
                $value = trim($value);
                
                // Convert boolean fields
                if (isset($config['boolean_fields']) && in_array($field, $config['boolean_fields'])) {
                    $value = strtoupper($value) === 'TRUE' || $value === '1';
                }
                
                // Convert numeric fields
                if (isset($config['numeric_fields']) && in_array($field, $config['numeric_fields'])) {
                    $value = $value === '' ? null : (float)$value;
                }
                
                // Handle empty values
                if ($value === '' && !in_array($field, $config['required_fields'])) {
                    $value = null;
                }
                
                $data[$field] = $value;
            }
        }
        
        // Validate required fields
        foreach ($config['required_fields'] as $required) {
            if (empty($data[$required])) {
                $rowErrors[] = "Missing required field: $required";
            }
        }
        
        if (!empty($rowErrors)) {
            $errors[] = "Row $rowNumber: " . implode(', ', $rowErrors);
            $skipped++;
            continue;
        }
        
        // Insert into database
        try {
            $db->insert($config['table'], $data);
            $imported++;
        } catch (Exception $e) {
            $errors[] = "Row $rowNumber: Database error - " . $e->getMessage();
            $skipped++;
        }
    }
    
    fclose($file);
    
    echo json_encode([
        'success' => true,
        'imported' => $imported,
        'skipped' => $skipped,
        'errors' => $errors,
        'message' => "Successfully imported $imported records" . ($skipped > 0 ? " ($skipped skipped due to errors)" : '')
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Import failed: ' . $e->getMessage()]);
}
