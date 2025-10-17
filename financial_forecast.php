<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$forecasts = $db->fetchAll("SELECT * FROM financial_forecasts WHERE user_id = ? ORDER BY created_at DESC LIMIT 5", [$userId]) ?: [];
$upcomingBills = $db->fetchAll("SELECT * FROM bills WHERE user_id = ? AND due_date >= CURRENT_DATE ORDER BY due_date LIMIT 10", [$userId]) ?: [];

$pageTitle = 'AI Financial Forecasting';
$extraScripts = ['/assets/js/financial_forecast.js'];
include 'includes/header.php';
?>

<div class="page-header">
    <h1><i class="fas fa-crystal-ball"></i> AI Financial Forecasting</h1>
    <p class="page-subtitle">AI-powered predictions for smarter financial decisions</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon bg-blue">
            <i class="fas fa-chart-line"></i>
        </div>
        <div class="stat-info">
            <h3 id="forecast-balance">Generate Forecast</h3>
            <p>Predicted Balance</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-green">
            <i class="fas fa-money-bill-wave"></i>
        </div>
        <div class="stat-info">
            <h3 id="forecast-income">-</h3>
            <p>Predicted Income</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-red">
            <i class="fas fa-receipt"></i>
        </div>
        <div class="stat-info">
            <h3 id="forecast-expenses">-</h3>
            <p>Predicted Expenses</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-purple">
            <i class="fas fa-piggy-bank"></i>
        </div>
        <div class="stat-info">
            <h3 id="forecast-savings">-</h3>
            <p>Suggested Savings</p>
        </div>
    </div>
</div>

<div class="action-bar">
    <button class="btn btn-primary" onclick="showForecastModal()">
        <i class="fas fa-magic"></i> Generate AI Forecast
    </button>
    <button class="btn btn-success" onclick="exportForecast()">
        <i class="fas fa-download"></i> Export to CSV
    </button>
</div>

<div class="dashboard-grid">
    <div class="dashboard-card full-width">
        <div class="card-header">
            <h3><i class="fas fa-chart-area"></i> Financial Forecast Chart</h3>
        </div>
        <div class="card-body">
            <canvas id="forecastChart"></canvas>
        </div>
    </div>
    
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-file-invoice-dollar"></i> Upcoming Bills Summary</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Bill Name</th>
                            <th>Amount</th>
                            <th>Due Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($upcomingBills)): ?>
                        <tr>
                            <td colspan="3" style="text-align: center; padding: 20px;">No upcoming bills</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($upcomingBills as $bill): ?>
                        <tr>
                            <td><?php echo sanitize($bill['name']); ?></td>
                            <td>$<?php echo number_format($bill['amount'], 2); ?></td>
                            <td><?php echo date('M d, Y', strtotime($bill['due_date'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-exclamation-triangle"></i> Financial Risk Alerts</h3>
        </div>
        <div class="card-body">
            <div id="risk-alerts">
                <p class="text-muted">Generate a forecast to see risk alerts</p>
            </div>
        </div>
    </div>
    
    <div class="dashboard-card full-width">
        <div class="card-header">
            <h3><i class="fas fa-lightbulb"></i> AI Recommendations</h3>
        </div>
        <div class="card-body">
            <div id="recommendations">
                <p class="text-muted">Generate a forecast to see personalized recommendations</p>
            </div>
        </div>
    </div>
</div>

<div id="forecastModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class="fas fa-magic"></i> Generate Financial Forecast</h2>
            <button class="modal-close" onclick="closeModal('forecastModal')">&times;</button>
        </div>
        <div class="modal-body">
            <form id="forecastForm" onsubmit="generateForecast(event)">
                <div class="form-group">
                    <label>Forecast Range</label>
                    <select id="range" name="range" class="form-control" required>
                        <option value="1 week">1 Week</option>
                        <option value="1 month" selected>1 Month</option>
                        <option value="3 months">3 Months</option>
                        <option value="6 months">6 Months</option>
                        <option value="1 year">1 Year</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Scenario Type</label>
                    <select id="scenario" name="scenario" class="form-control" required>
                        <option value="optimistic">Optimistic</option>
                        <option value="realistic" selected>Realistic</option>
                        <option value="pessimistic">Pessimistic</option>
                    </select>
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> AI will analyze your transaction history and upcoming bills to generate predictions
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('forecastModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-magic"></i> Generate Forecast
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.full-width {
    grid-column: 1 / -1;
}

.alert {
    padding: 12px 16px;
    border-radius: 8px;
    margin: 16px 0;
}

.alert-info {
    background: rgba(59, 130, 246, 0.1);
    border: 1px solid rgba(59, 130, 246, 0.3);
    color: var(--primary);
}

.alert-warning {
    background: rgba(245, 158, 11, 0.1);
    border: 1px solid rgba(245, 158, 11, 0.3);
    color: #f59e0b;
}
</style>

<?php include 'includes/footer.php'; ?>
