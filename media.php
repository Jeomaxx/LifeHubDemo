<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$mediaItems = $db->fetchAll("SELECT * FROM media WHERE user_id = ? ORDER BY created_at DESC", [$userId]);

$pageTitle = 'Media';
include 'includes/header.php';
?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-film"></i> Media</h1>
        <p class="page-subtitle">Track movies, shows, and content to watch</p>
    </div>
    <button class="btn btn-primary" onclick="openModal('mediaModal')">
        <i class="fas fa-plus"></i> Add Media
    </button>
</div>

<div class="dashboard-card">
    <div class="card-body">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Rating</th>
                    <th>Completion Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($mediaItems as $media): ?>
                <tr>
                    <td><?php echo sanitize($media['title']); ?></td>
                    <td><?php echo ucfirst($media['type']); ?></td>
                    <td><span class="badge badge-<?php echo $media['status'] == 'completed' ? 'low' : 'medium'; ?>"><?php echo ucfirst($media['status']); ?></span></td>
                    <td><?php echo str_repeat('⭐', $media['rating'] ?? 0); ?></td>
                    <td><?php echo formatDate($media['completion_date']); ?></td>
                    <td>
                        <button onclick="deleteItem('media', <?php echo $media['id']; ?>)" class="btn-icon btn-danger">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="mediaModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add Media</h3>
            <button class="modal-close">&times;</button>
        </div>
        <form onsubmit="event.preventDefault(); const formData = new FormData(event.target); createItem('media', Object.fromEntries(formData.entries()));">
            <div class="form-group">
                <label>Title</label>
                <input type="text" name="title" required>
            </div>
            <div class="form-group">
                <label>Type</label>
                <select name="type">
                    <option value="movie">Movie</option>
                    <option value="series">TV Series</option>
                    <option value="documentary">Documentary</option>
                    <option value="anime">Anime</option>
                </select>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <option value="to_watch">To Watch</option>
                    <option value="watching">Watching</option>
                    <option value="completed">Completed</option>
                </select>
            </div>
            <div class="form-group">
                <label>Rating (1-5)</label>
                <input type="number" name="rating" min="1" max="5">
            </div>
            <button type="submit" class="btn btn-primary btn-block">Save</button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
