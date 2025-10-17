<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/ai_config.php';

header('Content-Type: application/json');

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = $auth->getUserId();
$db = Database::getInstance();
$ai = AIConfig::getInstance();
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'generate':
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $range = $data['range'] ?? '1 month';
            $scenario = $data['scenario'] ?? 'realistic';
            
            $transactions = $db->fetchAll(
                "SELECT * FROM transactions WHERE user_id = ? ORDER BY date DESC LIMIT 50",
                [$userId]
            );
            
            $bills = $db->fetchAll(
                "SELECT * FROM bills WHERE user_id = ? AND due_date >= CURRENT_DATE ORDER BY due_date LIMIT 20",
                [$userId]
            );
            
            $aiResponse = $ai->predictFinancial($transactions, $bills, $range);
            
            $forecastData = json_decode($aiResponse, true);
            if (!$forecastData) {
                $forecastData = [
                    'predicted_balance' => 0,
                    'predicted_income' => 0,
                    'predicted_expenses' => 0,
                    'risks' => 'Unable to generate forecast',
                    'recommendations' => 'Please add more transaction data'
                ];
            }
            
            $forecastDate = date('Y-m-d', strtotime("+$range"));
            
            $db->execute(
                "INSERT INTO financial_forecasts (user_id, forecast_date, predicted_balance, predicted_income, predicted_expenses, confidence_level, scenario_type, risks, recommendations) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $userId,
                    $forecastDate,
                    $forecastData['predicted_balance'] ?? 0,
                    $forecastData['predicted_income'] ?? 0,
                    $forecastData['predicted_expenses'] ?? 0,
                    $scenario === 'optimistic' ? 'high' : ($scenario === 'pessimistic' ? 'low' : 'medium'),
                    $scenario,
                    $forecastData['risks'] ?? '',
                    $forecastData['recommendations'] ?? ''
                ]
            );
            
            echo json_encode(['success' => true, 'forecast' => $forecastData]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to generate forecast: ' . $e->getMessage()]);
        }
        break;
    
    case 'get_forecasts':
        try {
            $forecasts = $db->fetchAll(
                "SELECT * FROM financial_forecasts WHERE user_id = ? ORDER BY created_at DESC LIMIT 10",
                [$userId]
            );
            
            echo json_encode(['success' => true, 'forecasts' => $forecasts]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to get forecasts']);
        }
        break;
    
    case 'export':
        try {
            $forecasts = $db->fetchAll(
                "SELECT * FROM financial_forecasts WHERE user_id = ? ORDER BY forecast_date DESC",
                [$userId]
            );
            
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="financial_forecast_' . date('Y-m-d') . '.csv"');
            
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Date', 'Predicted Balance', 'Predicted Income', 'Predicted Expenses', 'Scenario', 'Risks', 'Recommendations']);
            
            foreach ($forecasts as $forecast) {
                fputcsv($output, [
                    $forecast['forecast_date'],
                    $forecast['predicted_balance'],
                    $forecast['predicted_income'],
                    $forecast['predicted_expenses'],
                    $forecast['scenario_type'],
                    $forecast['risks'],
                    $forecast['recommendations']
                ]);
            }
            
            fclose($output);
            exit;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Export failed']);
        }
        break;
    
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
