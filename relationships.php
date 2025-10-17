<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$relationships = $db->fetchAll("SELECT * FROM relationships WHERE user_id = ? ORDER BY last_interaction_date DESC", [$userId]) ?: [];

$pageTitle = 'Relationship Insights';
include 'includes/header.php';
?>

<div class="page-header">
    <h1><i class="fas fa-users"></i> AI Relationship Insights</h1>
    <p class="page-subtitle">Track and improve your relationships with AI-powered analysis</p>
</div>

<div class="action-bar">
    <button class="btn btn-primary" onclick="showAddRelationshipModal()">
        <i class="fas fa-plus"></i> Add Relationship
    </button>
    <button class="btn btn-secondary" onclick="analyzeRelationships()">
        <i class="fas fa-brain"></i> AI Analysis
    </button>
</div>

<div class="dashboard-grid">
    <div class="dashboard-card full-width">
        <div class="card-header">
            <h3><i class="fas fa-address-book"></i> Your Relationships</h3>
        </div>
        <div class="card-body">
            <?php if (empty($relationships)): ?>
            <p class="text-muted">No relationships tracked yet. Add your first relationship!</p>
            <?php else: ?>
            <?php foreach ($relationships as $rel): ?>
            <div class="relationship-card">
                <h4><?php echo sanitize($rel['contact_name']); ?></h4>
                <p><strong>Type:</strong> <?php echo ucfirst($rel['relationship_type']); ?></p>
                <p><strong>Last Contact:</strong> <?php echo date('M d, Y', strtotime($rel['last_interaction_date'])); ?></p>
                <div class="health-score">
                    Health Score: <strong><?php echo $rel['health_score']; ?>/100</strong>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<div id="addRelationshipModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class="fas fa-user-plus"></i> Add Relationship</h2>
            <button class="modal-close" onclick="closeModal('addRelationshipModal')">&times;</button>
        </div>
        <div class="modal-body">
            <form id="addRelationshipForm" onsubmit="addRelationship(event)">
                <div class="form-group">
                    <label>Contact Name</label>
                    <input type="text" name="contact_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Relationship Type</label>
                    <select name="relationship_type" class="form-control" required>
                        <option value="family">Family</option>
                        <option value="friend">Friend</option>
                        <option value="colleague">Colleague</option>
                        <option value="partner">Partner</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addRelationshipModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Relationship</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.relationship-card {
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 12px;
}

.health-score {
    margin-top: 12px;
    padding: 8px;
    background: rgba(34, 197, 94, 0.1);
    border-radius: 6px;
    color: var(--success);
}
</style>

<script>
function showAddRelationshipModal() {
    document.getElementById('addRelationshipModal').style.display = 'flex';
}

async function addRelationship(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    const data = Object.fromEntries(formData);
    
    try {
        const response = await fetch('/api/relationships.php?action=add', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('success', 'Success', 'Relationship added!');
            closeModal('addRelationshipModal');
            setTimeout(() => location.reload(), 1000);
        }
    } catch (error) {
        showToast('error', 'Error', 'Failed to add relationship');
    }
}

async function analyzeRelationships() {
    showToast('info', 'Processing', 'AI is analyzing your relationships...');
    
    try {
        const response = await fetch('/api/relationships.php?action=analyze');
        const result = await response.json();
        
        if (result.success) {
            showToast('success', 'Analysis Complete', 'Check the insights!');
        }
    } catch (error) {
        showToast('error', 'Error', 'Failed to analyze relationships');
    }
}
</script>

<?php include 'includes/footer.php'; ?>
