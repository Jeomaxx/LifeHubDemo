<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$predictions = $db->fetchAll("SELECT * FROM life_event_predictions WHERE user_id = ? AND is_confirmed = FALSE ORDER BY predicted_date ASC LIMIT 10", [$userId]) ?: [];

$pageTitle = 'Life Event Predictor';
include 'includes/header.php';
?>

<div class="page-header">
    <h1><i class="fas fa-crystal-ball"></i> Life Event Predictor</h1>
    <p class="page-subtitle">AI-powered predictions for upcoming life moments</p>
</div>

<div class="action-bar">
    <button class="btn btn-primary" onclick="generatePredictions()">
        <i class="fas fa-magic"></i> Generate AI Predictions
    </button>
</div>

<div class="dashboard-grid">
    <div class="dashboard-card full-width">
        <div class="card-header">
            <h3><i class="fas fa-calendar-alt"></i> Predicted Life Events</h3>
        </div>
        <div class="card-body">
            <div id="predictions-container">
                <?php if (empty($predictions)): ?>
                <p class="text-muted">No predictions yet. Generate AI predictions to see upcoming events!</p>
                <?php else: ?>
                <?php foreach ($predictions as $pred): ?>
                <div class="prediction-card">
                    <div class="prediction-header">
                        <h4><?php echo sanitize($pred['event_title']); ?></h4>
                        <span class="badge badge-<?php echo $pred['impact_level']; ?>"><?php echo ucfirst($pred['impact_level']); ?> Impact</span>
                    </div>
                    <p><strong>Predicted Date:</strong> <?php echo date('M d, Y', strtotime($pred['predicted_date'])); ?></p>
                    <p><strong>Confidence:</strong> <?php echo $pred['confidence_score']; ?>%</p>
                    <?php if ($pred['preventive_actions']): ?>
                    <div class="preventive-actions">
                        <strong>Suggested Actions:</strong>
                        <p><?php echo sanitize($pred['preventive_actions']); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.prediction-card {
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 12px;
}

.prediction-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}

.preventive-actions {
    background: rgba(59, 130, 246, 0.1);
    padding: 12px;
    border-radius: 6px;
    margin-top: 12px;
}
</style>

<script>
async function generatePredictions() {
    showToast('info', 'Processing', 'AI is analyzing your data to predict life events...');
    
    try {
        const response = await fetch('/api/life_events.php?action=predict', { method: 'POST' });
        const result = await response.json();
        
        if (result.success) {
            showToast('success', 'Success', 'Predictions generated successfully!');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast('error', 'Error', result.message || 'Failed to generate predictions');
        }
    } catch (error) {
        console.error('Error generating predictions:', error);
        showToast('error', 'Error', 'Failed to generate predictions');
    }
}
</script>

<?php include 'includes/footer.php'; ?>
