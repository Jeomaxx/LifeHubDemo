<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$habits = $db->fetchAll("SELECT * FROM habits WHERE user_id = ? ORDER BY created_at DESC", [$userId]);

$pageTitle = 'Habits';
include 'includes/header.php';
?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-check-circle"></i> Habits</h1>
        <p class="page-subtitle">Build and track your daily habits</p>
    </div>
    <button class="btn btn-primary" onclick="openModal('habitModal')">
        <i class="fas fa-plus"></i> Add Habit
    </button>
</div>

<div class="dashboard-card">
    <div class="card-body">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Habit</th>
                    <th>Description</th>
                    <th>Frequency</th>
                    <th>Current Streak</th>
                    <th>Best Streak</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($habits as $habit): ?>
                <tr>
                    <td><?php echo sanitize($habit['name']); ?></td>
                    <td><?php echo sanitize($habit['description']); ?></td>
                    <td><?php echo ucfirst($habit['frequency']); ?></td>
                    <td><?php echo $habit['streak']; ?> days</td>
                    <td><?php echo $habit['best_streak']; ?> days</td>
                    <td>
                        <button onclick="deleteItem('habits', <?php echo $habit['id']; ?>)" class="btn-icon btn-danger">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="habitModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add Habit</h3>
            <button class="modal-close">&times;</button>
        </div>
        <form onsubmit="event.preventDefault(); const formData = new FormData(event.target); createItem('habits', Object.fromEntries(formData.entries()));">
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label>Frequency</label>
                <select name="frequency">
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly">Monthly</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Save Habit</button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
