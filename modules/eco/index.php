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

$pageTitle = 'Eco Tracker';
$activePage = 'eco';
include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="main-content">
    <div class="page-header">
        <h1><i class="fas fa-leaf"></i> Sustainability & Eco Tracker</h1>
        <p>Track your environmental impact and build sustainable habits</p>
    </div>

    <div class="eco-dashboard">
        <div class="eco-score-card">
            <h2>Your Eco Score</h2>
            <div class="eco-score" id="ecoScore">75</div>
            <p>Keep it up! You're doing great for the planet 🌍</p>
        </div>

        <div class="impact-summary">
            <div class="impact-card">
                <i class="fas fa-cloud"></i>
                <h4>Carbon Footprint</h4>
                <div class="impact-value" id="carbonFootprint">0 kg CO2</div>
            </div>
            <div class="impact-card">
                <i class="fas fa-tint"></i>
                <h4>Water Usage</h4>
                <div class="impact-value" id="waterUsage">0 L</div>
            </div>
            <div class="impact-card">
                <i class="fas fa-bolt"></i>
                <h4>Energy Usage</h4>
                <div class="impact-value" id="energyUsage">0 kWh</div>
            </div>
            <div class="impact-card">
                <i class="fas fa-trash"></i>
                <h4>Waste Generated</h4>
                <div class="impact-value" id="wasteGenerated">0 kg</div>
            </div>
        </div>

        <div class="eco-log">
            <h3>Log Impact</h3>
            <form id="ecoLogForm">
                <select name="impact_category" required>
                    <option value="">Select category...</option>
                    <option value="transport">Transportation</option>
                    <option value="energy">Energy Use</option>
                    <option value="waste">Waste</option>
                    <option value="water">Water</option>
                    <option value="food">Food</option>
                    <option value="purchases">Purchases</option>
                </select>
                <input type="text" name="activity_description" placeholder="Activity description" required>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Log Activity
                </button>
            </form>
        </div>

        <div class="eco-tips">
            <h3><i class="fas fa-lightbulb"></i> Daily Eco Tips</h3>
            <div id="ecoTipsContainer"></div>
        </div>

        <div class="eco-goals">
            <h3><i class="fas fa-flag-checkered"></i> Sustainability Goals</h3>
            <button class="btn btn-primary" onclick="showAddGoalModal()">
                <i class="fas fa-plus"></i> Add Goal
            </button>
            <div id="ecoGoalsContainer"></div>
        </div>
    </div>
</div>

<script>
document.getElementById('ecoLogForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch('api/log_impact.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Impact logged successfully', 'success');
            loadEcoData();
            this.reset();
        } else {
            showNotification(data.message || 'Failed to log impact', 'error');
        }
    });
});

function loadEcoData() {
    fetch('api/get_eco_summary.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('ecoScore').textContent = data.summary.eco_score || 75;
                document.getElementById('carbonFootprint').textContent = (data.summary.carbon_footprint || 0) + ' kg CO2';
                document.getElementById('waterUsage').textContent = (data.summary.water_usage || 0) + ' L';
                document.getElementById('energyUsage').textContent = (data.summary.energy_usage || 0) + ' kWh';
                document.getElementById('wasteGenerated').textContent = (data.summary.waste_generated || 0) + ' kg';
            }
        });
}

function loadEcoTips() {
    const tips = [
        'Use reusable bags when shopping',
        'Turn off lights when leaving a room',
        'Reduce meat consumption once a week',
        'Use public transportation or bike',
        'Reduce water usage by taking shorter showers'
    ];

    const container = document.getElementById('ecoTipsContainer');
    container.innerHTML = tips.map(tip => `
        <div class="tip-card">
            <p>${tip}</p>
        </div>
    `).join('');
}

loadEcoData();
loadEcoTips();
</script>

<style>
.eco-dashboard {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin-top: 20px;
}

.eco-score-card {
    background: linear-gradient(135deg, #4CAF50 0%, #8BC34A 100%);
    color: white;
    border-radius: 8px;
    padding: 30px;
    text-align: center;
    grid-column: 1 / -1;
}

.eco-score {
    font-size: 72px;
    font-weight: bold;
    margin: 20px 0;
}

.impact-summary {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 15px;
    grid-column: 1 / -1;
}

.impact-card {
    background: white;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.impact-card i {
    font-size: 32px;
    color: #4CAF50;
    margin-bottom: 10px;
}

.impact-value {
    font-size: 20px;
    font-weight: bold;
    color: #333;
    margin-top: 10px;
}

.eco-log, .eco-tips, .eco-goals {
    background: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.eco-log form {
    display: flex;
    gap: 10px;
    margin-top: 15px;
}

.eco-log select, .eco-log input {
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 6px;
}

.tip-card {
    background: #E8F5E9;
    padding: 12px;
    border-radius: 6px;
    margin-bottom: 10px;
    border-left: 4px solid #4CAF50;
}

@media (max-width: 768px) {
    .impact-summary {
        grid-template-columns: repeat(2, 1fr);
    }
    .eco-dashboard {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include '../../includes/footer.php'; ?>
