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
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'get_rates':
            $rates = getExchangeRates();
            echo json_encode(['success' => true, 'rates' => $rates]);
            break;
            
        case 'convert':
            $amount = $_POST['amount'] ?? 0;
            $from = $_POST['from'] ?? 'USD';
            $to = $_POST['to'] ?? 'EUR';
            
            $result = convertCurrency($amount, $from, $to);
            echo json_encode(['success' => true, 'result' => $result]);
            break;
            
        case 'get_user_currency':
            $user = $db->fetchOne("SELECT currency_preference FROM users WHERE id = ?", [$userId]);
            echo json_encode(['success' => true, 'currency' => $user['currency_preference'] ?? 'USD']);
            break;
            
        case 'set_user_currency':
            $currency = $_POST['currency'] ?? 'USD';
            $db->execute("UPDATE users SET currency_preference = ? WHERE id = ?", [$currency, $userId]);
            echo json_encode(['success' => true, 'message' => 'Currency preference updated']);
            break;
            
        case 'get_multi_currency_balance':
            $balances = getMultiCurrencyBalance($userId, $db);
            echo json_encode(['success' => true, 'balances' => $balances]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    error_log('Currency API error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

// Get Exchange Rates from Free API
function getExchangeRates() {
    $cacheFile = sys_get_temp_dir() . '/exchange_rates_cache.json';
    $cacheExpiry = 3600; // 1 hour
    
    // Check cache
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheExpiry) {
        return json_decode(file_get_contents($cacheFile), true);
    }
    
    // Fetch from API (using exchangerate-api.com free tier)
    $url = 'https://api.exchangerate-api.com/v4/latest/USD';
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        $rates = $data['rates'] ?? [];
        
        // Cache the results
        file_put_contents($cacheFile, json_encode($rates));
        
        return $rates;
    }
    
    // Fallback rates if API fails
    return [
        'USD' => 1.0,
        'EUR' => 0.92,
        'GBP' => 0.79,
        'JPY' => 149.50,
        'CAD' => 1.36,
        'AUD' => 1.52,
        'CHF' => 0.88,
        'CNY' => 7.24,
        'INR' => 83.12
    ];
}

// Convert Currency
function convertCurrency($amount, $from, $to) {
    $rates = getExchangeRates();
    
    if (!isset($rates[$from]) || !isset($rates[$to])) {
        throw new Exception('Invalid currency code');
    }
    
    // Convert to USD first, then to target currency
    $usdAmount = $amount / $rates[$from];
    $result = $usdAmount * $rates[$to];
    
    return round($result, 2);
}

// Get Multi-Currency Balance
function getMultiCurrencyBalance($userId, $db) {
    $accounts = $db->fetchAll(
        "SELECT currency, SUM(balance) as total_balance FROM accounts WHERE user_id = ? GROUP BY currency",
        [$userId]
    ) ?: [];
    
    $balances = [];
    $rates = getExchangeRates();
    
    foreach ($accounts as $account) {
        $currency = $account['currency'];
        $balance = $account['total_balance'];
        
        $balances[] = [
            'currency' => $currency,
            'balance' => $balance,
            'usd_equivalent' => convertCurrency($balance, $currency, 'USD')
        ];
    }
    
    return $balances;
}
