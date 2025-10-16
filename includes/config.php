<?php
// Configuration file for Life Atlas Organizer

// Database Configuration (PostgreSQL)
define('DB_HOST', getenv('PGHOST') ?: 'localhost');
define('DB_PORT', getenv('PGPORT') ?: '5432');
define('DB_NAME', getenv('PGDATABASE') ?: 'life_atlas');
define('DB_USER', getenv('PGUSER') ?: 'postgres');
define('DB_PASS', getenv('PGPASSWORD') ?: '');

// Application Settings
define('SITE_NAME', 'Life Atlas Organizer');
define('SITE_URL', 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
define('BASE_PATH', dirname(__DIR__));

// Security
define('SESSION_LIFETIME', 86400); // 24 hours
define('CSRF_TOKEN_EXPIRY', 3600); // 1 hour

// Email Configuration (SMTP)
define('SMTP_HOST', '');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', '');
define('SMTP_PASSWORD', '');
define('SMTP_FROM_EMAIL', '');
define('SMTP_FROM_NAME', SITE_NAME);

// Telegram Configuration
define('TELEGRAM_BOT_TOKEN', '');
define('TELEGRAM_API_URL', 'https://api.telegram.org/bot');

// Backup Settings
define('BACKUP_PATH', BASE_PATH . '/uploads/backups/');
define('AUTO_BACKUP_ENABLED', true);
define('BACKUP_RETENTION_DAYS', 30);

// Timezone
date_default_timezone_set('UTC');

// Error Reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
