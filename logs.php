<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';

requireLogin();

$db = Database::getInstance();
$user_id = $_SESSION['user_id'];

$page_title = 'System Logs';

$logs = $db->query(
    "SELECT * FROM activity_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 100",
    [$user_id]
);

include 'includes/header.php';
?>

<div class="content-header">
    <h1><i class="fas fa-file-alt"></i> System Logs</h1>
    <div class="header-actions">
        <button onclick="clearLogs()" class="btn btn-danger">
            <i class="fas fa-trash"></i> Clear Logs
        </button>
        <button onclick="exportLogs()" class="btn btn-primary">
            <i class="fas fa-download"></i> Export
        </button>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Recent Activity</h3>
        <div class="filters">
            <select id="module-filter" onchange="filterLogs()">
                <option value="">All Modules</option>
                <option value="assets">Assets</option>
                <option value="bills">Bills</option>
                <option value="crypto">Crypto</option>
                <option value="finance">Finance</option>
                <option value="goals">Goals</option>
                <option value="habits">Habits</option>
                <option value="health">Health</option>
                <option value="journal">Journal</option>
                <option value="tasks">Tasks</option>
                <option value="system">System</option>
            </select>
            <select id="action-filter" onchange="filterLogs()">
                <option value="">All Actions</option>
                <option value="create">Create</option>
                <option value="update">Update</option>
                <option value="delete">Delete</option>
                <option value="login">Login</option>
                <option value="export">Export</option>
            </select>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table" id="logs-table">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Module</th>
                        <th>Action</th>
                        <th>Description</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="5" class="text-center">No logs found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr data-module="<?= htmlspecialchars($log['module']) ?>" 
                                data-action="<?= htmlspecialchars($log['action']) ?>">
                                <td><?= date('M d, Y h:i A', strtotime($log['created_at'])) ?></td>
                                <td><span class="badge"><?= htmlspecialchars($log['module']) ?></span></td>
                                <td><span class="badge badge-<?= $log['action'] == 'delete' ? 'danger' : ($log['action'] == 'create' ? 'success' : 'info') ?>">
                                    <?= htmlspecialchars($log['action']) ?>
                                </span></td>
                                <td><?= htmlspecialchars($log['description']) ?></td>
                                <td><?= htmlspecialchars($log['ip_address'] ?? 'N/A') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function filterLogs() {
    const moduleFilter = document.getElementById('module-filter').value;
    const actionFilter = document.getElementById('action-filter').value;
    const rows = document.querySelectorAll('#logs-table tbody tr[data-module]');
    
    rows.forEach(row => {
        const module = row.getAttribute('data-module');
        const action = row.getAttribute('data-action');
        const showModule = !moduleFilter || module === moduleFilter;
        const showAction = !actionFilter || action === actionFilter;
        
        row.style.display = (showModule && showAction) ? '' : 'none';
    });
}

function clearLogs() {
    if (!confirm('Are you sure you want to clear all logs? This action cannot be undone.')) {
        return;
    }
    
    fetch('/api/logs.php', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Logs cleared successfully', 'success');
            location.reload();
        } else {
            showToast(data.error || 'Failed to clear logs', 'error');
        }
    });
}

function exportLogs() {
    window.location.href = '/api/export.php?module=logs&format=csv';
}
</script>

<?php include 'includes/footer.php'; ?>
