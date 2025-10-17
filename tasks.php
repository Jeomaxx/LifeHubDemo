<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$tasks = $db->fetchAll("SELECT * FROM tasks WHERE user_id = ? ORDER BY created_at DESC", [$userId]) ?: [];
$pendingCount = $db->fetchOne("SELECT COUNT(*) as count FROM tasks WHERE user_id = ? AND status != 'completed'", [$userId]);

$pageTitle = 'Tasks';
include 'includes/header.php';
?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-tasks"></i> Tasks</h1>
        <p class="page-subtitle">Manage your to-do list and get things done</p>
    </div>
    <button class="btn btn-primary" onclick="openModal('taskModal')">
        <i class="fas fa-plus"></i> Add Task
    </button>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon bg-purple">
            <i class="fas fa-tasks"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo count($tasks); ?></h3>
            <p>Total Tasks</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-orange">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $pendingCount['count']; ?></h3>
            <p>Pending Tasks</p>
        </div>
    </div>
</div>

<div class="dashboard-card">
    <div class="card-body">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Priority</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tasks as $task): ?>
                <tr>
                    <td><?php echo sanitize($task['title']); ?></td>
                    <td><?php echo sanitize($task['category']); ?></td>
                    <td><span class="badge badge-<?php echo $task['priority']; ?>"><?php echo ucfirst($task['priority']); ?></span></td>
                    <td><?php echo formatDate($task['due_date']); ?></td>
                    <td><?php echo ucfirst($task['status']); ?></td>
                    <td>
                        <button onclick="deleteItem('tasks', <?php echo $task['id']; ?>)" class="btn-icon btn-danger">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="taskModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add Task</h3>
            <button class="modal-close">&times;</button>
        </div>
        <form id="taskForm" onsubmit="saveTask(event)">
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
                <label>Priority</label>
                <select name="priority">
                    <option value="low">Low</option>
                    <option value="medium" selected>Medium</option>
                    <option value="high">High</option>
                </select>
            </div>
            <div class="form-group">
                <label>Due Date</label>
                <input type="date" name="due_date">
            </div>
            <button type="submit" class="btn btn-primary btn-block">Save Task</button>
        </form>
    </div>
</div>

<script>
async function saveTask(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    
    await createItem('tasks', data);
}
</script>

<?php include 'includes/footer.php'; ?>
