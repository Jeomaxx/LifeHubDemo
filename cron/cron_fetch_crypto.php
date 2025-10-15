#!/usr/bin/env php
<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$db = Database::getInstance();

try {
    $portfolioCoins = $db->fetchAll("SELECT DISTINCT crypto_id, crypto_symbol FROM crypto_portfolio");
    $alertCoins = $db->fetchAll("SELECT DISTINCT crypto_id, crypto_symbol FROM crypto_alerts WHERE is_active = TRUE");
    
    $allCoins = array_merge($portfolioCoins, $alertCoins);
    
    if (empty($allCoins)) {
        echo "No coins to fetch\n";
        exit;
    }
    
    $uniqueIds = array_unique(array_column($allCoins, 'crypto_id'));
    
    if (empty($uniqueIds)) {
        echo "No valid coin IDs found\n";
        exit;
    }
    
    $ids = implode(',', $uniqueIds);
    
    $url = "https://api.coingecko.com/api/v3/simple/price?ids={$ids}&vs_currencies=usd&include_24hr_change=true";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Life-Atlas-Organizer/1.0');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200 || !$response) {
        echo "API request failed with code {$httpCode}\n";
        exit;
    }
    
    $prices = json_decode($response, true);
    
    if (!$prices) {
        echo "Failed to parse API response\n";
        exit;
    }
    
    $updatedCount = 0;
    
    foreach ($prices as $coinId => $data) {
        if (!isset($data['usd'])) continue;
        
        $priceUsd = $data['usd'];
        $change24h = $data['usd_24h_change'] ?? 0;
        
        $symbol = '';
        foreach ($allCoins as $coin) {
            if ($coin['crypto_id'] === $coinId) {
                $symbol = $coin['crypto_symbol'];
                break;
            }
        }
        
        $existing = $db->fetchOne("SELECT id FROM crypto_coins WHERE coingecko_id = ?", [$coinId]);
        
        if (!$existing) {
            $db->execute(
                "INSERT INTO crypto_coins (coingecko_id, symbol, name) VALUES (?, ?, ?)",
                [$coinId, $symbol, ucfirst($coinId)]
            );
            
            $coinDbId = $db->lastInsertId();
        } else {
            $coinDbId = $existing['id'];
        }
        
        $existingPrice = $db->fetchOne(
            "SELECT id FROM crypto_price_history WHERE coin_id = ? AND DATE(timestamp) = CURRENT_DATE ORDER BY timestamp DESC LIMIT 1",
            [$coinDbId]
        );
        
        if ($existingPrice) {
            $db->execute(
                "UPDATE crypto_price_history SET price_usd = ?, change_24h = ?, timestamp = CURRENT_TIMESTAMP WHERE id = ?",
                [$priceUsd, $change24h, $existingPrice['id']]
            );
        } else {
            $db->execute(
                "INSERT INTO crypto_price_history (coin_id, crypto_symbol, price_usd, change_24h) VALUES (?, ?, ?, ?)",
                [$coinDbId, $symbol, $priceUsd, $change24h]
            );
        }
        
        $updatedCount++;
        echo "Updated price for {$coinId}: \${$priceUsd} ({$change24h}%)\n";
    }
    
    echo "Successfully updated {$updatedCount} cryptocurrency prices at " . date('Y-m-d H:i:s') . "\n";
    
    checkAndTriggerAlerts($db, $prices);
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

function checkAndTriggerAlerts($db, $prices) {
    try {
        $alerts = $db->fetchAll(
            "SELECT * FROM crypto_alerts WHERE is_active = TRUE AND is_triggered = FALSE"
        );
        
        $triggeredCount = 0;
        
        foreach ($alerts as $alert) {
            if (!isset($prices[$alert['crypto_id']])) continue;
            
            $currentPrice = $prices[$alert['crypto_id']]['usd'];
            
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
                
                $user = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$alert['user_id']]);
                
                if ($user) {
                    $message = sprintf(
                        "🚨 Crypto Alert: %s has %s $%s (Current: $%s)",
                        strtoupper($alert['crypto_symbol']),
                        $alert['alert_type'] === 'above' ? 'risen above' : 'fallen below',
                        number_format($alert['target_price'], 2),
                        number_format($currentPrice, 2)
                    );
                    
                    $db->execute(
                        "INSERT INTO notifications (user_id, title, message, type, is_read) VALUES (?, ?, ?, ?, ?)",
                        [$user['id'], 'Crypto Price Alert', $message, 'crypto_alert', false]
                    );
                    
                    sendTelegramNotification($user, $message);
                    sendEmailNotification($user, 'Crypto Price Alert', $message);
                }
                
                $triggeredCount++;
                echo "Alert triggered for {$alert['crypto_symbol']}: {$alert['alert_type']} \${$alert['target_price']}\n";
            }
        }
        
        if ($triggeredCount > 0) {
            echo "Triggered {$triggeredCount} alerts\n";
        }
    } catch (Exception $e) {
        echo "Error checking alerts: " . $e->getMessage() . "\n";
    }
}

function sendTelegramNotification($user, $message) {
    if (empty(TELEGRAM_BOT_TOKEN) || empty($user['telegram_chat_id'])) {
        return false;
    }
    
    try {
        $url = TELEGRAM_API_URL . TELEGRAM_BOT_TOKEN . '/sendMessage';
        $data = [
            'chat_id' => $user['telegram_chat_id'],
            'text' => $message,
            'parse_mode' => 'HTML'
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return true;
    } catch (Exception $e) {
        return false;
    }
}

function sendEmailNotification($user, $subject, $message) {
    if (empty(SMTP_HOST) || empty($user['email'])) {
        return false;
    }
    
    try {
        $to = $user['email'];
        $headers = "From: " . SMTP_FROM_EMAIL . "\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        
        $body = "
        <html>
        <body>
            <h2>{$subject}</h2>
            <p>{$message}</p>
            <p>Regards,<br>Life Atlas Organizer</p>
        </body>
        </html>";
        
        return mail($to, $subject, $body, $headers);
    } catch (Exception $e) {
        return false;
    }
}
