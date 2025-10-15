<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$subscriptions = $db->fetchAll("SELECT * FROM subscriptions WHERE user_id = ? ORDER BY renewal_date", [$userId]);
$totalCost = $db->fetchOne("SELECT COALESCE(SUM(cost), 0) as total FROM subscriptions WHERE user_id = ? AND status = 'active'", [$userId]);

$pageTitle = 'Subscriptions';
include 'includes/header.php';
?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-sync"></i> Subscriptions</h1>
        <p class="page-subtitle">Manage your recurring subscriptions</p>
    </div>
    <button class="btn btn-primary" onclick="openModal('subscriptionModal')">
        <i class="fas fa-plus"></i> Add Subscription
    </button>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon bg-purple">
            <i class="fas fa-sync"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo count($subscriptions); ?></h3>
            <p>Total Subscriptions</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-red">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo formatCurrency($totalCost['total']); ?></h3>
            <p>Monthly Cost</p>
        </div>
    </div>
</div>

<div class="dashboard-card">
    <div class="card-body">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Cost</th>
                    <th>Billing Cycle</th>
                    <th>Renewal Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($subscriptions as $sub): ?>
                <tr>
                    <td><?php echo sanitize($sub['name']); ?></td>
                    <td><?php echo formatCurrency($sub['cost']); ?></td>
                    <td><?php echo ucfirst($sub['billing_cycle']); ?></td>
                    <td><?php echo formatDate($sub['renewal_date']); ?></td>
                    <td><span class="badge badge-<?php echo $sub['status'] == 'active' ? 'low' : 'high'; ?>"><?php echo ucfirst($sub['status']); ?></span></td>
                    <td>
                        <button onclick="deleteItem('subscriptions', <?php echo $sub['id']; ?>)" class="btn-icon btn-danger">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="subscriptionModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add Subscription</h3>
            <button class="modal-close">&times;</button>
        </div>
        <form onsubmit="event.preventDefault(); const formData = new FormData(event.target); createItem('subscriptions', Object.fromEntries(formData.entries()));">
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>Cost</label>
                <input type="number" name="cost" step="0.01" required>
            </div>
            <div class="form-group">
                <label>Billing Cycle</label>
                <select name="billing_cycle">
                    <option value="monthly">Monthly</option>
                    <option value="yearly">Yearly</option>
                    <option value="quarterly">Quarterly</option>
                </select>
            </div>
            <div class="form-group">
                <label>Renewal Date</label>
                <input type="date" name="renewal_date" required>
            </div>
            <div class="form-group">
                <label>Category</label>
                <input type="text" name="category" placeholder="Streaming, Software, etc.">
            </div>
            <button type="submit" class="btn btn-primary btn-block">Save</button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
