<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/totp.php';
require_once 'includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();
$user = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$userId]);

$pageTitle = 'Two-Factor Authentication';
include 'includes/header.php';
?>

<div class="page-header">
    <h1><i class="fas fa-shield-alt"></i> Two-Factor Authentication (2FA)</h1>
    <p class="page-subtitle">Enhance your account security with 2FA</p>
</div>

<div class="dashboard-card">
    <div class="card-header">
        <h3><i class="fas fa-mobile-alt"></i> 2FA Status</h3>
    </div>
    <div class="card-body">
        <?php if ($user['totp_enabled']): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> Two-Factor Authentication is <strong>enabled</strong> on your account.
            </div>
            <button class="btn btn-danger" onclick="disable2FA()">
                <i class="fas fa-times"></i> Disable 2FA
            </button>
            <button class="btn btn-secondary" onclick="regenerateBackupCodes()">
                <i class="fas fa-sync"></i> Regenerate Backup Codes
            </button>
        <?php else: ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> Two-Factor Authentication is <strong>not enabled</strong> on your account.
            </div>
            <p>Protect your account by enabling Two-Factor Authentication. You'll need a TOTP app like Google Authenticator or Authy.</p>
            <button class="btn btn-primary" onclick="setup2FA()">
                <i class="fas fa-shield-alt"></i> Enable 2FA
            </button>
        <?php endif; ?>
    </div>
</div>

<div id="setup-2fa-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class="fas fa-qrcode"></i> Setup Two-Factor Authentication</h2>
            <span class="close" onclick="closeSetup2FAModal()">&times;</span>
        </div>
        <div class="modal-body" id="setup-2fa-content">
            <div class="setup-step" id="step1">
                <h3>Step 1: Scan QR Code</h3>
                <p>Scan this QR code with your authenticator app (Google Authenticator, Authy, etc.)</p>
                <div style="text-align: center; margin: 20px 0;">
                    <img id="qr-code" src="" alt="QR Code" style="max-width: 200px;">
                </div>
                <p>Or enter this secret key manually:</p>
                <div style="text-align: center; background: var(--bg-secondary); padding: 12px; border-radius: 8px; font-family: monospace; font-size: 18px; margin: 10px 0;">
                    <span id="secret-key"></span>
                </div>
            </div>
            
            <div class="setup-step" id="step2" style="display: none;">
                <h3>Step 2: Verify Code</h3>
                <p>Enter the 6-digit code from your authenticator app to verify:</p>
                <input type="text" id="verify-code" class="form-control" placeholder="000000" maxlength="6" pattern="[0-9]{6}" style="text-align: center; font-size: 24px; letter-spacing: 8px;">
                <div id="verify-error" class="alert alert-danger" style="display: none; margin-top: 10px;"></div>
            </div>
            
            <div class="setup-step" id="step3" style="display: none;">
                <h3>Step 3: Save Backup Codes</h3>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> Save these backup codes in a safe place. You can use them to access your account if you lose your authenticator device.
                </div>
                <div id="backup-codes-display" style="background: var(--bg-secondary); padding: 20px; border-radius: 8px; font-family: monospace;">
                </div>
                <button class="btn btn-secondary" onclick="copyBackupCodes()" style="margin-top: 10px;">
                    <i class="fas fa-copy"></i> Copy Codes
                </button>
            </div>
        </div>
        <div class="modal-footer">
            <button id="prev-btn" class="btn btn-secondary" onclick="previousStep()" style="display: none;">
                <i class="fas fa-arrow-left"></i> Previous
            </button>
            <button id="next-btn" class="btn btn-primary" onclick="nextStep()">
                Next <i class="fas fa-arrow-right"></i>
            </button>
            <button id="finish-btn" class="btn btn-success" onclick="finish2FASetup()" style="display: none;">
                <i class="fas fa-check"></i> Finish Setup
            </button>
        </div>
    </div>
</div>

<script>
let currentStep = 1;
let totpSecret = '';
let backupCodes = [];

function setup2FA() {
    fetch('/api/auth.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'generate_totp_secret' })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            totpSecret = data.secret;
            document.getElementById('qr-code').src = data.qr_code_url;
            document.getElementById('secret-key').textContent = data.secret;
            document.getElementById('setup-2fa-modal').style.display = 'block';
            currentStep = 1;
            showStep(1);
        } else {
            showToast(data.message || 'Failed to generate 2FA secret', 'error');
        }
    })
    .catch(error => {
        showToast('Failed to setup 2FA', 'error');
    });
}

function nextStep() {
    if (currentStep === 1) {
        currentStep = 2;
        showStep(2);
    } else if (currentStep === 2) {
        const code = document.getElementById('verify-code').value;
        if (code.length !== 6) {
            document.getElementById('verify-error').textContent = 'Please enter a 6-digit code';
            document.getElementById('verify-error').style.display = 'block';
            return;
        }
        
        fetch('/api/auth.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                action: 'verify_totp_setup',
                secret: totpSecret,
                code: code
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                backupCodes = data.backup_codes;
                displayBackupCodes(backupCodes);
                currentStep = 3;
                showStep(3);
            } else {
                document.getElementById('verify-error').textContent = data.message || 'Invalid code. Please try again.';
                document.getElementById('verify-error').style.display = 'block';
            }
        })
        .catch(error => {
            document.getElementById('verify-error').textContent = 'Verification failed. Please try again.';
            document.getElementById('verify-error').style.display = 'block';
        });
    }
}

function previousStep() {
    if (currentStep > 1) {
        currentStep--;
        showStep(currentStep);
    }
}

function showStep(step) {
    document.querySelectorAll('.setup-step').forEach(el => el.style.display = 'none');
    document.getElementById('step' + step).style.display = 'block';
    
    document.getElementById('prev-btn').style.display = step > 1 ? 'inline-block' : 'none';
    document.getElementById('next-btn').style.display = step < 3 ? 'inline-block' : 'none';
    document.getElementById('finish-btn').style.display = step === 3 ? 'inline-block' : 'none';
    
    if (step === 2) {
        document.getElementById('verify-code').value = '';
        document.getElementById('verify-error').style.display = 'none';
    }
}

function displayBackupCodes(codes) {
    const html = codes.map(code => `<div style="padding: 5px;">${code}</div>`).join('');
    document.getElementById('backup-codes-display').innerHTML = html;
}

function copyBackupCodes() {
    const text = backupCodes.join('\n');
    navigator.clipboard.writeText(text).then(() => {
        showToast('Backup codes copied to clipboard', 'success');
    });
}

function finish2FASetup() {
    closeSetup2FAModal();
    location.reload();
}

function closeSetup2FAModal() {
    document.getElementById('setup-2fa-modal').style.display = 'none';
}

function disable2FA() {
    if (!confirm('Are you sure you want to disable Two-Factor Authentication? This will make your account less secure.')) {
        return;
    }
    
    fetch('/api/auth.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'disable_2fa' })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('2FA disabled successfully', 'success');
            location.reload();
        } else {
            showToast(data.message || 'Failed to disable 2FA', 'error');
        }
    });
}

function regenerateBackupCodes() {
    if (!confirm('This will invalidate your current backup codes. Are you sure?')) {
        return;
    }
    
    fetch('/api/auth.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'regenerate_backup_codes' })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('New Backup Codes:\n\n' + data.backup_codes.join('\n') + '\n\nPlease save these codes in a safe place.');
        } else {
            showToast(data.message || 'Failed to regenerate backup codes', 'error');
        }
    });
}

window.onclick = function(event) {
    if (event.target == document.getElementById('setup-2fa-modal')) {
        closeSetup2FAModal();
    }
}
</script>

<?php include 'includes/footer.php'; ?>
