<?php
/**
 * Backfill script for crypto_id values
 * This script maps crypto symbols to CoinGecko IDs for existing records
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

// Common crypto symbol to CoinGecko ID mappings
$symbolMappings = [
    'btc' => 'bitcoin',
    'eth' => 'ethereum',
    'ada' => 'cardano',
    'usdt' => 'tether',
    'bnb' => 'binancecoin',
    'xrp' => 'ripple',
    'sol' => 'solana',
    'dot' => 'polkadot',
    'doge' => 'dogecoin',
    'usdc' => 'usd-coin',
    'matic' => 'matic-network',
    'ltc' => 'litecoin',
    'link' => 'chainlink',
    'avax' => 'avalanche-2',
    'uni' => 'uniswap',
    'atom' => 'cosmos'
];

function backfillCryptoIds() {
    global $symbolMappings;
    $db = Database::getInstance();
    
    try {
        // Backfill portfolio
        $portfolioRows = $db->fetchAll(
            "SELECT id, crypto_symbol FROM crypto_portfolio WHERE crypto_id IS NULL OR crypto_id = ''"
        );
        
        foreach ($portfolioRows as $row) {
            $symbol = strtolower($row['crypto_symbol']);
            if (isset($symbolMappings[$symbol])) {
                $db->execute(
                    "UPDATE crypto_portfolio SET crypto_id = ? WHERE id = ?",
                    [$symbolMappings[$symbol], $row['id']]
                );
                echo "Updated portfolio ID {$row['id']}: {$symbol} -> {$symbolMappings[$symbol]}\n";
            } else {
                // Try to resolve via CoinGecko API
                $apiUrl = "https://api.coingecko.com/api/v3/search?query=" . urlencode($symbol);
                $response = @file_get_contents($apiUrl);
                if ($response) {
                    $data = json_decode($response, true);
                    if (!empty($data['coins'])) {
                        $cryptoId = $data['coins'][0]['id'];
                        $db->execute(
                            "UPDATE crypto_portfolio SET crypto_id = ? WHERE id = ?",
                            [$cryptoId, $row['id']]
                        );
                        echo "Resolved portfolio ID {$row['id']}: {$symbol} -> {$cryptoId}\n";
                    }
                }
                usleep(200000); // Rate limiting
            }
        }
        
        // Backfill alerts
        $alertRows = $db->fetchAll(
            "SELECT id, crypto_symbol FROM crypto_alerts WHERE crypto_id IS NULL OR crypto_id = ''"
        );
        
        foreach ($alertRows as $row) {
            $symbol = strtolower($row['crypto_symbol']);
            if (isset($symbolMappings[$symbol])) {
                $db->execute(
                    "UPDATE crypto_alerts SET crypto_id = ? WHERE id = ?",
                    [$symbolMappings[$symbol], $row['id']]
                );
                echo "Updated alert ID {$row['id']}: {$symbol} -> {$symbolMappings[$symbol]}\n";
            } else {
                // Try to resolve via CoinGecko API
                $apiUrl = "https://api.coingecko.com/api/v3/search?query=" . urlencode($symbol);
                $response = @file_get_contents($apiUrl);
                if ($response) {
                    $data = json_decode($response, true);
                    if (!empty($data['coins'])) {
                        $cryptoId = $data['coins'][0]['id'];
                        $db->execute(
                            "UPDATE crypto_alerts SET crypto_id = ? WHERE id = ?",
                            [$cryptoId, $row['id']]
                        );
                        echo "Resolved alert ID {$row['id']}: {$symbol} -> {$cryptoId}\n";
                    }
                }
                usleep(200000); // Rate limiting
            }
        }
        
        echo "\nBackfill complete!\n";
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}

// Run backfill if executed directly
if (php_sapi_name() === 'cli') {
    backfillCryptoIds();
}
?>
