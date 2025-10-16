<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/rate_limiter.php';
require_once '../includes/auth.php';
require_once '../includes/totp.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? $_GET['action'] ?? '';
$db = Database::getInstance();
$auth = new Auth();
$rateLimiter = new RateLimiter();

switch ($action) {
    case 'generate_totp_secret':
        if (!$auth->isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        $userId = $auth->getUserId();
        $user = $db->fetchOne("SELECT email, name FROM users WHERE id = ?", [$userId]);
        
        $secret = TOTP::generateSecret();
        $qrCodeUrl = TOTP::getQRCodeUrl($user['email'], $secret);
        
        $_SESSION['pending_totp_secret'] = $secret;
        
        echo json_encode([
            'success' => true,
            'secret' => $secret,
            'qr_code_url' => $qrCodeUrl
        ]);
        break;
    
    case 'verify_totp_setup':
        if (!$auth->isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        $code = $input['code'] ?? '';
        $secret = $input['secret'] ?? $_SESSION['pending_totp_secret'] ?? '';
        
        if (!$secret) {
            echo json_encode(['success' => false, 'message' => 'No pending 2FA setup']);
            exit;
        }
        
        if (TOTP::verifyCode($secret, $code)) {
            $userId = $auth->getUserId();
            $backupCodes = TOTP::generateBackupCodes();
            
            $hashedBackupCodes = array_map(function($code) {
                return password_hash($code, PASSWORD_BCRYPT);
            }, $backupCodes);
            
            $db->execute(
                "UPDATE users SET totp_secret = ?, totp_enabled = TRUE, backup_codes = ? WHERE id = ?",
                [$secret, json_encode($hashedBackupCodes), $userId]
            );
            
            unset($_SESSION['pending_totp_secret']);
            
            echo json_encode([
                'success' => true,
                'message' => '2FA enabled successfully',
                'backup_codes' => $backupCodes
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid verification code']);
        }
        break;
    
    case 'regenerate_backup_codes':
        if (!$auth->isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        $userId = $auth->getUserId();
        $backupCodes = TOTP::generateBackupCodes();
        
        $hashedBackupCodes = array_map(function($code) {
            return password_hash($code, PASSWORD_BCRYPT);
        }, $backupCodes);
        
        $db->execute(
            "UPDATE users SET backup_codes = ? WHERE id = ?",
            [json_encode($hashedBackupCodes), $userId]
        );
        
        echo json_encode([
            'success' => true,
            'backup_codes' => $backupCodes
        ]);
        break;
    
    
    case 'disable_2fa':
        if (!$auth->isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        $userId = $auth->getUserId();
        
        $db->execute("UPDATE users SET totp_secret = NULL, totp_enabled = FALSE, backup_codes = NULL WHERE id = ?", [$userId]);
        
        echo json_encode(['success' => true, 'message' => '2FA disabled successfully']);
        break;
    
    case 'verify_2fa':
        $data = json_decode(file_get_contents('php://input'), true);
        $userId = $data['user_id'] ?? 0;
        $code = $data['code'] ?? '';
        
        if (!$userId || !$code) {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit;
        }
        
        $user = $db->fetchOne("SELECT totp_secret, backup_codes FROM users WHERE id = ? AND totp_enabled = TRUE", [$userId]);
        
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'User not found or 2FA not enabled']);
            exit;
        }
        
        if (TOTP::verifyCode($user['totp_secret'], $code)) {
            $_SESSION['2fa_verified'] = true;
            $_SESSION['2fa_verified_at'] = time();
            echo json_encode(['success' => true]);
        } else {
            $backupCodesJson = $user['backup_codes'];
            if ($backupCodesJson) {
                $backupCodes = json_decode($backupCodesJson, true);
                if (is_array($backupCodes)) {
                    foreach ($backupCodes as $index => $hashedCode) {
                        if (password_verify($code, $hashedCode)) {
                            unset($backupCodes[$index]);
                            $db->execute(
                                "UPDATE users SET backup_codes = ? WHERE id = ?",
                                [json_encode(array_values($backupCodes)), $userId]
                            );
                            $_SESSION['2fa_verified'] = true;
                            $_SESSION['2fa_verified_at'] = time();
                            echo json_encode(['success' => true, 'backup_used' => true]);
                            exit;
                        }
                    }
                }
            }
            
            echo json_encode(['success' => false, 'message' => 'Invalid code']);
        }
        break;
    
    case 'generate_api_token':
        if (!$auth->isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $name = $data['name'] ?? 'API Token';
        $expiresInDays = $data['expires_in_days'] ?? 365;
        
        $userId = $auth->getUserId();
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime("+{$expiresInDays} days"));
        
        $db->execute(
            "INSERT INTO api_tokens (user_id, token, name, expires_at) VALUES (?, ?, ?, ?)",
            [$userId, $token, $name, $expiresAt]
        );
        
        echo json_encode([
            'success' => true,
            'token' => $token,
            'name' => $name,
            'expires_at' => $expiresAt
        ]);
        break;
    
    case 'list_api_tokens':
        if (!$auth->isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        $userId = $auth->getUserId();
        $tokens = $db->fetchAll(
            "SELECT id, name, LEFT(token, 8) || '...' as token_preview, last_used, expires_at, created_at 
             FROM api_tokens WHERE user_id = ? ORDER BY created_at DESC",
            [$userId]
        );
        
        echo json_encode(['success' => true, 'tokens' => $tokens]);
        break;
    
    case 'revoke_api_token':
        if (!$auth->isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        $tokenId = $_GET['id'] ?? 0;
        $userId = $auth->getUserId();
        
        $db->execute(
            "DELETE FROM api_tokens WHERE id = ? AND user_id = ?",
            [$tokenId, $userId]
        );
        
        echo json_encode(['success' => true]);
        break;
    
    case 'check_rate_limit':
        $ipAddress = $rateLimiter->getClientIP();
        $email = $_POST['email'] ?? null;
        
        $status = $rateLimiter->getRemainingAttempts($ipAddress, $email);
        echo json_encode(['success' => true, 'status' => $status]);
        break;
    
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
