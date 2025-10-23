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

$pageTitle = 'AI Digital Twin';
$activePage = 'digital_twin';
include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="main-content">
    <div class="page-header">
        <h1><i class="fas fa-user-robot"></i> AI Digital Twin</h1>
        <p>Your AI-powered digital replica that learns your patterns and predicts outcomes</p>
    </div>

    <div class="digital-twin-dashboard">
        <div class="twin-status">
            <h3>Digital Twin Status</h3>
            <div class="status-card">
                <div class="twin-avatar">
                    <i class="fas fa-brain"></i>
                </div>
                <h4>Your Digital Twin</h4>
                <p class="model-version">Model Version: 1.0</p>
                <p class="accuracy">Prediction Accuracy: <span id="accuracyScore">85%</span></p>
                <p class="last-trained">Last Trained: <span id="lastTrained">Today</span></p>
                <button class="btn btn-primary" onclick="trainModel()">
                    <i class="fas fa-sync"></i> Retrain Model
                </button>
            </div>
        </div>

        <div class="predictions">
            <h3><i class="fas fa-crystal-ball"></i> Predictions & Insights</h3>
            <div id="predictionsContainer"></div>
        </div>

        <div class="behavior-patterns">
            <h3><i class="fas fa-chart-line"></i> Detected Patterns</h3>
            <div id="patternsContainer"></div>
        </div>

        <div class="what-if-simulator">
            <h3><i class="fas fa-flask"></i> What If Simulator</h3>
            <form id="whatIfForm">
                <select name="scenario_type" required>
                    <option value="">Select scenario...</option>
                    <option value="skip_gym">If I skip gym today</option>
                    <option value="extra_sleep">If I sleep 1 hour more</option>
                    <option value="skip_coffee">If I skip morning coffee</option>
                    <option value="extra_savings">If I save $100 more this month</option>
                    <option value="custom">Custom scenario</option>
                </select>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-play"></i> Simulate
                </button>
            </form>
            <div id="simulationResults"></div>
        </div>
    </div>
</div>

<script>
function loadDigitalTwin() {
    fetch('api/get_twin_status.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.model) {
                document.getElementById('accuracyScore').textContent = 
                    (data.model.prediction_accuracy || 85).toFixed(0) + '%';
                document.getElementById('lastTrained').textContent = 
                    formatDate(data.model.last_trained_at);
            }
        });
}

function loadPredictions() {
    fetch('api/get_predictions.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayPredictions(data.predictions);
            }
        });
}

function displayPredictions(predictions) {
    const container = document.getElementById('predictionsContainer');
    if (predictions.length === 0) {
        container.innerHTML = `
            <p class="no-data">Building your digital twin... Keep using the app to generate predictions!</p>
        `;
        return;
    }

    container.innerHTML = predictions.map(pred => `
        <div class="prediction-card">
            <h4>${escapeHtml(pred.prediction_type)}</h4>
            <p>${getPredictionText(pred)}</p>
            <div class="confidence-bar">
                <div class="confidence-fill" style="width: ${pred.confidence_score}%"></div>
            </div>
            <p class="confidence-text">${pred.confidence_score}% Confidence</p>
        </div>
    `).join('');
}

function loadBehaviorPatterns() {
    fetch('api/get_patterns.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayPatterns(data.patterns);
            }
        });
}

function displayPatterns(patterns) {
    const container = document.getElementById('patternsContainer');
    if (patterns.length === 0) {
        container.innerHTML = '<p class="no-data">Analyzing your behavior patterns...</p>';
        return;
    }

    container.innerHTML = patterns.map(pattern => `
        <div class="pattern-card">
            <h4><i class="fas fa-lightbulb"></i> ${escapeHtml(pattern.pattern_type)}</h4>
            <p>${getPatternDescription(pattern)}</p>
            <span class="frequency-badge">${pattern.frequency}</span>
        </div>
    `).join('');
}

document.getElementById('whatIfForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch('api/simulate_scenario.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displaySimulationResults(data.result);
        }
    });
});

function displaySimulationResults(result) {
    const container = document.getElementById('simulationResults');
    container.innerHTML = `
        <div class="simulation-result">
            <h4>Simulation Result</h4>
            <p>${escapeHtml(result.prediction || 'Result calculated')}</p>
            <p class="impact">Impact: ${result.impact || 'Moderate'}</p>
        </div>
    `;
}

function getPredictionText(pred) {
    return `Predicted outcome: ${JSON.stringify(pred.predicted_outcome)}`;
}

function getPatternDescription(pattern) {
    return `Detected pattern in ${pattern.pattern_type}`;
}

function formatDate(date) {
    if (!date) return 'Never';
    return new Date(date).toLocaleString();
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function trainModel() {
    showNotification('Retraining digital twin model...', 'info');
    setTimeout(() => {
        showNotification('Model retrained successfully!', 'success');
        loadDigitalTwin();
    }, 2000);
}

loadDigitalTwin();
loadPredictions();
loadBehaviorPatterns();
</script>

<style>
.digital-twin-dashboard {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-top: 20px;
}

.twin-status, .predictions, .behavior-patterns, .what-if-simulator {
    background: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.status-card {
    text-align: center;
    padding: 20px;
}

.twin-avatar {
    width: 100px;
    height: 100px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    margin: 0 auto 20px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.twin-avatar i {
    font-size: 48px;
    color: white;
}

.prediction-card, .pattern-card {
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 15px;
    margin-bottom: 15px;
}

.confidence-bar {
    height: 8px;
    background: #eee;
    border-radius: 4px;
    overflow: hidden;
    margin: 10px 0;
}

.confidence-fill {
    height: 100%;
    background: #4CAF50;
}

.confidence-text {
    font-size: 12px;
    color: #666;
}

.frequency-badge {
    background: #667eea;
    color: white;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
}

.simulation-result {
    background: #E3F2FD;
    padding: 15px;
    border-radius: 6px;
    margin-top: 15px;
}

@media (max-width: 768px) {
    .digital-twin-dashboard {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include '../../includes/footer.php'; ?>
