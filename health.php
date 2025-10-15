<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$healthRecords = $db->fetchAll("SELECT * FROM health WHERE user_id = ? ORDER BY date DESC LIMIT 30", [$userId]);

$pageTitle = 'Health';
include 'includes/header.php';
?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-heartbeat"></i> Health</h1>
        <p class="page-subtitle">Track your health and fitness</p>
    </div>
    <button class="btn btn-primary" onclick="openModal('healthModal')">
        <i class="fas fa-plus"></i> Add Record
    </button>
</div>

<div class="dashboard-card">
    <div class="card-body">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Weight (kg)</th>
                    <th>Exercise (min)</th>
                    <th>Water (L)</th>
                    <th>Sleep (hrs)</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($healthRecords as $record): ?>
                <tr>
                    <td><?php echo formatDate($record['date']); ?></td>
                    <td><?php echo $record['weight']; ?></td>
                    <td><?php echo $record['exercise_minutes']; ?></td>
                    <td><?php echo $record['water_intake']; ?></td>
                    <td><?php echo $record['sleep_hours']; ?></td>
                    <td>
                        <button onclick="deleteItem('health', <?php echo $record['id']; ?>)" class="btn-icon btn-danger">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="healthModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add Health Record</h3>
            <button class="modal-close">&times;</button>
        </div>
        <form onsubmit="event.preventDefault(); const formData = new FormData(event.target); createItem('health', Object.fromEntries(formData.entries()));">
            <div class="form-group">
                <label>Date</label>
                <input type="date" name="date" required>
            </div>
            <div class="form-group">
                <label>Weight (kg)</label>
                <input type="number" name="weight" step="0.1">
            </div>
            <div class="form-group">
                <label>Exercise (minutes)</label>
                <input type="number" name="exercise_minutes">
            </div>
            <div class="form-group">
                <label>Water Intake (liters)</label>
                <input type="number" name="water_intake" step="0.1">
            </div>
            <div class="form-group">
                <label>Sleep (hours)</label>
                <input type="number" name="sleep_hours" step="0.5">
            </div>
            <button type="submit" class="btn btn-primary btn-block">Save Record</button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
