<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$goals = $db->fetchAll("SELECT * FROM goals WHERE user_id = ? ORDER BY created_at DESC", [$userId]);

$pageTitle = 'Goals';
include 'includes/header.php';
?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-bullseye"></i> Goals</h1>
        <p class="page-subtitle">Set and track your personal goals</p>
    </div>
    <button class="btn btn-primary" onclick="openModal('goalModal')">
        <i class="fas fa-plus"></i> Add Goal
    </button>
</div>

<div class="dashboard-grid">
    <?php foreach ($goals as $goal): ?>
    <div class="dashboard-card">
        <div class="card-body">
            <h4><?php echo sanitize($goal['title']); ?></h4>
            <p><?php echo sanitize($goal['description']); ?></p>
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?php echo $goal['progress']; ?>%"></div>
            </div>
            <span class="progress-text"><?php echo $goal['progress']; ?>%</span>
            <p><small>Target: <?php echo formatDate($goal['target_date']); ?></small></p>
            <button onclick="deleteItem('goals', <?php echo $goal['id']; ?>)" class="btn-icon btn-danger">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div id="goalModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add Goal</h3>
            <button class="modal-close">&times;</button>
        </div>
        <form onsubmit="event.preventDefault(); const formData = new FormData(event.target); createItem('goals', Object.fromEntries(formData.entries()));">
            <div class="form-group">
                <label>Title</label>
                <input type="text" name="title" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label>Category</label>
                <input type="text" name="category">
            </div>
            <div class="form-group">
                <label>Target Date</label>
                <input type="date" name="target_date">
            </div>
            <div class="form-group">
                <label>Progress (%)</label>
                <input type="number" name="progress" min="0" max="100" value="0">
            </div>
            <button type="submit" class="btn btn-primary btn-block">Save Goal</button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
