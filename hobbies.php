<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$hobbies = $db->fetchAll("SELECT * FROM hobbies WHERE user_id = ? ORDER BY created_at DESC", [$userId]);

$pageTitle = 'Hobbies';
include 'includes/header.php';
?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-palette"></i> Hobbies</h1>
        <p class="page-subtitle">Track your hobbies and time spent</p>
    </div>
    <button class="btn btn-primary" onclick="openModal('hobbyModal')">
        <i class="fas fa-plus"></i> Add Hobby
    </button>
</div>

<div class="dashboard-card">
    <div class="card-body">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Time Spent (hrs)</th>
                    <th>Progress Notes</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($hobbies as $hobby): ?>
                <tr>
                    <td><?php echo sanitize($hobby['name']); ?></td>
                    <td><?php echo sanitize($hobby['category']); ?></td>
                    <td><?php echo $hobby['time_spent_hours']; ?></td>
                    <td><?php echo sanitize($hobby['progress_notes']); ?></td>
                    <td>
                        <button onclick="deleteItem('hobbies', <?php echo $hobby['id']; ?>)" class="btn-icon btn-danger">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="hobbyModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add Hobby</h3>
            <button class="modal-close">&times;</button>
        </div>
        <form onsubmit="event.preventDefault(); const formData = new FormData(event.target); createItem('hobbies', Object.fromEntries(formData.entries()));">
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>Category</label>
                <input type="text" name="category">
            </div>
            <div class="form-group">
                <label>Time Spent (hours)</label>
                <input type="number" name="time_spent_hours" step="0.5" value="0">
            </div>
            <div class="form-group">
                <label>Progress Notes</label>
                <textarea name="progress_notes" rows="3"></textarea>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Save Hobby</button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
