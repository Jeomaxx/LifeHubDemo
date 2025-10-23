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

$pageTitle = 'Scenario Simulator';
$activePage = 'scenario';
include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="main-content">
    <div class="page-header">
        <h1><i class="fas fa-flask"></i> AI Scenario Simulator</h1>
        <p>Explore "what if" scenarios and predict outcomes with AI</p>
    </div>

    <div class="scenario-dashboard">
        <div class="create-scenario">
            <h3>Create New Scenario</h3>
            <form id="scenarioForm">
                <div class="form-group">
                    <label>Scenario Name</label>
                    <input type="text" name="simulation_name" required>
                </div>
                
                <div class="form-group">
                    <label>What If Question</label>
                    <textarea name="what_if_question" rows="3" placeholder="e.g., What if I invest $500 monthly for 10 years?" required></textarea>
                </div>

                <div class="form-group">
                    <label>Scenario Type</label>
                    <select name="simulation_type" required>
                        <option value="financial">Financial</option>
                        <option value="health">Health</option>
                        <option value="career">Career</option>
                        <option value="lifestyle">Lifestyle</option>
                        <option value="mixed">Mixed</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Simulation Period (months)</label>
                    <input type="number" name="simulation_period" min="1" value="12" required>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-play"></i> Run Simulation
                </button>
            </form>
        </div>

        <div class="scenarios-list">
            <h3>Your Scenarios</h3>
            <div id="scenariosContainer"></div>
        </div>

        <div class="scenario-results" id="resultsPanel" style="display: none;">
            <h3>Simulation Results</h3>
            <div id="resultsContent"></div>
            <canvas id="impactChart"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.getElementById('scenarioForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    showNotification('Running simulation...', 'info');
    
    fetch('api/run_simulation.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Simulation completed successfully', 'success');
            displaySimulationResults(data.simulation);
            loadScenarios();
            this.reset();
        } else {
            showNotification(data.message || 'Simulation failed', 'error');
        }
    });
});

function loadScenarios() {
    fetch('api/get_scenarios.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayScenarios(data.scenarios);
            }
        });
}

function displayScenarios(scenarios) {
    const container = document.getElementById('scenariosContainer');
    if (scenarios.length === 0) {
        container.innerHTML = '<p class="no-data">No scenarios yet. Create your first simulation!</p>';
        return;
    }

    container.innerHTML = scenarios.map(scenario => `
        <div class="scenario-card">
            <h4>${escapeHtml(scenario.simulation_name)}</h4>
            <p class="what-if">${escapeHtml(scenario.what_if_question)}</p>
            <div class="scenario-meta">
                <span class="type-badge ${scenario.simulation_type}">${scenario.simulation_type}</span>
                <span class="confidence">Confidence: ${parseFloat(scenario.confidence_level || 80).toFixed(0)}%</span>
            </div>
            <button class="btn btn-sm" onclick="viewScenario(${scenario.id})">View Results</button>
        </div>
    `).join('');
}

function displaySimulationResults(simulation) {
    const resultsPanel = document.getElementById('resultsPanel');
    const resultsContent = document.getElementById('resultsContent');
    
    resultsPanel.style.display = 'block';
    
    resultsContent.innerHTML = `
        <div class="result-summary">
            <h4>${escapeHtml(simulation.simulation_name)}</h4>
            <p><strong>Question:</strong> ${escapeHtml(simulation.what_if_question)}</p>
            <p><strong>Confidence Level:</strong> ${parseFloat(simulation.confidence_level || 80).toFixed(0)}%</p>
            ${simulation.recommendation ? `
                <div class="recommendation">
                    <i class="fas fa-lightbulb"></i>
                    <strong>Recommendation:</strong> ${escapeHtml(simulation.recommendation)}
                </div>
            ` : ''}
        </div>
    `;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function viewScenario(id) {
    fetch(`api/get_scenario.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displaySimulationResults(data.scenario);
            }
        });
}

loadScenarios();
</script>

<style>
.scenario-dashboard {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-top: 20px;
}

.create-scenario, .scenarios-list, .scenario-results {
    background: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.scenario-results {
    grid-column: 1 / -1;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
}

.form-group input, .form-group textarea, .form-group select {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 6px;
}

.scenario-card {
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 15px;
    margin-bottom: 15px;
}

.what-if {
    font-style: italic;
    color: #666;
    margin: 10px 0;
}

.scenario-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 10px 0;
}

.type-badge {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    text-transform: capitalize;
}

.type-badge.financial { background: #4CAF50; color: white; }
.type-badge.health { background: #FF5722; color: white; }
.type-badge.career { background: #2196F3; color: white; }
.type-badge.lifestyle { background: #9C27B0; color: white; }
.type-badge.mixed { background: #FFC107; color: white; }

.confidence {
    font-size: 14px;
    font-weight: bold;
    color: #667eea;
}

.recommendation {
    background: #E3F2FD;
    padding: 15px;
    border-radius: 6px;
    margin-top: 15px;
}

@media (max-width: 768px) {
    .scenario-dashboard {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include '../../includes/footer.php'; ?>
