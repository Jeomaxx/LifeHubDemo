<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = $auth->getUserId();
$db = Database::getInstance();

$module = $_GET['module'] ?? '';
$format = $_GET['format'] ?? 'csv'; // csv or template

// Define module configurations
$modules = [
    'gifts' => [
        'table' => 'gifts',
        'columns' => ['gift_name', 'recipient_name', 'occasion', 'provider_link', 'price', 'notes', 'purchased'],
        'template_headers' => ['Gift Name*', 'Recipient*', 'Occasion', 'Provider Link (URL)', 'Price', 'Notes', 'Purchased (TRUE/FALSE)'],
        'sample_data' => ['Wireless Headphones', 'John Doe', 'Birthday', 'https://amazon.com/product', '99.99', 'Black color preferred', 'FALSE']
    ],
    'bills' => [
        'table' => 'bills',
        'columns' => ['bill_name', 'amount', 'due_date', 'category', 'vendor', 'payment_status', 'recurring', 'notes'],
        'template_headers' => ['Bill Name*', 'Amount*', 'Due Date (YYYY-MM-DD)', 'Category', 'Vendor', 'Payment Status', 'Recurring (TRUE/FALSE)', 'Notes'],
        'sample_data' => ['Electric Bill', '150.00', '2025-01-15', 'Utilities', 'Power Company', 'pending', 'TRUE', 'Monthly payment']
    ],
    'tasks' => [
        'table' => 'tasks',
        'columns' => ['title', 'description', 'priority', 'due_date', 'category', 'status'],
        'template_headers' => ['Title*', 'Description', 'Priority (low/medium/high)', 'Due Date (YYYY-MM-DD)', 'Category', 'Status'],
        'sample_data' => ['Complete Project Report', 'Finish quarterly report', 'high', '2025-01-20', 'Work', 'pending']
    ],
    'finance' => [
        'table' => 'finance',
        'columns' => ['description', 'amount', 'type', 'category', 'date'],
        'template_headers' => ['Description*', 'Amount*', 'Type (income/expense)', 'Category', 'Date (YYYY-MM-DD)'],
        'sample_data' => ['Salary Payment', '5000.00', 'income', 'Salary', '2025-01-01']
    ],
    'gym' => [
        'table' => 'gym_routines',
        'columns' => ['routine_name', 'description'],
        'template_headers' => ['Routine Name*', 'Description'],
        'sample_data' => ['Morning Cardio', '30 min running and stretching']
    ],
    'diet' => [
        'table' => 'diet_plans',
        'columns' => ['meal_name', 'meal_type', 'calories', 'protein', 'carbs', 'fats', 'date'],
        'template_headers' => ['Meal Name*', 'Meal Type', 'Calories', 'Protein (g)', 'Carbs (g)', 'Fats (g)', 'Date (YYYY-MM-DD)'],
        'sample_data' => ['Grilled Chicken Salad', 'lunch', '450', '35', '20', '25', '2025-01-15']
    ],
    'water' => [
        'table' => 'water_intake',
        'columns' => ['date', 'amount', 'goal'],
        'template_headers' => ['Date (YYYY-MM-DD)*', 'Amount (ml)*', 'Daily Goal (ml)'],
        'sample_data' => ['2025-01-15', '2000', '2500']
    ],
    'goals' => [
        'table' => 'goals',
        'columns' => ['title', 'description', 'target_date', 'category', 'status'],
        'template_headers' => ['Goal Title*', 'Description', 'Target Date (YYYY-MM-DD)', 'Category', 'Status'],
        'sample_data' => ['Learn Spanish', 'Achieve B2 level fluency', '2025-12-31', 'Learning', 'active']
    ],
    'habits' => [
        'table' => 'habits',
        'columns' => ['habit_name', 'frequency', 'target_count', 'category'],
        'template_headers' => ['Habit Name*', 'Frequency (daily/weekly)', 'Target Count', 'Category'],
        'sample_data' => ['Morning Meditation', 'daily', '1', 'Health']
    ]
];

if (!isset($modules[$module])) {
    echo json_encode(['success' => false, 'message' => 'Invalid module']);
    exit;
}

$config = $modules[$module];

try {
    if ($format === 'template') {
        // Generate template file
        $filename = "{$module}_import_template_" . date('Y-m-d') . ".csv";
        header('Content-Type: text/csv');
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        
        $output = fopen('php://output', 'w');
        fputcsv($output, $config['template_headers']);
        fputcsv($output, $config['sample_data']);
        fputcsv($output, array_fill(0, count($config['template_headers']), '')); // Empty row for user data
        fclose($output);
        exit;
    } else {
        // Export actual data
        $query = "SELECT " . implode(', ', $config['columns']) . " FROM {$config['table']} WHERE user_id = ? ORDER BY created_at DESC";
        $data = $db->fetchAll($query, [$userId]);
        
        if (empty($data)) {
            echo json_encode(['success' => false, 'message' => 'No data to export']);
            exit;
        }
        
        $filename = "{$module}_export_" . date('Y-m-d_His') . ".csv";
        header('Content-Type: text/csv');
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        
        $output = fopen('php://output', 'w');
        fputcsv($output, $config['columns']);
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        fclose($output);
        exit;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Export failed: ' . $e->getMessage()]);
}
