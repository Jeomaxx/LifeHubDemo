<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$stats = getStats($userId);

$upcomingBirthdays = getUpcomingBirthdays($userId, 7) ?: [];
$upcomingBills = getUpcomingBills($userId, 7) ?: [];

$recentGoals = $db->fetchAll("SELECT * FROM goals WHERE user_id = ? AND status = 'active' ORDER BY created_at DESC LIMIT 5", [$userId]) ?: [];
$recentTasks = $db->fetchAll("SELECT * FROM tasks WHERE user_id = ? AND status != 'completed' ORDER BY created_at DESC LIMIT 5", [$userId]) ?: [];

$financeData = $db->fetchAll("SELECT type, SUM(amount) as total FROM finance WHERE user_id = ? AND EXTRACT(MONTH FROM date) = EXTRACT(MONTH FROM CURRENT_DATE) GROUP BY type", [$userId]) ?: [];

$pageTitle = 'Dashboard';
include 'includes/header.php';
?>

<div class="page-header animate-fadeInDown">
    <h1><i class="fas fa-home"></i> Dashboard</h1>
    <p class="page-subtitle">Welcome back, <?php echo sanitize($auth->getCurrentUser()['name']); ?>!</p>
</div>

<div class="stats-grid stagger-animation">
    <div class="stat-card">
        <div class="stat-icon bg-blue">
            <i class="fas fa-box"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $stats['assets']; ?></h3>
            <p>Total Assets</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-red">
            <i class="fas fa-file-invoice-dollar"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $stats['bills']; ?></h3>
            <p>Pending Bills</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-green">
            <i class="fas fa-bullseye"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $stats['goals']; ?></h3>
            <p>Active Goals</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-purple">
            <i class="fas fa-tasks"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $stats['tasks']; ?></h3>
            <p>Pending Tasks</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-orange">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $stats['habits']; ?></h3>
            <p>Active Habits</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-teal">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo formatCurrency($stats['total_income'] - $stats['total_expense']); ?></h3>
            <p>Net Balance (This Month)</p>
        </div>
    </div>
</div>

<!-- AI Insights Panel -->
<div class="ai-insights-panel animate-fadeInUp" id="ai-insights-panel">
    <div class="card-header">
        <h3><i class="fas fa-robot"></i> AI Insights & Predictions</h3>
        <button class="btn btn-sm" onclick="refreshAIInsights()"><i class="fas fa-sync-alt"></i> Refresh</button>
    </div>
    <div class="ai-insights-grid" id="ai-insights-grid">
        <div class="text-center py-4">
            <i class="fas fa-spinner fa-spin fa-2x text-gray-400"></i>
            <p class="mt-2 text-gray-500">Loading AI insights...</p>
        </div>
    </div>
</div>

<div class="dashboard-grid page-transition">
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-birthday-cake"></i> Upcoming Birthdays</h3>
            <a href="/birthdays.php" class="btn btn-sm">View All</a>
        </div>
        <div class="card-body">
            <?php if (count($upcomingBirthdays) > 0): ?>
                <ul class="event-list">
                    <?php foreach ($upcomingBirthdays as $birthday): ?>
                        <li class="event-item">
                            <div class="event-info">
                                <strong><?php echo sanitize($birthday['name']); ?></strong>
                                <span><?php echo formatDate($birthday['birth_date'], 'M d'); ?></span>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="no-data">No upcoming birthdays in the next 7 days</p>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-file-invoice-dollar"></i> Upcoming Bills</h3>
            <a href="/bills.php" class="btn btn-sm">View All</a>
        </div>
        <div class="card-body">
            <?php if (count($upcomingBills) > 0): ?>
                <ul class="event-list">
                    <?php foreach ($upcomingBills as $bill): ?>
                        <li class="event-item">
                            <div class="event-info">
                                <strong><?php echo sanitize($bill['name']); ?></strong>
                                <span><?php echo formatCurrency($bill['amount']); ?> - <?php echo formatDate($bill['due_date'], 'M d'); ?></span>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="no-data">No bills due in the next 7 days</p>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-bullseye"></i> Active Goals</h3>
            <a href="/goals.php" class="btn btn-sm">View All</a>
        </div>
        <div class="card-body">
            <?php if (count($recentGoals) > 0): ?>
                <ul class="goal-list">
                    <?php foreach ($recentGoals as $goal): ?>
                        <li class="goal-item">
                            <div class="goal-info">
                                <strong><?php echo sanitize($goal['title']); ?></strong>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo $goal['progress']; ?>%"></div>
                                </div>
                                <span class="progress-text"><?php echo $goal['progress']; ?>%</span>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="no-data">No active goals</p>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-tasks"></i> Recent Tasks</h3>
            <a href="/tasks.php" class="btn btn-sm">View All</a>
        </div>
        <div class="card-body">
            <?php if (count($recentTasks) > 0): ?>
                <ul class="task-list">
                    <?php foreach ($recentTasks as $task): ?>
                        <li class="task-item">
                            <div class="task-info">
                                <strong><?php echo sanitize($task['title']); ?></strong>
                                <span class="badge badge-<?php echo $task['priority']; ?>"><?php echo ucfirst($task['priority']); ?></span>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="no-data">No pending tasks</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="chart-section">
    <div class="chart-card">
        <h3><i class="fas fa-chart-pie"></i> Finance Overview (This Month)</h3>
        <canvas id="financeChart"></canvas>
    </div>
</div>

<style>
.ai-insights-panel {
    background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(139, 92, 246, 0.1));
    border-radius: 12px;
    padding: 24px;
    margin: 24px 0;
    border: 1px solid rgba(99, 102, 241, 0.2);
}

.ai-insights-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 16px;
    margin-top: 16px;
}

.ai-insight-card {
    background: white;
    border-radius: 8px;
    padding: 16px;
    border-left: 4px solid;
    transition: transform 0.2s;
}

.ai-insight-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.ai-insight-card.blue { border-left-color: #3b82f6; }
.ai-insight-card.green { border-left-color: #10b981; }
.ai-insight-card.purple { border-left-color: #8b5cf6; }
.ai-insight-card.orange { border-left-color: #f59e0b; }
.ai-insight-card.pink { border-left-color: #ec4899; }
.ai-insight-card.red { border-left-color: #ef4444; }
.ai-insight-card.yellow { border-left-color: #eab308; }

.ai-insight-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
}

.ai-insight-icon {
    font-size: 1.25rem;
}
</style>

<script>
async function loadAIInsights() {
    try {
        const response = await fetch('/api/ai_insights.php?action=get_all');
        const result = await response.json();
        
        if (result.success && result.insights.length > 0) {
            const container = document.getElementById('ai-insights-grid');
            container.innerHTML = result.insights.map(insight => `
                <a href="${insight.link}" class="ai-insight-card ${insight.color}">
                    <div class="ai-insight-header">
                        <i class="fas fa-${insight.icon} ai-insight-icon"></i>
                        <strong>${insight.title}</strong>
                    </div>
                    <p class="text-sm text-gray-600">${insight.message}</p>
                </a>
            `).join('');
        } else {
            document.getElementById('ai-insights-grid').innerHTML = '<p class="text-center text-gray-500">No AI insights available yet. Start using AI features to see predictions here!</p>';
        }
    } catch (error) {
        console.error('Error loading AI insights:', error);
        document.getElementById('ai-insights-grid').innerHTML = '<p class="text-center text-red-500">Failed to load AI insights</p>';
    }
}

function refreshAIInsights() {
    loadAIInsights();
    showToast('info', 'Refreshing', 'Updating AI insights...');
}

document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('financeChart').getContext('2d');
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: ['Income', 'Expense'],
            datasets: [{
                data: [<?php echo $stats['total_income']; ?>, <?php echo $stats['total_expense']; ?>],
                backgroundColor: ['#10b981', '#ef4444']
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
    
    loadAIInsights();
});
</script>

<?php include 'includes/footer.php'; ?>
