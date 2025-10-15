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

$upcomingBirthdays = getUpcomingBirthdays($userId, 7);
$upcomingBills = getUpcomingBills($userId, 7);

$recentGoals = $db->fetchAll("SELECT * FROM goals WHERE user_id = ? AND status = 'active' ORDER BY created_at DESC LIMIT 5", [$userId]);
$recentTasks = $db->fetchAll("SELECT * FROM tasks WHERE user_id = ? AND status != 'completed' ORDER BY created_at DESC LIMIT 5", [$userId]);

$financeData = $db->fetchAll("SELECT type, SUM(amount) as total FROM finance WHERE user_id = ? AND EXTRACT(MONTH FROM date) = EXTRACT(MONTH FROM CURRENT_DATE) GROUP BY type", [$userId]);

$pageTitle = 'Dashboard';
include 'includes/header.php';
?>

<div class="page-header">
    <h1><i class="fas fa-home"></i> Dashboard</h1>
    <p class="page-subtitle">Welcome back, <?php echo sanitize($auth->getCurrentUser()['name']); ?>!</p>
</div>

<div class="stats-grid">
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

<div class="dashboard-grid">
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

<script>
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
});
</script>

<?php include 'includes/footer.php'; ?>
