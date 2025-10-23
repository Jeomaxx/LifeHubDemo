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

$pageTitle = 'Life Analytics';
$activePage = 'analytics';
include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="main-content">
    <div class="page-header">
        <h1><i class="fas fa-chart-line"></i> Life Analytics & Insight Engine</h1>
        <p>Comprehensive analysis of your life metrics and patterns</p>
    </div>

    <div class="analytics-dashboard">
        <div class="life-balance-score">
            <h2>Life Balance Score</h2>
            <div class="score-circle" id="lifeBalanceScore">
                <div class="score-value">--</div>
                <p>Overall Balance</p>
            </div>
        </div>

        <div class="score-breakdown">
            <div class="score-card health">
                <i class="fas fa-heartbeat"></i>
                <h3>Health</h3>
                <div class="score" id="healthScore">--</div>
            </div>
            <div class="score-card finance">
                <i class="fas fa-dollar-sign"></i>
                <h3>Finance</h3>
                <div class="score" id="financeScore">--</div>
            </div>
            <div class="score-card productivity">
                <i class="fas fa-tasks"></i>
                <h3>Productivity</h3>
                <div class="score" id="productivityScore">--</div>
            </div>
            <div class="score-card mood">
                <i class="fas fa-smile"></i>
                <h3>Mood</h3>
                <div class="score" id="moodScore">--</div>
            </div>
        </div>

        <div class="analytics-tabs">
            <button class="tab-btn active" onclick="showTab('reports')">Weekly Reports</button>
            <button class="tab-btn" onclick="showTab('correlations')">Correlations</button>
            <button class="tab-btn" onclick="showTab('insights')">AI Insights</button>
            <button class="tab-btn" onclick="showTab('export')">Export Data</button>
        </div>

        <div id="reports-tab" class="tab-content active">
            <h3>Life Reports</h3>
            <div id="reportsContainer"></div>
        </div>

        <div id="correlations-tab" class="tab-content">
            <h3>Data Correlations</h3>
            <div class="correlation-grid">
                <canvas id="sleepProductivityChart"></canvas>
                <canvas id="spendingStressChart"></canvas>
            </div>
        </div>

        <div id="insights-tab" class="tab-content">
            <h3>AI-Generated Insights</h3>
            <div id="insightsContainer"></div>
        </div>

        <div id="export-tab" class="tab-content">
            <h3>Export Analytics</h3>
            <button class="btn btn-primary" onclick="exportToPDF()">
                <i class="fas fa-file-pdf"></i> Export as PDF
            </button>
            <button class="btn btn-secondary" onclick="exportToExcel()">
                <i class="fas fa-file-excel"></i> Export as Excel
            </button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function loadLifeBalance() {
    fetch('api/get_life_balance.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('lifeBalanceScore').querySelector('.score-value').textContent = 
                    data.balance.overall_score.toFixed(1);
                document.getElementById('healthScore').textContent = data.balance.health_score.toFixed(1);
                document.getElementById('financeScore').textContent = data.balance.finance_score.toFixed(1);
                document.getElementById('productivityScore').textContent = data.balance.productivity_score.toFixed(1);
                document.getElementById('moodScore').textContent = data.balance.mood_score.toFixed(1);
            }
        });
}

function loadReports() {
    fetch('api/get_reports.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayReports(data.reports);
            }
        });
}

function showTab(tabName) {
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    document.getElementById(tabName + '-tab').classList.add('active');
    event.target.classList.add('active');
}

loadLifeBalance();
loadReports();
</script>

<style>
.analytics-dashboard {
    padding: 20px;
}

.life-balance-score {
    text-align: center;
    margin: 30px 0;
}

.score-circle {
    width: 200px;
    height: 200px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    color: white;
}

.score-value {
    font-size: 48px;
    font-weight: bold;
}

.score-breakdown {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin: 30px 0;
}

.score-card {
    background: white;
    padding: 20px;
    border-radius: 8px;
    text-align: center;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.score-card i {
    font-size: 32px;
    margin-bottom: 10px;
}

.score-card .score {
    font-size: 36px;
    font-weight: bold;
    margin-top: 10px;
}

.analytics-tabs {
    display: flex;
    gap: 10px;
    margin: 30px 0;
    border-bottom: 2px solid #ddd;
}

.tab-btn {
    padding: 10px 20px;
    background: none;
    border: none;
    cursor: pointer;
    font-size: 16px;
    position: relative;
}

.tab-btn.active {
    color: #667eea;
    font-weight: bold;
}

.tab-btn.active::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    right: 0;
    height: 2px;
    background: #667eea;
}

.tab-content {
    display: none;
    padding: 20px 0;
}

.tab-content.active {
    display: block;
}

.correlation-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

@media (max-width: 768px) {
    .score-breakdown {
        grid-template-columns: repeat(2, 1fr);
    }
    .correlation-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include '../../includes/footer.php'; ?>
