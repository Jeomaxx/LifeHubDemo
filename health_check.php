<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

header('Content-Type: application/json');

$health = [
    'status' => 'healthy',
    'timestamp' => date('Y-m-d H:i:s'),
    'checks' => []
];

try {
    $db = Database::getInstance();
    $db->fetchOne("SELECT 1");
    $health['checks']['database'] = [
        'status' => 'ok',
        'message' => 'Database connection successful'
    ];
} catch (Exception $e) {
    $health['status'] = 'unhealthy';
    $health['checks']['database'] = [
        'status' => 'error',
        'message' => 'Database connection failed: ' . $e->getMessage()
    ];
}

if (is_writable(BASE_PATH . '/uploads')) {
    $health['checks']['filesystem'] = [
        'status' => 'ok',
        'message' => 'File system writable'
    ];
} else {
    $health['status'] = 'degraded';
    $health['checks']['filesystem'] = [
        'status' => 'warning',
        'message' => 'Upload directory not writable'
    ];
}

$requiredExtensions = ['pdo', 'pdo_pgsql', 'json', 'curl', 'mbstring'];
$missingExtensions = [];
foreach ($requiredExtensions as $ext) {
    if (!extension_loaded($ext)) {
        $missingExtensions[] = $ext;
    }
}

if (empty($missingExtensions)) {
    $health['checks']['php_extensions'] = [
        'status' => 'ok',
        'message' => 'All required PHP extensions loaded'
    ];
} else {
    $health['status'] = 'unhealthy';
    $health['checks']['php_extensions'] = [
        'status' => 'error',
        'message' => 'Missing extensions: ' . implode(', ', $missingExtensions)
    ];
}

$health['checks']['smtp'] = [
    'status' => !empty(SMTP_HOST) ? 'ok' : 'warning',
    'message' => !empty(SMTP_HOST) ? 'SMTP configured' : 'SMTP not configured'
];

$health['checks']['telegram'] = [
    'status' => !empty(TELEGRAM_BOT_TOKEN) ? 'ok' : 'warning',
    'message' => !empty(TELEGRAM_BOT_TOKEN) ? 'Telegram configured' : 'Telegram not configured'
];

$health['checks']['google_oauth'] = [
    'status' => (!empty(getenv('GOOGLE_CLIENT_ID')) && !empty(getenv('GOOGLE_CLIENT_SECRET'))) ? 'ok' : 'warning',
    'message' => (!empty(getenv('GOOGLE_CLIENT_ID')) && !empty(getenv('GOOGLE_CLIENT_SECRET'))) ? 'Google OAuth configured' : 'Google OAuth not configured'
];

$health['system_info'] = [
    'php_version' => phpversion(),
    'memory_limit' => ini_get('memory_limit'),
    'max_execution_time' => ini_get('max_execution_time'),
    'upload_max_filesize' => ini_get('upload_max_filesize')
];

http_response_code($health['status'] === 'healthy' ? 200 : ($health['status'] === 'degraded' ? 207 : 503));
echo json_encode($health, JSON_PRETTY_PRINT);
