<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$investments = $db->fetchAll("SELECT * FROM investments WHERE user_id = ? ORDER BY created_at DESC", [$userId]);
$totalInvested = $db->fetchOne("SELECT COALESCE(SUM(amount_invested), 0) as total FROM investments WHERE user_id = ?", [$userId]);
$totalValue = $db->fetchOne("SELECT COALESCE(SUM(current_value), 0) as total FROM investments WHERE user_id = ?", [$userId]);

$pageTitle = 'Investments';
include 'includes/header.php';
?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-chart-line"></i> Investments</h1>
        <p class="page-subtitle">Track your investment portfolio</p>
    </div>
    <button class="btn btn-primary" onclick="openModal('investmentModal')">
        <i class="fas fa-plus"></i> Add Investment
    </button>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon bg-blue">
            <i class="fas fa-piggy-bank"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo formatCurrency($totalInvested['total']); ?></h3>
            <p>Total Invested</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-green">
            <i class="fas fa-chart-line"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo formatCurrency($totalValue['total']); ?></h3>
            <p>Current Value</p>
        </div>
    </div>
</div>

<div class="dashboard-card">
    <div class="card-body">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Invested</th>
                    <th>Current Value</th>
                    <th>Return</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($investments as $investment): ?>
                <tr>
                    <td><?php echo sanitize($investment['name']); ?></td>
                    <td><?php echo sanitize($investment['type']); ?></td>
                    <td><?php echo formatCurrency($investment['amount_invested']); ?></td>
                    <td><?php echo formatCurrency($investment['current_value']); ?></td>
                    <td><?php 
                        $return = $investment['current_value'] - $investment['amount_invested'];
                        echo ($return >= 0 ? '+' : '') . formatCurrency($return);
                    ?></td>
                    <td>
                        <button onclick="deleteItem('investments', <?php echo $investment['id']; ?>)" class="btn-icon btn-danger">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="investmentModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add Investment</h3>
            <button class="modal-close">&times;</button>
        </div>
        <form onsubmit="event.preventDefault(); const formData = new FormData(event.target); createItem('investments', Object.fromEntries(formData.entries()));">
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>Type</label>
                <input type="text" name="type" placeholder="Stocks, Bonds, Real Estate, etc.">
            </div>
            <div class="form-group">
                <label>Amount Invested</label>
                <input type="number" name="amount_invested" step="0.01" required>
            </div>
            <div class="form-group">
                <label>Current Value</label>
                <input type="number" name="current_value" step="0.01">
            </div>
            <div class="form-group">
                <label>Purchase Date</label>
                <input type="date" name="purchase_date">
            </div>
            <button type="submit" class="btn btn-primary btn-block">Save Investment</button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
