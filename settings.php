<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/totp.php';

requireLogin();

$db = Database::getInstance();
$user_id = $_SESSION['user_id'];

$user = $db->queryOne("SELECT * FROM users WHERE id = ?", [$user_id]);
$page_title = 'Settings';

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        
        if (empty($name) || empty($email)) {
            $error_message = 'Name and email are required';
        } else {
            $db->query(
                "UPDATE users SET name = ?, email = ?, updated_at = NOW() WHERE id = ?",
                [$name, $email, $user_id]
            );
            $success_message = 'Profile updated successfully';
            $user['name'] = $name;
            $user['email'] = $email;
        }
    }
    
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (!password_verify($current_password, $user['password'])) {
            $error_message = 'Current password is incorrect';
        } elseif ($new_password !== $confirm_password) {
            $error_message = 'New passwords do not match';
        } elseif (strlen($new_password) < 8) {
            $error_message = 'Password must be at least 8 characters';
        } else {
            $hashed = password_hash($new_password, PASSWORD_BCRYPT);
            $db->query("UPDATE users SET password = ? WHERE id = ?", [$hashed, $user_id]);
            $success_message = 'Password changed successfully';
        }
    }
    
    if (isset($_POST['enable_2fa'])) {
        $secret = TOTP::generateSecret();
        $backup_codes = TOTP::generateBackupCodes();
        
        $db->query(
            "UPDATE users SET totp_secret = ?, totp_backup_codes = ? WHERE id = ?",
            [$secret, json_encode($backup_codes), $user_id]
        );
        
        $user['totp_secret'] = $secret;
        $user['totp_backup_codes'] = json_encode($backup_codes);
        $success_message = 'Two-factor authentication enabled';
    }
    
    if (isset($_POST['verify_2fa'])) {
        $code = $_POST['totp_code'];
        
        if (TOTP::verifyCode($user['totp_secret'], $code)) {
            $db->query("UPDATE users SET totp_enabled = 1 WHERE id = ?", [$user_id]);
            $user['totp_enabled'] = 1;
            $success_message = 'Two-factor authentication verified and activated';
        } else {
            $error_message = 'Invalid verification code';
        }
    }
    
    if (isset($_POST['disable_2fa'])) {
        $db->query(
            "UPDATE users SET totp_enabled = 0, totp_secret = NULL, totp_backup_codes = NULL WHERE id = ?",
            [$user_id]
        );
        $user['totp_enabled'] = 0;
        $user['totp_secret'] = null;
        $success_message = 'Two-factor authentication disabled';
    }
    
    if (isset($_POST['update_telegram'])) {
        $telegram_chat_id = trim($_POST['telegram_chat_id']);
        $db->query("UPDATE users SET telegram_chat_id = ? WHERE id = ?", [$telegram_chat_id, $user_id]);
        $user['telegram_chat_id'] = $telegram_chat_id;
        $success_message = 'Telegram settings updated';
    }
}

include 'includes/header.php';
?>

<div class="content-header">
    <h1><i class="fas fa-cog"></i> Settings</h1>
</div>

<?php if ($success_message): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
<?php endif; ?>

<?php if ($error_message): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
<?php endif; ?>

<div class="settings-grid">
    <div class="card">
        <div class="card-header">
            <h3>Profile Settings</h3>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="name" class="form-control" 
                           value="<?= htmlspecialchars($user['name']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" 
                           value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>
                <button type="submit" name="update_profile" class="btn btn-primary">
                    Update Profile
                </button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Change Password</h3>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" name="current_password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>
                <button type="submit" name="change_password" class="btn btn-primary">
                    Change Password
                </button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Two-Factor Authentication</h3>
        </div>
        <div class="card-body">
            <?php if (!$user['totp_secret']): ?>
                <p>Enhance your account security with two-factor authentication.</p>
                <form method="POST">
                    <button type="submit" name="enable_2fa" class="btn btn-primary">
                        <i class="fas fa-shield-alt"></i> Enable 2FA
                    </button>
                </form>
            <?php elseif (!$user['totp_enabled']): ?>
                <div class="totp-setup">
                    <p><strong>Scan this QR code with Google Authenticator:</strong></p>
                    <img src="<?= TOTP::getQRCodeUrl($user['email'], $user['totp_secret']) ?>" 
                         alt="QR Code" class="qr-code">
                    <p><strong>Or enter this secret manually:</strong></p>
                    <code class="totp-secret"><?= htmlspecialchars($user['totp_secret']) ?></code>
                    
                    <p class="mt-3"><strong>Backup Codes (save these securely):</strong></p>
                    <div class="backup-codes">
                        <?php 
                        $backup_codes = json_decode($user['totp_backup_codes'], true);
                        foreach ($backup_codes as $code): 
                        ?>
                            <code><?= htmlspecialchars($code) ?></code>
                        <?php endforeach; ?>
                    </div>
                    
                    <form method="POST" class="mt-3">
                        <div class="form-group">
                            <label>Enter verification code:</label>
                            <input type="text" name="totp_code" class="form-control" 
                                   pattern="[0-9]{6}" maxlength="6" required>
                        </div>
                        <button type="submit" name="verify_2fa" class="btn btn-success">
                            Verify & Activate
                        </button>
                    </form>
                </div>
            <?php else: ?>
                <p class="text-success">
                    <i class="fas fa-check-circle"></i> Two-factor authentication is enabled
                </p>
                <form method="POST">
                    <button type="submit" name="disable_2fa" class="btn btn-danger">
                        <i class="fas fa-times"></i> Disable 2FA
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Telegram Notifications</h3>
        </div>
        <div class="card-body">
            <p>Get instant notifications on Telegram for alerts and reminders.</p>
            <form method="POST">
                <div class="form-group">
                    <label>Telegram Chat ID</label>
                    <input type="text" name="telegram_chat_id" class="form-control" 
                           value="<?= htmlspecialchars($user['telegram_chat_id'] ?? '') ?>" 
                           placeholder="Enter your Telegram chat ID">
                    <small class="form-text">
                        To get your chat ID, message @userinfobot on Telegram
                    </small>
                </div>
                <button type="submit" name="update_telegram" class="btn btn-primary">
                    Update Telegram Settings
                </button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Theme Preferences</h3>
        </div>
        <div class="card-body">
            <div class="theme-toggle">
                <label>
                    <input type="radio" name="theme" value="light" onchange="setTheme('light')">
                    <span><i class="fas fa-sun"></i> Light Mode</span>
                </label>
                <label>
                    <input type="radio" name="theme" value="dark" onchange="setTheme('dark')">
                    <span><i class="fas fa-moon"></i> Dark Mode</span>
                </label>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Danger Zone</h3>
        </div>
        <div class="card-body">
            <p class="text-danger">
                <i class="fas fa-exclamation-triangle"></i> 
                Destructive actions that cannot be undone
            </p>
            <button onclick="deleteAccount()" class="btn btn-danger">
                <i class="fas fa-user-times"></i> Delete Account
            </button>
        </div>
    </div>
</div>

<style>
.settings-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 20px;
}

.qr-code {
    margin: 20px 0;
    border: 2px solid #ddd;
    padding: 10px;
    background: white;
}

.totp-secret {
    display: block;
    padding: 10px;
    background: #f5f5f5;
    font-size: 18px;
    letter-spacing: 2px;
    margin: 10px 0;
}

.backup-codes {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
    margin-top: 10px;
}

.backup-codes code {
    padding: 8px;
    background: #f5f5f5;
    border: 1px solid #ddd;
    text-align: center;
}

.theme-toggle {
    display: flex;
    gap: 20px;
}

.theme-toggle label {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 15px 20px;
    border: 2px solid #ddd;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s;
}

.theme-toggle label:hover {
    border-color: var(--primary-color);
    background: var(--primary-light);
}

.theme-toggle input[type="radio"] {
    margin: 0;
}

@media (max-width: 768px) {
    .settings-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
function setTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('theme', theme);
    showToast('Theme updated to ' + theme + ' mode', 'success');
}

const savedTheme = localStorage.getItem('theme') || 'light';
document.documentElement.setAttribute('data-theme', savedTheme);
document.querySelector(`input[value="${savedTheme}"]`).checked = true;

function deleteAccount() {
    if (!confirm('Are you absolutely sure you want to delete your account? This action cannot be undone and all your data will be permanently deleted.')) {
        return;
    }
    
    const password = prompt('Enter your password to confirm account deletion:');
    if (!password) return;
    
    fetch('/api/user.php', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ password: password })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Your account has been deleted. You will be logged out.');
            window.location.href = '/logout.php';
        } else {
            alert(data.error || 'Failed to delete account');
        }
    });
}
</script>

<?php include 'includes/footer.php'; ?>
