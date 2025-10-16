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
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'import_csv':
        if (!isset($_FILES['csv_file'])) {
            echo json_encode(['success' => false, 'message' => 'No file uploaded']);
            exit;
        }
        
        try {
            $file = $_FILES['csv_file'];
            $handle = fopen($file['tmp_name'], 'r');
            
            if (!$handle) {
                throw new Exception('Unable to read file');
            }
            
            $header = fgetcsv($handle);
            $imported = 0;
            $errors = [];
            
            $expectedHeaders = ['date', 'type', 'category', 'amount', 'description'];
            $headerMap = [];
            
            foreach ($header as $index => $col) {
                $col = strtolower(trim($col));
                if (in_array($col, $expectedHeaders)) {
                    $headerMap[$col] = $index;
                }
            }
            
            if (count($headerMap) < 4) {
                throw new Exception('Invalid CSV format. Expected columns: date, type, category, amount, description (optional)');
            }
            
            while (($row = fgetcsv($handle)) !== false) {
                try {
                    if (count($row) < 4) continue;
                    
                    $date = isset($headerMap['date']) ? trim($row[$headerMap['date']]) : null;
                    $type = isset($headerMap['type']) ? strtolower(trim($row[$headerMap['type']])) : 'expense';
                    $category = isset($headerMap['category']) ? trim($row[$headerMap['category']]) : 'Uncategorized';
                    $amount = isset($headerMap['amount']) ? floatval(str_replace(['$', ','], '', $row[$headerMap['amount']])) : 0;
                    $description = isset($headerMap['description']) ? trim($row[$headerMap['description']]) : '';
                    
                    if (!in_array($type, ['income', 'expense'])) {
                        $type = 'expense';
                    }
                    
                    if (empty($date)) {
                        $date = date('Y-m-d');
                    } else {
                        $date = date('Y-m-d', strtotime($date));
                    }
                    
                    if ($amount > 0) {
                        $db->execute(
                            "INSERT INTO finance (user_id, type, category, amount, date, description) 
                             VALUES (?, ?, ?, ?, ?, ?)",
                            [$userId, $type, $category, $amount, $date, $description]
                        );
                        $imported++;
                    }
                } catch (Exception $e) {
                    $errors[] = "Row " . ($imported + 1) . ": " . $e->getMessage();
                }
            }
            
            fclose($handle);
            
            echo json_encode([
                'success' => true,
                'message' => "Successfully imported {$imported} transactions",
                'imported' => $imported,
                'errors' => $errors
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;
    
    case 'export_template':
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="finance_import_template.csv"');
        
        echo "date,type,category,amount,description\n";
        echo date('Y-m-d') . ",expense,Groceries,50.00,Weekly shopping\n";
        echo date('Y-m-d') . ",income,Salary,3000.00,Monthly salary\n";
        echo date('Y-m-d') . ",expense,Transportation,25.00,Gas\n";
        exit;
        break;
    
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
