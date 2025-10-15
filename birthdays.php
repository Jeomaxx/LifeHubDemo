<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$birthdays = $db->fetchAll("SELECT * FROM birthdays WHERE user_id = ? ORDER BY EXTRACT(MONTH FROM birth_date), EXTRACT(DAY FROM birth_date)", [$userId]);

$pageTitle = 'Birthdays';
include 'includes/header.php';
?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-birthday-cake"></i> Birthdays</h1>
        <p class="page-subtitle">Never forget important birthdays</p>
    </div>
    <button class="btn btn-primary" onclick="openModal('birthdayModal')">
        <i class="fas fa-plus"></i> Add Birthday
    </button>
</div>

<div class="dashboard-card">
    <div class="card-body">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Birth Date</th>
                    <th>Relationship</th>
                    <th>Notes</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($birthdays as $birthday): ?>
                <tr>
                    <td><?php echo sanitize($birthday['name']); ?></td>
                    <td><?php echo formatDate($birthday['birth_date'], 'M d, Y'); ?></td>
                    <td><?php echo sanitize($birthday['relationship']); ?></td>
                    <td><?php echo sanitize($birthday['notes']); ?></td>
                    <td>
                        <button onclick="deleteItem('birthdays', <?php echo $birthday['id']; ?>)" class="btn-icon btn-danger">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="birthdayModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add Birthday</h3>
            <button class="modal-close">&times;</button>
        </div>
        <form onsubmit="event.preventDefault(); const formData = new FormData(event.target); createItem('birthdays', Object.fromEntries(formData.entries()));">
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>Birth Date</label>
                <input type="date" name="birth_date" required>
            </div>
            <div class="form-group">
                <label>Relationship</label>
                <input type="text" name="relationship">
            </div>
            <div class="form-group">
                <label>Notes</label>
                <textarea name="notes" rows="3"></textarea>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Save Birthday</button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
