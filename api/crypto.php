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
                                "UPDATE crypto_alerts SET is_triggered = TRUE, triggered_at = NOW() WHERE id = ?",
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
    
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
?>
