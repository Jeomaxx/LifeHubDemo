<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    redirect('/login.php');
}

$db = getDB();
$userId = $auth->getUserId();

$pageTitle = 'Shared Spaces';
$activePage = 'collaboration';
include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="main-content">
    <div class="page-header">
        <h1><i class="fas fa-users"></i> Collaboration & Shared Spaces</h1>
        <p>Collaborate with family, friends, and team members</p>
    </div>

    <div class="collaboration-controls">
        <button class="btn btn-primary" onclick="showCreateSpaceModal()">
            <i class="fas fa-plus"></i> Create Shared Space
        </button>
    </div>

    <div class="spaces-grid" id="spacesGrid"></div>
</div>

<div id="createSpaceModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Create Shared Space</h2>
            <button class="close-modal" onclick="closeModal('createSpaceModal')">&times;</button>
        </div>
        <div class="modal-body">
            <form id="createSpaceForm">
                <div class="form-group">
                    <label>Space Name</label>
                    <input type="text" name="name" required>
                </div>
                
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label>Space Type</label>
                    <select name="space_type" required>
                        <option value="family">Family</option>
                        <option value="team">Team</option>
                        <option value="project">Project</option>
                        <option value="friends">Friends</option>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Create Space</button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal('createSpaceModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function loadSharedSpaces() {
    fetch('api/get_spaces.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displaySpaces(data.spaces);
            }
        });
}

function displaySpaces(spaces) {
    const container = document.getElementById('spacesGrid');
    if (spaces.length === 0) {
        container.innerHTML = '<p class="no-data">No shared spaces yet. Create your first space!</p>';
        return;
    }

    container.innerHTML = spaces.map(space => `
        <div class="space-card">
            <div class="space-header">
                <h3>${escapeHtml(space.name)}</h3>
                <span class="space-type">${space.space_type}</span>
            </div>
            <p>${escapeHtml(space.description || '')}</p>
            <div class="space-footer">
                <button class="btn btn-sm" onclick="openSpace(${space.id})">Open</button>
            </div>
        </div>
    `).join('');
}

function showCreateSpaceModal() {
    document.getElementById('createSpaceModal').style.display = 'flex';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

document.getElementById('createSpaceForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch('api/create_space.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Shared space created successfully', 'success');
            closeModal('createSpaceModal');
            loadSharedSpaces();
            this.reset();
        } else {
            showNotification(data.message || 'Failed to create space', 'error');
        }
    });
});

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

loadSharedSpaces();
</script>

<style>
.spaces-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.space-card {
    background: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.space-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.space-type {
    background: #667eea;
    color: white;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    text-transform: capitalize;
}

.space-footer {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #eee;
}
</style>

<?php include '../../includes/footer.php'; ?>
