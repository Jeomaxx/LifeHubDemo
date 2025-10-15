<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$learningItems = $db->fetchAll("SELECT * FROM learning WHERE user_id = ? ORDER BY created_at DESC", [$userId]);

$pageTitle = 'Learning';
include 'includes/header.php';
?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-graduation-cap"></i> Learning</h1>
        <p class="page-subtitle">Track your courses, books, and study progress</p>
    </div>
    <button class="btn btn-primary" onclick="openModal('learningModal')">
        <i class="fas fa-plus"></i> Add Course/Book
    </button>
</div>

<div class="dashboard-card">
    <div class="card-body">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Platform</th>
                    <th>Progress</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($learningItems as $item): ?>
                <tr>
                    <td><?php echo sanitize($item['title']); ?></td>
                    <td><?php echo ucfirst($item['type']); ?></td>
                    <td><?php echo sanitize($item['platform']); ?></td>
                    <td><?php echo $item['progress']; ?>%</td>
                    <td><span class="badge badge-<?php echo $item['status'] == 'completed' ? 'low' : 'medium'; ?>"><?php echo ucfirst($item['status']); ?></span></td>
                    <td>
                        <button onclick="deleteItem('learning', <?php echo $item['id']; ?>)" class="btn-icon btn-danger">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="learningModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add Learning Item</h3>
            <button class="modal-close">&times;</button>
        </div>
        <form onsubmit="event.preventDefault(); const formData = new FormData(event.target); createItem('learning', Object.fromEntries(formData.entries()));">
            <div class="form-group">
                <label>Title</label>
                <input type="text" name="title" required>
            </div>
            <div class="form-group">
                <label>Type</label>
                <select name="type">
                    <option value="course">Course</option>
                    <option value="book">Book</option>
                    <option value="tutorial">Tutorial</option>
                </select>
            </div>
            <div class="form-group">
                <label>Platform</label>
                <input type="text" name="platform" placeholder="Udemy, Coursera, etc.">
            </div>
            <div class="form-group">
                <label>Progress (%)</label>
                <input type="number" name="progress" min="0" max="100" value="0">
            </div>
            <button type="submit" class="btn btn-primary btn-block">Save</button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
