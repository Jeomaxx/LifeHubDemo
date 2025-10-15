<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$financeByMonth = $db->fetchAll("
    SELECT 
        TO_CHAR(date, 'YYYY-MM') as month,
        type,
        SUM(amount) as total
    FROM finance 
    WHERE user_id = ?
    AND date >= CURRENT_DATE - INTERVAL '12 months'
    GROUP BY month, type
    ORDER BY month
", [$userId]);

$billsByStatus = $db->fetchAll("
    SELECT status, COUNT(*) as count 
    FROM bills 
    WHERE user_id = ? 
    GROUP BY status
", [$userId]);

$goalsByStatus = $db->fetchAll("
    SELECT status, COUNT(*) as count 
    FROM goals 
    WHERE user_id = ? 
    GROUP BY status
", [$userId]);

$habitCompletion = $db->fetchAll("
    SELECT h.name, COUNT(hl.id) as completions
    FROM habits h
    LEFT JOIN habit_logs hl ON h.id = hl.habit_id
    WHERE h.user_id = ?
    GROUP BY h.id, h.name
    ORDER BY completions DESC
    LIMIT 10
", [$userId]);

$assetsByCategory = $db->fetchAll("
    SELECT category, COUNT(*) as count, SUM(value) as total_value
    FROM assets
    WHERE user_id = ?
    GROUP BY category
", [$userId]);

$subscriptionsCost = $db->fetchAll("
    SELECT name, cost, billing_cycle
    FROM subscriptions
    WHERE user_id = ? AND status = 'active'
    ORDER BY cost DESC
", [$userId]);

$journalMoods = $db->fetchAll("
    SELECT mood, COUNT(*) as count
    FROM journal
    WHERE user_id = ? AND mood IS NOT NULL
    GROUP BY mood
", [$userId]);

$learningProgress = $db->fetchAll("
    SELECT title, progress, type
    FROM learning
    WHERE user_id = ?
    ORDER BY progress DESC
    LIMIT 10
", [$userId]);

$healthData = $db->fetchAll("
    SELECT date, weight, exercise_minutes, water_intake, sleep_hours
    FROM health
    WHERE user_id = ?
    AND date >= CURRENT_DATE - INTERVAL '30 days'
    ORDER BY date
", [$userId]);

$investmentPerformance = $db->fetchAll("
    SELECT name, COALESCE(current_value, 0) as current_value, COALESCE(invested_amount, 0) as invested_amount
    FROM investments
    WHERE user_id = ?
", [$userId]);

$pageTitle = 'Analytics Dashboard';
$extraScripts = ['/assets/js/analytics.js'];
include 'includes/header.php';
?>

<div class="page-header">
    <h1><i class="fas fa-chart-bar"></i> Analytics Dashboard</h1>
    <p class="page-subtitle">Comprehensive insights into your life data</p>
</div>

<div class="dashboard-grid">
    <div class="dashboard-card full-width">
        <div class="card-header">
            <h3><i class="fas fa-chart-line"></i> Finance Trends (Last 12 Months)</h3>
        </div>
        <div class="card-body">
            <canvas id="financeChart" height="80"></canvas>
        </div>
    </div>
    
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-file-invoice-dollar"></i> Bills Status</h3>
        </div>
        <div class="card-body">
            <canvas id="billsChart"></canvas>
        </div>
    </div>
    
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-bullseye"></i> Goals Progress</h3>
        </div>
        <div class="card-body">
            <canvas id="goalsChart"></canvas>
        </div>
    </div>
    
    <div class="dashboard-card full-width">
        <div class="card-header">
            <h3><i class="fas fa-check-circle"></i> Top 10 Habits Completion</h3>
        </div>
        <div class="card-body">
            <canvas id="habitsChart" height="80"></canvas>
        </div>
    </div>
    
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-box"></i> Assets by Category</h3>
        </div>
        <div class="card-body">
            <canvas id="assetsChart"></canvas>
        </div>
    </div>
    
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-credit-card"></i> Monthly Subscriptions</h3>
        </div>
        <div class="card-body">
            <div class="subscriptions-list">
                <?php 
                $totalMonthlyCost = 0;
                foreach ($subscriptionsCost as $sub): 
                    $monthlyCost = $sub['billing_cycle'] === 'yearly' ? $sub['cost'] / 12 : $sub['cost'];
                    $totalMonthlyCost += $monthlyCost;
                ?>
                <div class="subscription-item">
                    <span class="sub-name"><?php echo sanitize($sub['name']); ?></span>
                    <span class="sub-cost">$<?php echo number_format($monthlyCost, 2); ?>/mo</span>
                </div>
                <?php endforeach; ?>
                <div class="subscription-total">
                    <strong>Total Monthly Cost:</strong>
                    <strong class="text-primary">$<?php echo number_format($totalMonthlyCost, 2); ?></strong>
                </div>
            </div>
        </div>
    </div>
    
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-smile"></i> Journal Mood Distribution</h3>
        </div>
        <div class="card-body">
            <canvas id="moodChart"></canvas>
        </div>
    </div>
    
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-graduation-cap"></i> Learning Progress</h3>
        </div>
        <div class="card-body">
            <canvas id="learningChart"></canvas>
        </div>
    </div>
    
    <div class="dashboard-card full-width">
        <div class="card-header">
            <h3><i class="fas fa-heartbeat"></i> Health Tracking (Last 30 Days)</h3>
        </div>
        <div class="card-body">
            <canvas id="healthChart" height="80"></canvas>
        </div>
    </div>
    
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-chart-pie"></i> Investment Performance</h3>
        </div>
        <div class="card-body">
            <canvas id="investmentChart"></canvas>
        </div>
    </div>
    
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-download"></i> Data Management</h3>
        </div>
        <div class="card-body">
            <div class="data-management">
                <button class="btn btn-primary" onclick="exportData('json')">
                    <i class="fas fa-file-export"></i> Export as JSON
                </button>
                <button class="btn btn-secondary" onclick="exportData('csv')">
                    <i class="fas fa-file-csv"></i> Export as CSV
                </button>
                <button class="btn btn-info" onclick="document.getElementById('importFile').click()">
                    <i class="fas fa-file-import"></i> Import Data
                </button>
                <input type="file" id="importFile" accept=".json" style="display:none" onchange="importData(this)">
            </div>
        </div>
    </div>
</div>

<style>
.full-width {
    grid-column: 1 / -1;
}

.subscriptions-list {
    max-height: 400px;
    overflow-y: auto;
}

.subscription-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px;
    border-bottom: 1px solid var(--border);
    transition: background 0.2s;
}

.subscription-item:hover {
    background: var(--light);
}

.sub-name {
    font-weight: 500;
    color: var(--text);
}

.sub-cost {
    color: var(--primary);
    font-weight: 600;
}

.subscription-total {
    display: flex;
    justify-content: space-between;
    padding: 16px 12px;
    border-top: 2px solid var(--primary);
    margin-top: 8px;
    font-size: 1.1rem;
}
</style>

<script>
const financeData = <?php echo json_encode($financeByMonth); ?>;
const billsData = <?php echo json_encode($billsByStatus); ?>;
const goalsData = <?php echo json_encode($goalsByStatus); ?>;
const habitsData = <?php echo json_encode($habitCompletion); ?>;
const assetsData = <?php echo json_encode($assetsByCategory); ?>;
const moodData = <?php echo json_encode($journalMoods); ?>;
const learningData = <?php echo json_encode($learningProgress); ?>;
const healthData = <?php echo json_encode($healthData); ?>;
const investmentData = <?php echo json_encode($investmentPerformance); ?>;

function exportData(format) {
    showToast('Preparing export...', 'info');
    window.location.href = `/api/export.php?format=${format}`;
}

async function importData(input) {
    const file = input.files[0];
    if (!file) return;
    
    const reader = new FileReader();
    reader.onload = async function(e) {
        try {
            const data = JSON.parse(e.target.result);
            
            const response = await fetch('/api/import.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (result.success) {
                showToast(result.message, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(result.message, 'error');
            }
        } catch (error) {
            showToast('Failed to import data', 'error');
        }
    };
    reader.readAsText(file);
}
</script>

<?php include 'includes/footer.php'; ?>
