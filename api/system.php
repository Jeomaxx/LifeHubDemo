<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user = $auth->getUser();
if (!$user['is_admin']) {
    echo json_encode(['success' => false, 'message' => 'Admin access required']);
    exit;
}

$db = Database::getInstance();
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'create_backup':
        try {
            $userId = $user['id'];
            $filename = generateBackup($userId);
            
            echo json_encode([
                'success' => true,
                'message' => 'Backup created successfully',
                'filename' => $filename
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Backup failed: ' . $e->getMessage()]);
        }
        break;
    
    case 'test_cron':
        try {
            $results = [];
            
            $cryptoCron = BASE_PATH . '/cron/cron_fetch_crypto.php';
            if (file_exists($cryptoCron)) {
                exec("php $cryptoCron 2>&1", $output, $returnCode);
                $results['crypto_fetch'] = [
                    'status' => $returnCode === 0 ? 'success' : 'failed',
                    'output' => implode("\n", $output)
                ];
            }
            
            $remindersCron = BASE_PATH . '/cron/reminders.php';
            if (file_exists($remindersCron)) {
                exec("php $remindersCron 2>&1", $output2, $returnCode2);
                $results['reminders'] = [
                    'status' => $returnCode2 === 0 ? 'success' : 'failed',
                    'output' => implode("\n", $output2)
                ];
            }
            
            $db->execute(
                "INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)",
                [$user['id'], 'Cron Test Results', json_encode($results), 'system']
            );
            
            echo json_encode([
                'success' => true,
                'message' => 'Cron jobs tested',
                'results' => $results
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Cron test failed: ' . $e->getMessage()]);
        }
        break;
    
    case 'test_email':
        try {
            if (empty(SMTP_HOST)) {
                throw new Exception('SMTP not configured');
            }
            
            $sent = sendEmail(
                $user['email'],
                'Test Email from Life Atlas',
                '<h2>Test Email</h2><p>If you received this email, your SMTP configuration is working correctly!</p><p>Time: ' . date('Y-m-d H:i:s') . '</p>'
            );
            
            if ($sent) {
                echo json_encode(['success' => true, 'message' => 'Test email sent to ' . $user['email']]);
            } else {
                throw new Exception('Failed to send email');
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;
    
    case 'test_telegram':
        try {
            if (empty(TELEGRAM_BOT_TOKEN)) {
                throw new Exception('Telegram bot token not configured');
            }
            
            if (empty($user['telegram_chat_id'])) {
                throw new Exception('User telegram_chat_id not set. Please set it in your profile first.');
            }
            
            $message = "🧪 Test Message from Life Atlas\n\nIf you received this message, your Telegram integration is working correctly!\n\nTime: " . date('Y-m-d H:i:s');
            
            $sent = sendTelegramMessage($user['telegram_chat_id'], $message);
            
            if ($sent) {
                echo json_encode(['success' => true, 'message' => 'Test message sent to Telegram']);
            } else {
                throw new Exception('Failed to send Telegram message');
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;
    
    case 'save_smtp_config':
        try {
            $configPath = BASE_PATH . '/includes/config.php';
            $configContent = file_get_contents($configPath);
            
            $updates = [
                'SMTP_HOST' => $_POST['smtp_host'] ?? '',
                'SMTP_PORT' => $_POST['smtp_port'] ?? '587',
                'SMTP_USERNAME' => $_POST['smtp_username'] ?? '',
                'SMTP_FROM_EMAIL' => $_POST['smtp_from_email'] ?? ''
            ];
            
            if (!empty($_POST['smtp_password'])) {
                $updates['SMTP_PASSWORD'] = $_POST['smtp_password'];
            }
            
            foreach ($updates as $key => $value) {
                $pattern = "/define\('" . $key . "',\s*'[^']*'\);/";
                $replacement = "define('" . $key . "', '" . addslashes($value) . "');";
                $configContent = preg_replace($pattern, $replacement, $configContent);
            }
            
            file_put_contents($configPath, $configContent);
            
            echo json_encode(['success' => true, 'message' => 'SMTP configuration saved']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to save configuration: ' . $e->getMessage()]);
        }
        break;
    
    case 'save_telegram_config':
        try {
            $configPath = BASE_PATH . '/includes/config.php';
            $configContent = file_get_contents($configPath);
            
            $botToken = $_POST['telegram_bot_token'] ?? '';
            
            $pattern = "/define\('TELEGRAM_BOT_TOKEN',\s*'[^']*'\);/";
            $replacement = "define('TELEGRAM_BOT_TOKEN', '" . addslashes($botToken) . "');";
            $configContent = preg_replace($pattern, $replacement, $configContent);
            
            file_put_contents($configPath, $configContent);
            
            echo json_encode(['success' => true, 'message' => 'Telegram configuration saved']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to save configuration: ' . $e->getMessage()]);
        }
        break;
    
    case 'clear_logs':
        try {
            $db->execute("DELETE FROM notifications WHERE type = 'system'");
            echo json_encode(['success' => true, 'message' => 'System logs cleared']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to clear logs']);
        }
        break;
    
    case 'get_system_stats':
        try {
            $stats = [
                'total_users' => $db->fetchColumn("SELECT COUNT(*) FROM users"),
                'total_tasks' => $db->fetchColumn("SELECT COUNT(*) FROM tasks"),
                'total_assets' => $db->fetchColumn("SELECT COUNT(*) FROM assets"),
                'total_finance_records' => $db->fetchColumn("SELECT COUNT(*) FROM finance"),
                'recent_activity' => $db->fetchAll(
                    "SELECT * FROM notifications WHERE type = 'system' ORDER BY created_at DESC LIMIT 10"
                )
            ];
            
            echo json_encode(['success' => true, 'stats' => $stats]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to get stats']);
        }
        break;
    
    case 'download_backup':
        try {
            $backupId = $_GET['id'] ?? 0;
            $backup = $db->fetchOne("SELECT * FROM backups WHERE id = ?", [$backupId]);
            
            if (!$backup) {
                throw new Exception('Backup not found');
            }
            
            $filepath = BACKUP_PATH . $backup['filename'];
            
            if (!file_exists($filepath)) {
                throw new Exception('Backup file not found');
            }
            
            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="' . $backup['filename'] . '"');
            header('Content-Length: ' . filesize($filepath));
            
            readfile($filepath);
            exit;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;
    
    case 'delete_backup':
        try {
            $backupId = $_GET['id'] ?? 0;
            $backup = $db->fetchOne("SELECT * FROM backups WHERE id = ?", [$backupId]);
            
            if (!$backup) {
                throw new Exception('Backup not found');
            }
            
            $filepath = BACKUP_PATH . $backup['filename'];
            if (file_exists($filepath)) {
                unlink($filepath);
            }
            
            $db->execute("DELETE FROM backups WHERE id = ?", [$backupId]);
            
            echo json_encode(['success' => true, 'message' => 'Backup deleted']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to delete backup']);
        }
        break;
    
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
?>
