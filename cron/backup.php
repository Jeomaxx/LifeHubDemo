<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (php_sapi_name() !== 'cli') {
    die('This script can only be run from the command line');
}

$db = Database::getInstance();

$users = $db->fetchAll("SELECT id FROM users WHERE is_admin = FALSE");

echo "Starting automatic backup process...\n";
echo "Found " . count($users) . " users to backup\n\n";

foreach ($users as $user) {
    $userId = $user['id'];
    
    try {
        $filename = generateBackup($userId);
        echo "✓ Backup created for user ID $userId: $filename\n";
    } catch (Exception $e) {
        echo "✗ Failed to backup user ID $userId: " . $e->getMessage() . "\n";
    }
}

$cutoffDate = date('Y-m-d H:i:s', strtotime('-' . BACKUP_RETENTION_DAYS . ' days'));
$db->query("DELETE FROM backups WHERE created_at < ?", [$cutoffDate]);

echo "\n✓ Old backups cleaned up (older than " . BACKUP_RETENTION_DAYS . " days)\n";
echo "Backup process completed!\n";
