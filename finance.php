<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$transactions = $db->fetchAll("SELECT * FROM finance WHERE user_id = ? ORDER BY date DESC LIMIT 50", [$userId]);
$stats = $db->fetchOne("
    SELECT 
        COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) as total_income,
        COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) as total_expense
    FROM finance 
    WHERE user_id = ? 
    AND EXTRACT(MONTH FROM date) = EXTRACT(MONTH FROM CURRENT_DATE)
", [$userId]);

$pageTitle = 'Finance';
include 'includes/header.php';
?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-wallet"></i> Finance</h1>
        <p class="page-subtitle">Track your income and expenses</p>
    </div>
    <button class="btn btn-primary" onclick="openModal('financeModal')">
        <i class="fas fa-plus"></i> Add Transaction
    </button>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon bg-green">
            <i class="fas fa-arrow-up"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo formatCurrency($stats['total_income']); ?></h3>
            <p>Total Income (This Month)</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-red">
            <i class="fas fa-arrow-down"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo formatCurrency($stats['total_expense']); ?></h3>
            <p>Total Expenses (This Month)</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-blue">
            <i class="fas fa-balance-scale"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo formatCurrency($stats['total_income'] - $stats['total_expense']); ?></h3>
            <p>Net Balance (This Month)</p>
        </div>
    </div>
</div>

<div class="dashboard-card">
    <div class="card-body">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Category</th>
                    <th>Amount</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transactions as $transaction): ?>
                <tr>
                    <td><?php echo formatDate($transaction['date']); ?></td>
                    <td><span class="badge badge-<?php echo $transaction['type'] == 'income' ? 'low' : 'high'; ?>"><?php echo ucfirst($transaction['type']); ?></span></td>
                    <td><?php echo sanitize($transaction['category']); ?></td>
                    <td><?php echo formatCurrency($transaction['amount']); ?></td>
                    <td><?php echo sanitize($transaction['description']); ?></td>
                    <td>
                        <button onclick="deleteItem('finance', <?php echo $transaction['id']; ?>)" class="btn-icon btn-danger">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="financeModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add Transaction</h3>
            <button class="modal-close">&times;</button>
        </div>
        <form id="financeForm" onsubmit="saveTransaction(event)">
            <div class="form-group">
                <label>Type</label>
                <select name="type" required>
                    <option value="income">Income</option>
                    <option value="expense">Expense</option>
                </select>
            </div>
            <div class="form-group">
                <label>Category</label>
                <input type="text" name="category">
            </div>
            <div class="form-group">
                <label>Amount</label>
                <input type="number" name="amount" step="0.01" required>
            </div>
            <div class="form-group">
                <label>Date</label>
                <input type="date" name="date" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="3"></textarea>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Save Transaction</button>
        </form>
    </div>
</div>

<script>
async function saveTransaction(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    
    await createItem('finance', data);
}
</script>

<?php include 'includes/footer.php'; ?>
