<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$bills = $db->fetchAll("SELECT * FROM bills WHERE user_id = ? ORDER BY due_date", [$userId]);

$pageTitle = 'Bills';
include 'includes/header.php';
?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-file-invoice-dollar"></i> Bills</h1>
        <p class="page-subtitle">Track your bills and payment due dates</p>
    </div>
    <button class="btn btn-primary" onclick="openModal('billModal')">
        <i class="fas fa-plus"></i> Add Bill
    </button>
</div>

<div class="dashboard-card">
    <div class="card-body">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Amount</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Recurring</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bills as $bill): ?>
                <tr>
                    <td><?php echo sanitize($bill['name']); ?></td>
                    <td><?php echo formatCurrency($bill['amount']); ?></td>
                    <td><?php echo formatDate($bill['due_date']); ?></td>
                    <td><span class="badge badge-<?php echo $bill['payment_status'] == 'paid' ? 'low' : 'high'; ?>"><?php echo ucfirst($bill['payment_status']); ?></span></td>
                    <td><?php echo $bill['recurring'] ? 'Yes' : 'No'; ?></td>
                    <td>
                        <button onclick="deleteItem('bills', <?php echo $bill['id']; ?>)" class="btn-icon btn-danger">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="billModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add Bill</h3>
            <button class="modal-close">&times;</button>
        </div>
        <form onsubmit="event.preventDefault(); const formData = new FormData(event.target); createItem('bills', Object.fromEntries(formData.entries()));">
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>Amount</label>
                <input type="number" name="amount" step="0.01" required>
            </div>
            <div class="form-group">
                <label>Due Date</label>
                <input type="date" name="due_date" required>
            </div>
            <div class="form-group">
                <label>Payment Status</label>
                <select name="payment_status">
                    <option value="pending">Pending</option>
                    <option value="paid">Paid</option>
                </select>
            </div>
            <div class="form-group">
                <label><input type="checkbox" name="recurring" value="1"> Recurring Bill</label>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Save Bill</button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
