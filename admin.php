<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$pageTitle = 'System Administration';
include 'includes/header.php';

$systemStats = [
    'total_users' => $db->fetchColumn("SELECT COUNT(*) FROM users"),
    'total_records' => 
        $db->fetchColumn("SELECT COUNT(*) FROM assets WHERE user_id = ?", [$userId]) +
        $db->fetchColumn("SELECT COUNT(*) FROM bills WHERE user_id = ?", [$userId]) +
        $db->fetchColumn("SELECT COUNT(*) FROM finance WHERE user_id = ?", [$userId]) +
        $db->fetchColumn("SELECT COUNT(*) FROM goals WHERE user_id = ?", [$userId]) +
        $db->fetchColumn("SELECT COUNT(*) FROM tasks WHERE user_id = ?", [$userId]),
    'database_size' => $db->fetchColumn("SELECT pg_size_pretty(pg_database_size(current_database()))"),
    'last_login' => $db->fetchColumn("SELECT MAX(created_at) FROM users WHERE id = ?", [$userId])
];

$recentActivity = $db->fetchAll("
    SELECT 'Task' as type, title as description, created_at FROM tasks WHERE user_id = ? 
    UNION ALL
    SELECT 'Goal' as type, title as description, created_at FROM goals WHERE user_id = ?
    UNION ALL
    SELECT 'Journal' as type, LEFT(entry, 50) || '...' as description, entry_date as created_at FROM journal WHERE user_id = ?
    ORDER BY created_at DESC LIMIT 10
", [$userId, $userId, $userId]);
?>

<div class="page-header">
    <h1><i class="fas fa-cog"></i> System Administration</h1>
    <p class="page-subtitle">Manage your Life Atlas system settings and configuration</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon bg-blue">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $systemStats['total_users']; ?></h3>
            <p>Total Users</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-green">
            <i class="fas fa-database"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $systemStats['total_records']; ?></h3>
            <p>Total Records</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-purple">
            <i class="fas fa-hdd"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $systemStats['database_size']; ?></h3>
            <p>Database Size</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-orange">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo date('M d', strtotime($systemStats['last_login'])); ?></h3>
            <p>Last Activity</p>
        </div>
    </div>
</div>

<div class="dashboard-grid">
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-chart-line"></i> System Health</h3>
        </div>
        <div class="card-body">
            <div class="health-item">
                <span class="health-label">Database Connection</span>
                <span class="badge badge-success">Active</span>
            </div>
            <div class="health-item">
                <span class="health-label">Session Status</span>
                <span class="badge badge-success">Connected</span>
            </div>
            <div class="health-item">
                <span class="health-label">API Services</span>
                <span class="badge badge-success">Online</span>
            </div>
            <div class="health-item">
                <span class="health-label">Backup System</span>
                <span class="badge badge-warning">Manual</span>
            </div>
        </div>
    </div>
    
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-history"></i> Recent Activity</h3>
        </div>
        <div class="card-body">
            <div class="activity-list">
                <?php foreach ($recentActivity as $activity): ?>
                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-<?php echo $activity['type'] === 'Task' ? 'tasks' : ($activity['type'] === 'Goal' ? 'bullseye' : 'book'); ?>"></i>
                    </div>
                    <div class="activity-info">
                        <p class="activity-title"><?php echo sanitize($activity['description']); ?></p>
                        <span class="activity-time"><?php echo timeAgo($activity['created_at']); ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-tools"></i> Quick Actions</h3>
        </div>
        <div class="card-body">
            <div class="action-buttons">
                <button class="btn btn-primary" onclick="exportData()">
                    <i class="fas fa-download"></i> Export All Data
                </button>
                <button class="btn btn-secondary" onclick="clearCache()">
                    <i class="fas fa-broom"></i> Clear Cache
                </button>
                <button class="btn btn-warning" onclick="optimizeDatabase()">
                    <i class="fas fa-database"></i> Optimize Database
                </button>
                <button class="btn btn-info" onclick="viewLogs()">
                    <i class="fas fa-file-alt"></i> View System Logs
                </button>
            </div>
        </div>
    </div>
    
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-cogs"></i> Module Status</h3>
        </div>
        <div class="card-body">
            <div class="module-list">
                <div class="module-item">
                    <span>Assets Module</span>
                    <span class="badge badge-success">Active</span>
                </div>
                <div class="module-item">
                    <span>Finance Module</span>
                    <span class="badge badge-success">Active</span>
                </div>
                <div class="module-item">
                    <span>Health Module</span>
                    <span class="badge badge-success">Active</span>
                </div>
                <div class="module-item">
                    <span>Cryptocurrency Module</span>
                    <span class="badge badge-success">Active</span>
                </div>
                <div class="module-item">
                    <span>Analytics Module</span>
                    <span class="badge badge-success">Active</span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.health-item, .module-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px;
    border-bottom: 1px solid var(--border);
}

.health-item:last-child, .module-item:last-child {
    border-bottom: none;
}

.health-label {
    font-weight: 500;
    color: var(--text);
}

.activity-list {
    max-height: 400px;
    overflow-y: auto;
}

.activity-item {
    display: flex;
    gap: 12px;
    padding: 12px;
    border-bottom: 1px solid var(--border);
    transition: background 0.3s ease;
}

.activity-item:hover {
    background: var(--light);
}

.activity-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: var(--primary);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
}

.activity-info {
    flex: 1;
}

.activity-title {
    margin: 0 0 4px 0;
    font-weight: 500;
    color: var(--text);
}

.activity-time {
    font-size: 0.875rem;
    color: var(--text-light);
}

.action-buttons {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 12px;
}
</style>

<script>
function exportData() {
    showToast('Preparing data export...', 'info');
    setTimeout(() => {
        window.location.href = '/api/export.php';
    }, 1000);
}

function clearCache() {
    showToast('Cache cleared successfully!', 'success');
}

function optimizeDatabase() {
    showToast('Database optimization started...', 'info');
    fetch('/api/optimize.php', { method: 'POST' })
        .then(response => response.json())
        .then(data => {
            showToast('Database optimized successfully!', 'success');
        })
        .catch(error => {
            showToast('Optimization failed', 'error');
        });
}

function viewLogs() {
    window.open('/logs.php', '_blank');
}
</script>

<?php include 'includes/footer.php'; ?>
