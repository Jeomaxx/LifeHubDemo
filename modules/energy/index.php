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

$pageTitle = 'Energy & Focus';
$activePage = 'energy';
include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="main-content">
    <div class="page-header">
        <h1><i class="fas fa-bolt"></i> Energy & Focus Manager</h1>
        <p>Track your energy levels and optimize your work rhythm</p>
    </div>

    <div class="energy-dashboard">
        <div class="quick-log">
            <h3>Quick Energy Log</h3>
            <form id="quickEnergyForm">
                <div class="slider-group">
                    <label>Energy Level</label>
                    <input type="range" name="energy_level" min="1" max="10" value="5" oninput="updateSliderValue(this, 'energyValue')">
                    <span id="energyValue">5</span>
                </div>
                <div class="slider-group">
                    <label>Focus Level</label>
                    <input type="range" name="focus_level" min="1" max="10" value="5" oninput="updateSliderValue(this, 'focusValue')">
                    <span id="focusValue">5</span>
                </div>
                <button type="submit" class="btn btn-primary">Log Now</button>
            </form>
        </div>

        <div class="work-rhythm">
            <h3><i class="fas fa-clock"></i> Your Work Rhythm</h3>
            <div id="peakHoursChart"></div>
            <div class="rhythm-insights" id="rhythmInsights"></div>
        </div>

        <div class="focus-sessions">
            <h3><i class="fas fa-stopwatch"></i> Focus Sessions</h3>
            <button class="btn btn-primary" onclick="startFocusSession()">
                <i class="fas fa-play"></i> Start Focus Session
            </button>
            <div id="focusSessionsContainer"></div>
        </div>

        <div class="energy-history">
            <h3><i class="fas fa-chart-line"></i> Energy Trends</h3>
            <canvas id="energyTrendsChart"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function updateSliderValue(slider, targetId) {
    document.getElementById(targetId).textContent = slider.value;
}

document.getElementById('quickEnergyForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch('api/log_energy.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Energy logged successfully', 'success');
            loadEnergyData();
        } else {
            showNotification(data.message || 'Failed to log energy', 'error');
        }
    });
});

function loadEnergyData() {
    fetch('api/get_energy_logs.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderEnergyChart(data.logs);
            }
        });
}

function loadWorkRhythm() {
    fetch('api/get_work_rhythm.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.insights) {
                displayWorkRhythm(data.insights);
            }
        });
}

function displayWorkRhythm(insights) {
    const container = document.getElementById('rhythmInsights');
    container.innerHTML = `
        <div class="insight-card">
            <h4>Peak Energy Hours</h4>
            <p>${insights.peak_hours || 'Building your profile...'}</p>
        </div>
        <div class="insight-card">
            <h4>Best Focus Times</h4>
            <p>${insights.focus_times || 'Keep logging to see patterns'}</p>
        </div>
        <div class="insight-card">
            <h4>Recommended Breaks</h4>
            <p>${insights.break_schedule || 'Data collecting...'}</p>
        </div>
    `;
}

function startFocusSession() {
    showNotification('Focus session started! Stay focused 🎯', 'success');
}

loadEnergyData();
loadWorkRhythm();
</script>

<style>
.energy-dashboard {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-top: 20px;
}

.quick-log, .work-rhythm, .focus-sessions, .energy-history {
    background: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.slider-group {
    margin: 15px 0;
}

.slider-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
}

.slider-group input[type="range"] {
    width: 100%;
    margin-right: 10px;
}

.slider-group span {
    display: inline-block;
    width: 30px;
    text-align: center;
    font-weight: bold;
    color: #667eea;
}

.rhythm-insights {
    display: grid;
    gap: 10px;
    margin-top: 15px;
}

.insight-card {
    background: #f5f5f5;
    padding: 12px;
    border-radius: 6px;
}

.insight-card h4 {
    margin-bottom: 8px;
    color: #333;
}

@media (max-width: 768px) {
    .energy-dashboard {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include '../../includes/footer.php'; ?>
