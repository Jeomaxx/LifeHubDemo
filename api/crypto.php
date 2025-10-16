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
    case 'add':
        $data = json_decode(file_get_contents('php://input'), true);
        
        try {
            $db->execute(
                "INSERT INTO crypto_portfolio (user_id, crypto_id, crypto_symbol, crypto_name, amount, purchase_price, purchase_date, notes) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $userId,
                    $data['crypto_id'],
                    $data['crypto_symbol'],
                    $data['crypto_name'],
                    $data['amount'],
                    $data['purchase_price'],
                    $data['purchase_date'],
                    $data['notes'] ?? null
                ]
            );
            
            echo json_encode(['success' => true, 'message' => 'Cryptocurrency added successfully']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to add cryptocurrency: ' . $e->getMessage()]);
        }
        break;
    
    case 'delete':
        $id = $_GET['id'] ?? 0;
        
        try {
            $db->execute("DELETE FROM crypto_portfolio WHERE id = ? AND user_id = ?", [$id, $userId]);
            echo json_encode(['success' => true, 'message' => 'Cryptocurrency removed successfully']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to remove cryptocurrency']);
        }
        break;
    
    case 'create_alert':
        $data = json_decode(file_get_contents('php://input'), true);
        
        try {
            $db->execute(
                "INSERT INTO crypto_alerts (user_id, crypto_id, crypto_symbol, alert_type, target_price) 
                VALUES (?, ?, ?, ?, ?)",
                [
                    $userId,
                    $data['crypto_id'],
                    $data['crypto_symbol'],
                    $data['alert_type'],
                    $data['target_price']
                ]
            );
            
            echo json_encode(['success' => true, 'message' => 'Alert created successfully']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to create alert: ' . $e->getMessage()]);
        }
        break;
    
    case 'delete_alert':
        $id = $_GET['id'] ?? 0;
        
        try {
            $db->execute("DELETE FROM crypto_alerts WHERE id = ? AND user_id = ?", [$id, $userId]);
            echo json_encode(['success' => true, 'message' => 'Alert deleted successfully']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to delete alert']);
        }
        break;
    
    case 'check_alerts':
        try {
            $alerts = $db->fetchAll(
                "SELECT * FROM crypto_alerts WHERE user_id = ? AND is_active = TRUE AND is_triggered = FALSE",
                [$userId]
            );
            
            $triggeredAlerts = [];
            
            foreach ($alerts as $alert) {
                $url = "https://api.coingecko.com/api/v3/simple/price?ids={$alert['crypto_id']}&vs_currencies=usd";
                $response = @file_get_contents($url);
                
                if ($response) {
                    $priceData = json_decode($response, true);
                    $currentPrice = $priceData[$alert['crypto_id']]['usd'] ?? null;
                    
                    if ($currentPrice) {
                        $db->execute(
                            "UPDATE crypto_alerts SET current_price = ? WHERE id = ?",
                            [$currentPrice, $alert['id']]
                        );
                        
                        $shouldTrigger = false;
                        
                        if ($alert['alert_type'] === 'above' && $currentPrice >= $alert['target_price']) {
                            $shouldTrigger = true;
                        } elseif ($alert['alert_type'] === 'below' && $currentPrice <= $alert['target_price']) {
                            $shouldTrigger = true;
                        }
                        
                        if ($shouldTrigger) {
                            $db->execute(
                                "UPDATE crypto_alerts SET is_triggered = TRUE, triggered_at = CURRENT_TIMESTAMP WHERE id = ?",
                                [$alert['id']]
                            );
                            
                            $triggeredAlerts[] = [
                                'symbol' => $alert['crypto_symbol'],
                                'type' => $alert['alert_type'],
                                'target' => $alert['target_price'],
                                'current' => $currentPrice
                            ];
                        }
                    }
                }
            }
            
            echo json_encode(['success' => true, 'triggered' => $triggeredAlerts]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to check alerts']);
        }
        break;
    
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
            
            while (($row = fgetcsv($handle)) !== false) {
                try {
                    if (count($row) < 6) continue;
                    
                    $cryptoSymbol = strtolower(trim($row[0]));
                    $cryptoName = trim($row[1]);
                    $amount = floatval($row[2]);
                    $purchasePrice = floatval($row[3]);
                    $purchaseDate = $row[4];
                    $notes = $row[5] ?? '';
                    
                    $apiUrl = "https://api.coingecko.com/api/v3/search?query=" . urlencode($cryptoSymbol);
                    $response = @file_get_contents($apiUrl);
                    
                    if ($response) {
                        $searchData = json_decode($response, true);
                        if (!empty($searchData['coins'])) {
                            $firstMatch = $searchData['coins'][0];
                            $cryptoId = $firstMatch['id'];
                            $cryptoName = $firstMatch['name'];
                        } else {
                            $cryptoId = $cryptoSymbol;
                        }
                    } else {
                        $cryptoId = $cryptoSymbol;
                    }
                    
                    if ($amount > 0 && $purchasePrice > 0) {
                        $db->execute(
                            "INSERT INTO crypto_portfolio (user_id, crypto_id, crypto_symbol, crypto_name, amount, purchase_price, purchase_date, notes) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                            [$userId, $cryptoId, $cryptoSymbol, $cryptoName, $amount, $purchasePrice, $purchaseDate, $notes]
                        );
                        $imported++;
                    }
                    
                    usleep(200000);
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
            echo json_encode(['success' => false, 'message' => 'Import failed: ' . $e->getMessage()]);
        }
        break;
    
    case 'get_history':
        try {
            $symbol = $_GET['symbol'] ?? '';
            $days = (int)($_GET['days'] ?? 30);
            // Validate and cap the days value for safety
            if ($days < 1) $days = 1;
            if ($days > 365) $days = 365;
            
            $history = $db->fetchAll(
                "SELECT DATE(timestamp) as date, AVG(price_usd) as price, AVG(change_24h) as change 
                FROM crypto_price_history 
                WHERE crypto_symbol = ? AND timestamp >= CURRENT_DATE - INTERVAL '1 day' * ?
                GROUP BY DATE(timestamp)
                ORDER BY date ASC",
                [$symbol, $days]
            );
            
            echo json_encode(['success' => true, 'data' => $history]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to get history']);
        }
        break;
    
    case 'get_portfolio_stats':
        try {
            $stats = [
                'total_holdings' => 0,
                'total_invested' => 0,
                'total_pnl' => 0,
                'pnl_percentage' => 0,
                'best_performer' => null,
                'worst_performer' => null,
                'distribution' => []
            ];
            
            $portfolio = $db->fetchAll(
                "SELECT * FROM crypto_portfolio WHERE user_id = ?",
                [$userId]
            );
            
            echo json_encode(['success' => true, 'stats' => $stats, 'portfolio' => $portfolio]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to get stats']);
        }
        break;
    
    case 'export':
        try {
            $portfolio = $db->fetchAll(
                "SELECT crypto_symbol, crypto_name, amount, purchase_price, purchase_date, notes 
                FROM crypto_portfolio WHERE user_id = ? ORDER BY created_at DESC",
                [$userId]
            );
            
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="crypto_portfolio_' . date('Y-m-d') . '.csv"');
            
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Symbol', 'Name', 'Amount', 'Purchase Price', 'Purchase Date', 'Notes']);
            
            foreach ($portfolio as $item) {
                fputcsv($output, [
                    $item['crypto_symbol'],
                    $item['crypto_name'],
                    $item['amount'],
                    $item['purchase_price'],
                    $item['purchase_date'],
                    $item['notes']
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
?>
