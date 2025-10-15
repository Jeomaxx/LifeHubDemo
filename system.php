<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$user = $auth->getUser();
if (!$user['is_admin']) {
    header('Location: dashboard.php');
    exit;
}

$db = Database::getInstance();

$systemStats = [
    'php_version' => phpversion(),
    'db_type' => 'PostgreSQL',
    'total_users' => $db->fetchColumn("SELECT COUNT(*) FROM users"),
    'disk_usage' => disk_free_space('/') ? round((disk_total_space('/') - disk_free_space('/')) / 1024 / 1024 / 1024, 2) : 'N/A',
    'disk_total' => disk_total_space('/') ? round(disk_total_space('/') / 1024 / 1024 / 1024, 2) : 'N/A',
    'memory_usage' => round(memory_get_usage() / 1024 / 1024, 2),
    'server_time' => date('Y-m-d H:i:s'),
    'uptime' => sys_getloadavg() ? sys_getloadavg()[0] : 'N/A'
];

$recentBackups = $db->fetchAll("SELECT * FROM backups ORDER BY created_at DESC LIMIT 10");
$recentLogs = $db->fetchAll("SELECT * FROM notifications WHERE type = 'system' ORDER BY created_at DESC LIMIT 20");

$pageTitle = 'System Management';
include 'includes/header.php';
?>

<div class="page-header">
    <h1><i class="fas fa-cog"></i> System Management</h1>
    <p class="page-subtitle">Monitor and manage your Life Atlas system</p>
</div>

<div class="admin-tabs">
    <button class="tab-btn active" onclick="switchTab('diagnostics')">
        <i class="fas fa-chart-line"></i> System Diagnostics
    </button>
    <button class="tab-btn" onclick="switchTab('backups')">
        <i class="fas fa-database"></i> Backups
    </button>
    <button class="tab-btn" onclick="switchTab('cron')">
        <i class="fas fa-clock"></i> Cron Jobs
    </button>
    <button class="tab-btn" onclick="switchTab('config')">
        <i class="fas fa-sliders-h"></i> Configuration
    </button>
    <button class="tab-btn" onclick="switchTab('logs')">
        <i class="fas fa-file-alt"></i> System Logs
    </button>
</div>

<div id="diagnostics-tab" class="tab-content active">
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon bg-blue">
                <i class="fab fa-php"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $systemStats['php_version']; ?></h3>
                <p>PHP Version</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon bg-green">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $systemStats['total_users']; ?></h3>
                <p>Total Users</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon bg-orange">
                <i class="fas fa-hdd"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $systemStats['disk_usage']; ?> GB / <?php echo $systemStats['disk_total']; ?> GB</h3>
                <p>Disk Usage</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon bg-purple">
                <i class="fas fa-memory"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $systemStats['memory_usage']; ?> MB</h3>
                <p>Memory Usage</p>
            </div>
        </div>
    </div>
    
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-info-circle"></i> System Information</h3>
        </div>
        <div class="card-body">
            <table class="info-table">
                <tr>
                    <td><strong>Database Type:</strong></td>
                    <td><?php echo $systemStats['db_type']; ?></td>
                </tr>
                <tr>
                    <td><strong>Server Time:</strong></td>
                    <td><?php echo $systemStats['server_time']; ?></td>
                </tr>
                <tr>
                    <td><strong>Server Load:</strong></td>
                    <td><?php echo $systemStats['uptime']; ?></td>
                </tr>
                <tr>
                    <td><strong>Backup Retention:</strong></td>
                    <td><?php echo BACKUP_RETENTION_DAYS; ?> days</td>
                </tr>
                <tr>
                    <td><strong>Auto Backup:</strong></td>
                    <td><?php echo AUTO_BACKUP_ENABLED ? '<span class="badge badge-success">Enabled</span>' : '<span class="badge badge-secondary">Disabled</span>'; ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>

<div id="backups-tab" class="tab-content">
    <div class="action-bar">
        <button class="btn btn-primary" onclick="createBackup()">
            <i class="fas fa-plus"></i> Create Backup Now
        </button>
        <button class="btn btn-secondary" onclick="cleanOldBackups()">
            <i class="fas fa-broom"></i> Clean Old Backups
        </button>
    </div>
    
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-database"></i> Recent Backups</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Filename</th>
                            <th>Size</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentBackups)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px;">
                                <i class="fas fa-database fa-3x" style="color: var(--text-light); margin-bottom: 16px;"></i>
                                <p style="color: var(--text-light);">No backups found</p>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($recentBackups as $backup): ?>
                        <tr>
                            <td><?php echo $backup['id']; ?></td>
                            <td>User #<?php echo $backup['user_id']; ?></td>
                            <td><?php echo sanitize($backup['filename']); ?></td>
                            <td><?php echo isset($backup['file_size']) ? round($backup['file_size'] / 1024, 2) . ' KB' : 'N/A'; ?></td>
                            <td><?php echo date('Y-m-d H:i:s', strtotime($backup['created_at'])); ?></td>
                            <td>
                                <button class="btn-icon btn-icon-primary" onclick="downloadBackup(<?php echo $backup['id']; ?>)" title="Download">
                                    <i class="fas fa-download"></i>
                                </button>
                                <button class="btn-icon btn-icon-danger" onclick="deleteBackup(<?php echo $backup['id']; ?>)" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="cron-tab" class="tab-content">
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-clock"></i> Cron Job Configuration</h3>
        </div>
        <div class="card-body">
            <div class="cron-instructions">
                <h4>Add these cron jobs to your Hostinger cPanel:</h4>
                <div class="cron-item">
                    <h5>1. Crypto Price Fetcher (Every 5 minutes)</h5>
                    <code>*/5 * * * * /usr/bin/php <?php echo BASE_PATH; ?>/cron/cron_fetch_crypto.php</code>
                    <button class="btn btn-sm btn-secondary" onclick="copyToClipboard(this.previousElementSibling.textContent)">
                        <i class="fas fa-copy"></i> Copy
                    </button>
                </div>
                
                <div class="cron-item">
                    <h5>2. Reminders & Alerts (Every 15 minutes)</h5>
                    <code>*/15 * * * * /usr/bin/php <?php echo BASE_PATH; ?>/cron/reminders.php</code>
                    <button class="btn btn-sm btn-secondary" onclick="copyToClipboard(this.previousElementSibling.textContent)">
                        <i class="fas fa-copy"></i> Copy
                    </button>
                </div>
                
                <div class="cron-item">
                    <h5>3. Daily Backup (At 2 AM)</h5>
                    <code>0 2 * * * /usr/bin/php <?php echo BASE_PATH; ?>/cron/backup.php</code>
                    <button class="btn btn-sm btn-secondary" onclick="copyToClipboard(this.previousElementSibling.textContent)">
                        <i class="fas fa-copy"></i> Copy
                    </button>
                </div>
            </div>
            
            <div class="cron-status">
                <h4>Cron Job Status</h4>
                <button class="btn btn-primary" onclick="testCronJobs()">
                    <i class="fas fa-play"></i> Test All Cron Jobs
                </button>
            </div>
        </div>
    </div>
</div>

<div id="config-tab" class="tab-content">
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-envelope"></i> Email Configuration (SMTP)</h3>
        </div>
        <div class="card-body">
            <form id="smtpConfigForm" onsubmit="saveSmtpConfig(event)">
                <div class="form-row">
                    <div class="form-group">
                        <label>SMTP Host</label>
                        <input type="text" name="smtp_host" class="form-control" value="<?php echo SMTP_HOST; ?>" placeholder="smtp.gmail.com">
                    </div>
                    <div class="form-group">
                        <label>SMTP Port</label>
                        <input type="number" name="smtp_port" class="form-control" value="<?php echo SMTP_PORT; ?>" placeholder="587">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>SMTP Username</label>
                        <input type="text" name="smtp_username" class="form-control" value="<?php echo SMTP_USERNAME; ?>" placeholder="your-email@gmail.com">
                    </div>
                    <div class="form-group">
                        <label>SMTP Password</label>
                        <input type="password" name="smtp_password" class="form-control" placeholder="••••••••">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>From Email</label>
                    <input type="email" name="smtp_from_email" class="form-control" value="<?php echo SMTP_FROM_EMAIL; ?>" placeholder="noreply@yourdomain.com">
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Configuration
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="testEmail()">
                        <i class="fas fa-paper-plane"></i> Send Test Email
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fab fa-telegram"></i> Telegram Configuration</h3>
        </div>
        <div class="card-body">
            <form id="telegramConfigForm" onsubmit="saveTelegramConfig(event)">
                <div class="form-group">
                    <label>Telegram Bot Token</label>
                    <input type="text" name="telegram_bot_token" class="form-control" value="<?php echo TELEGRAM_BOT_TOKEN; ?>" placeholder="123456:ABC-DEF1234ghIkl-zyx57W2v1u123ew11">
                    <small class="form-text">Get your bot token from @BotFather on Telegram</small>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Configuration
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="testTelegram()">
                        <i class="fas fa-paper-plane"></i> Send Test Message
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-tools"></i> System Settings</h3>
        </div>
        <div class="card-body">
            <div class="setting-item">
                <div class="setting-info">
                    <h4>Maintenance Mode</h4>
                    <p>Temporarily disable access for all users except admins</p>
                </div>
                <div class="setting-control">
                    <label class="switch">
                        <input type="checkbox" id="maintenance-mode" onchange="toggleMaintenanceMode(this)">
                        <span class="slider"></span>
                    </label>
                </div>
            </div>
            
            <div class="setting-item">
                <div class="setting-info">
                    <h4>Auto Backups</h4>
                    <p>Automatically backup user data daily</p>
                </div>
                <div class="setting-control">
                    <label class="switch">
                        <input type="checkbox" id="auto-backup" <?php echo AUTO_BACKUP_ENABLED ? 'checked' : ''; ?> onchange="toggleAutoBackup(this)">
                        <span class="slider"></span>
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="logs-tab" class="tab-content">
    <div class="action-bar">
        <button class="btn btn-secondary" onclick="clearSystemLogs()">
            <i class="fas fa-trash"></i> Clear Logs
        </button>
        <button class="btn btn-primary" onclick="refreshLogs()">
            <i class="fas fa-sync"></i> Refresh
        </button>
    </div>
    
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-file-alt"></i> Recent System Logs</h3>
        </div>
        <div class="card-body">
            <div class="logs-container" id="system-logs">
                <?php if (empty($recentLogs)): ?>
                <div style="text-align: center; padding: 40px; color: var(--text-light);">
                    <i class="fas fa-file-alt fa-3x" style="margin-bottom: 16px;"></i>
                    <p>No system logs found</p>
                </div>
                <?php else: ?>
                <?php foreach ($recentLogs as $log): ?>
                <div class="log-entry">
                    <span class="log-time"><?php echo date('Y-m-d H:i:s', strtotime($log['created_at'])); ?></span>
                    <span class="log-message"><?php echo sanitize($log['message']); ?></span>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.admin-tabs {
    display: flex;
    gap: 8px;
    margin-bottom: 24px;
    border-bottom: 2px solid var(--border);
    overflow-x: auto;
}

.tab-btn {
    padding: 12px 24px;
    background: transparent;
    border: none;
    color: var(--text-light);
    cursor: pointer;
    transition: all 0.3s;
    border-bottom: 3px solid transparent;
    white-space: nowrap;
}

.tab-btn:hover {
    color: var(--primary);
}

.tab-btn.active {
    color: var(--primary);
    border-bottom-color: var(--primary);
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

.info-table {
    width: 100%;
}

.info-table tr {
    border-bottom: 1px solid var(--border);
}

.info-table td {
    padding: 12px 0;
}

.cron-instructions {
    margin-bottom: 32px;
}

.cron-item {
    margin-bottom: 24px;
    padding: 16px;
    background: var(--light);
    border-radius: 8px;
}

.cron-item h5 {
    margin-bottom: 8px;
    color: var(--text);
}

.cron-item code {
    display: block;
    padding: 12px;
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 4px;
    margin: 8px 0;
    font-size: 0.9rem;
    overflow-x: auto;
}

.cron-status {
    margin-top: 24px;
    padding-top: 24px;
    border-top: 1px solid var(--border);
}

.setting-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 0;
    border-bottom: 1px solid var(--border);
}

.setting-item:last-child {
    border-bottom: none;
}

.setting-info h4 {
    margin: 0 0 4px 0;
}

.setting-info p {
    margin: 0;
    color: var(--text-light);
    font-size: 0.9rem;
}

.switch {
    position: relative;
    display: inline-block;
    width: 60px;
    height: 34px;
}

.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: 0.4s;
    border-radius: 34px;
}

.slider:before {
    position: absolute;
    content: "";
    height: 26px;
    width: 26px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    transition: 0.4s;
    border-radius: 50%;
}

input:checked + .slider {
    background-color: var(--primary);
}

input:checked + .slider:before {
    transform: translateX(26px);
}

.logs-container {
    max-height: 500px;
    overflow-y: auto;
}

.log-entry {
    padding: 12px;
    border-bottom: 1px solid var(--border);
    font-family: monospace;
    font-size: 0.9rem;
}

.log-time {
    color: var(--text-light);
    margin-right: 12px;
}

.log-message {
    color: var(--text);
}

.form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 16px;
}
</style>

<script>
function switchTab(tabName) {
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
    
    event.target.closest('.tab-btn').classList.add('active');
    document.getElementById(tabName + '-tab').classList.add('active');
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showToast('Cron command copied to clipboard!', 'success');
    });
}

async function createBackup() {
    if (!confirm('Create a new backup? This may take a few moments.')) return;
    
    try {
        showToast('Creating backup...', 'info');
        const response = await fetch('/api/system.php?action=create_backup', {
            method: 'POST'
        });
        const data = await response.json();
        
        if (data.success) {
            showToast(data.message, 'success');
            location.reload();
        } else {
            showToast(data.message, 'error');
        }
    } catch (error) {
        showToast('Failed to create backup', 'error');
    }
}

async function testCronJobs() {
    showToast('Testing cron jobs...', 'info');
    
    try {
        const response = await fetch('/api/system.php?action=test_cron');
        const data = await response.json();
        
        if (data.success) {
            showToast('Cron jobs tested successfully! Check logs for details.', 'success');
        } else {
            showToast(data.message, 'error');
        }
    } catch (error) {
        showToast('Failed to test cron jobs', 'error');
    }
}

async function testEmail() {
    showToast('Sending test email...', 'info');
    
    try {
        const response = await fetch('/api/system.php?action=test_email', {
            method: 'POST'
        });
        const data = await response.json();
        
        if (data.success) {
            showToast('Test email sent successfully!', 'success');
        } else {
            showToast(data.message, 'error');
        }
    } catch (error) {
        showToast('Failed to send test email', 'error');
    }
}

async function testTelegram() {
    showToast('Sending test Telegram message...', 'info');
    
    try {
        const response = await fetch('/api/system.php?action=test_telegram', {
            method: 'POST'
        });
        const data = await response.json();
        
        if (data.success) {
            showToast('Test message sent successfully!', 'success');
        } else {
            showToast(data.message, 'error');
        }
    } catch (error) {
        showToast('Failed to send test message', 'error');
    }
}

function toggleMaintenanceMode(checkbox) {
    const enabled = checkbox.checked;
    
    if (!confirm(`${enabled ? 'Enable' : 'Disable'} maintenance mode?`)) {
        checkbox.checked = !enabled;
        return;
    }
    
    showToast(`Maintenance mode ${enabled ? 'enabled' : 'disabled'}`, 'info');
}

function toggleAutoBackup(checkbox) {
    const enabled = checkbox.checked;
    showToast(`Auto backup ${enabled ? 'enabled' : 'disabled'}`, 'info');
}

async function refreshLogs() {
    location.reload();
}

async function clearSystemLogs() {
    if (!confirm('Clear all system logs? This cannot be undone.')) return;
    
    try {
        const response = await fetch('/api/system.php?action=clear_logs', {
            method: 'POST'
        });
        const data = await response.json();
        
        if (data.success) {
            showToast('Logs cleared successfully', 'success');
            document.getElementById('system-logs').innerHTML = '<div style="text-align: center; padding: 40px; color: var(--text-light);"><p>No system logs found</p></div>';
        } else {
            showToast(data.message, 'error');
        }
    } catch (error) {
        showToast('Failed to clear logs', 'error');
    }
}
</script>

<?php include 'includes/footer.php'; ?>
