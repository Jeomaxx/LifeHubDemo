<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$assets = $db->fetchAll("SELECT * FROM assets WHERE user_id = ? ORDER BY created_at DESC", [$userId]);
$totalValue = $db->fetchOne("SELECT COALESCE(SUM(value), 0) as total FROM assets WHERE user_id = ?", [$userId]);

$pageTitle = 'Assets';
include 'includes/header.php';
?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-box"></i> Assets</h1>
        <p class="page-subtitle">Track your valuable possessions and their worth</p>
    </div>
    <button class="btn btn-primary" onclick="openModal('assetModal')">
        <i class="fas fa-plus"></i> Add Asset
    </button>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon bg-blue">
            <i class="fas fa-box"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo count($assets); ?></h3>
            <p>Total Assets</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-green">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo formatCurrency($totalValue['total']); ?></h3>
            <p>Total Value</p>
        </div>
    </div>
</div>

<div class="dashboard-card">
    <div class="card-body">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Value</th>
                    <th>Acquisition Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($assets as $asset): ?>
                <tr>
                    <td><?php echo sanitize($asset['name']); ?></td>
                    <td><?php echo sanitize($asset['category']); ?></td>
                    <td><?php echo formatCurrency($asset['value']); ?></td>
                    <td><?php echo formatDate($asset['acquisition_date']); ?></td>
                    <td>
                        <button onclick="editAsset(<?php echo $asset['id']; ?>)" class="btn-icon">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="deleteItem('assets', <?php echo $asset['id']; ?>)" class="btn-icon btn-danger">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="assetModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add Asset</h3>
            <button class="modal-close">&times;</button>
        </div>
        <form id="assetForm" onsubmit="saveAsset(event)">
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>Category</label>
                <input type="text" name="category">
            </div>
            <div class="form-group">
                <label>Value</label>
                <input type="number" name="value" step="0.01">
            </div>
            <div class="form-group">
                <label>Acquisition Date</label>
                <input type="date" name="acquisition_date">
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="3"></textarea>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Save Asset</button>
        </form>
    </div>
</div>

<style>
.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th {
    text-align: left;
    padding: 0.75rem;
    border-bottom: 2px solid var(--border);
    color: var(--text);
    font-weight: 600;
}

.data-table td {
    padding: 0.75rem;
    border-bottom: 1px solid var(--border);
}

.btn-icon {
    background: none;
    border: none;
    padding: 0.5rem;
    cursor: pointer;
    color: var(--primary);
    font-size: 1rem;
}

.btn-icon.btn-danger {
    color: var(--danger);
}
</style>

<script>
async function saveAsset(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    
    await createItem('assets', data);
}

function editAsset(id) {
    console.log('Edit asset:', id);
}
</script>

<?php include 'includes/footer.php'; ?>
