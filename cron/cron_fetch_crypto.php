#!/usr/bin/env php
<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

$db = Database::getInstance();

$coins = $db->query("SELECT DISTINCT coingecko_id FROM crypto_coins");

if (empty($coins)) {
    echo "No coins to fetch\n";
    exit;
}

$ids = implode(',', array_column($coins, 'coingecko_id'));

$url = "https://api.coingecko.com/api/v3/simple/price?ids={$ids}&vs_currencies=usd&include_24hr_change=true";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_USERAGENT, 'Life-Atlas-Organizer/1.0');

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    echo "API request failed with code {$httpCode}\n";
    exit;
}

$prices = json_decode($response, true);

if (!$prices) {
    echo "Failed to parse API response\n";
    exit;
}

foreach ($prices as $coinId => $data) {
    $coin = $db->queryOne("SELECT id FROM crypto_coins WHERE coingecko_id = ?", [$coinId]);
    
    if ($coin) {
        $db->query(
            "INSERT INTO crypto_price_history (coin_id, price_usd, change_24h, timestamp) 
             VALUES (?, ?, ?, NOW())
             ON CONFLICT (coin_id, timestamp) DO UPDATE SET price_usd = ?, change_24h = ?",
            [
                $coin['id'],
                $data['usd'],
                $data['usd_24h_change'] ?? 0,
                $data['usd'],
                $data['usd_24h_change'] ?? 0
            ]
        );
        
        echo "Updated price for {$coinId}: \${$data['usd']}\n";
    }
}

echo "Price update completed at " . date('Y-m-d H:i:s') . "\n";
