<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$entries = $db->fetchAll("SELECT * FROM journal WHERE user_id = ? ORDER BY date DESC", [$userId]);

$pageTitle = 'Journal';
include 'includes/header.php';
?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-book"></i> Journal</h1>
        <p class="page-subtitle">Record your daily thoughts and experiences</p>
    </div>
    <button class="btn btn-primary" onclick="openModal('journalModal')">
        <i class="fas fa-plus"></i> New Entry
    </button>
</div>

<div class="dashboard-grid">
    <?php foreach ($entries as $entry): ?>
    <div class="dashboard-card">
        <div class="card-body">
            <h4><?php echo sanitize($entry['title']); ?></h4>
            <p><small><?php echo formatDate($entry['date'], 'M d, Y'); ?> - Mood: <?php echo ucfirst($entry['mood']); ?></small></p>
            <p><?php echo nl2br(sanitize($entry['content'])); ?></p>
            <button onclick="deleteItem('journal', <?php echo $entry['id']; ?>)" class="btn-icon btn-danger">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div id="journalModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>New Journal Entry</h3>
            <button class="modal-close">&times;</button>
        </div>
        <form onsubmit="event.preventDefault(); const formData = new FormData(event.target); createItem('journal', Object.fromEntries(formData.entries()));">
            <div class="form-group">
                <label>Date</label>
                <input type="date" name="date" required>
            </div>
            <div class="form-group">
                <label>Title</label>
                <input type="text" name="title">
            </div>
            <div class="form-group">
                <label>Content</label>
                <textarea name="content" rows="6" required></textarea>
            </div>
            <div class="form-group">
                <label>Mood</label>
                <select name="mood">
                    <option value="happy">Happy</option>
                    <option value="neutral">Neutral</option>
                    <option value="sad">Sad</option>
                    <option value="excited">Excited</option>
                    <option value="stressed">Stressed</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Save Entry</button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
