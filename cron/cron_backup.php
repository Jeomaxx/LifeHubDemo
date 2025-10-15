#!/usr/bin/env php
<?php
require_once __DIR__ . '/../includes/config.php';

$backupDir = __DIR__ . '/../backups';
if (!file_exists($backupDir)) {
    mkdir($backupDir, 0755, true);
}

$filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
$filepath = $backupDir . '/' . $filename;

$command = sprintf(
    'PGPASSWORD=%s pg_dump -h %s -U %s -d %s > %s',
    escapeshellarg(getenv('PGPASSWORD')),
    escapeshellarg(getenv('PGHOST')),
    escapeshellarg(getenv('PGUSER')),
    escapeshellarg(getenv('PGDATABASE')),
    escapeshellarg($filepath)
);

exec($command, $output, $returnCode);

if ($returnCode === 0) {
    echo "Backup created successfully: {$filename}\n";
    
    $backups = glob($backupDir . '/backup_*.sql');
    rsort($backups);
    
    $maxBackups = 8;
    if (count($backups) > $maxBackups) {
        $toDelete = array_slice($backups, $maxBackups);
        foreach ($toDelete as $oldBackup) {
            unlink($oldBackup);
            echo "Deleted old backup: " . basename($oldBackup) . "\n";
        }
    }
    
    require_once __DIR__ . '/../includes/db.php';
    $db = Database::getInstance();
    $db->query(
        "INSERT INTO backups (filename, size_bytes, created_at) VALUES (?, ?, NOW())",
        [$filename, filesize($filepath)]
    );
    
    echo "Backup rotation completed. Kept last {$maxBackups} backups.\n";
} else {
    echo "Backup failed with code: {$returnCode}\n";
    exit(1);
}

echo "Backup completed at " . date('Y-m-d H:i:s') . "\n";
