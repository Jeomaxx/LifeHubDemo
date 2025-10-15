<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_backup'])) {
    $filename = generateBackup($userId);
    $message = "Backup created successfully: $filename";
}

$backups = $db->fetchAll("SELECT * FROM backups WHERE user_id = ? ORDER BY created_at DESC", [$userId]);

$pageTitle = 'Backup & Restore';
include 'includes/header.php';
?>

<div class="page-header">
    <h1><i class="fas fa-database"></i> Backup & Restore</h1>
    <p class="page-subtitle">Create and manage your data backups</p>
</div>

<?php if (isset($message)): ?>
<div class="alert alert-success">
    <i class="fas fa-check-circle"></i>
    <?php echo $message; ?>
</div>
<?php endif; ?>

<div class="dashboard-card">
    <div class="card-header">
        <h3><i class="fas fa-cloud-upload-alt"></i> Create Backup</h3>
    </div>
    <div class="card-body">
        <form method="POST">
            <p>Create a complete backup of all your data. This will generate a JSON file containing all your information.</p>
            <button type="submit" name="create_backup" class="btn btn-primary">
                <i class="fas fa-download"></i> Create Backup Now
            </button>
        </form>
    </div>
</div>

<div class="dashboard-card">
    <div class="card-header">
        <h3><i class="fas fa-history"></i> Backup History</h3>
    </div>
    <div class="card-body">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Filename</th>
                    <th>Type</th>
                    <th>Size</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($backups as $backup): ?>
                <tr>
                    <td><?php echo sanitize($backup['filename']); ?></td>
                    <td><?php echo ucfirst($backup['backup_type']); ?></td>
                    <td><?php echo number_format($backup['file_size'] / 1024, 2); ?> KB</td>
                    <td><?php echo timeAgo($backup['created_at']); ?></td>
                    <td>
                        <a href="/uploads/backups/<?php echo $backup['filename']; ?>" download class="btn-icon">
                            <i class="fas fa-download"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
