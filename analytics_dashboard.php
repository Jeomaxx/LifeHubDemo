<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$pageTitle = 'Analytics Dashboard';
include 'includes/header.php';

// Get comprehensive stats
$financialStats = $db->fetchOne("
    SELECT 
        COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) as total_income,
        COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) as total_expense
    FROM finance 
    WHERE user_id = ? AND EXTRACT(MONTH FROM date) = EXTRACT(MONTH FROM CURRENT_DATE)
", [$userId]);

$taskStats = $db->fetchOne("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
        SUM(CASE WHEN status != 'completed' AND due_date < CURRENT_DATE THEN 1 ELSE 0 END) as overdue
    FROM tasks WHERE user_id = ?
", [$userId]);

$completionRate = $taskStats['total'] > 0 ? ($taskStats['completed'] / $taskStats['total']) * 100 : 0;

// Monthly expense trend (last 6 months)
$expenseTrend = [];
for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $monthData = $db->fetchOne("
        SELECT COALESCE(SUM(amount), 0) as amount
        FROM finance 
        WHERE user_id = ? AND type = 'expense' AND DATE_TRUNC('month', date) = ?::date
    ", [$userId, $month . '-01']);
    $expenseTrend[] = [
        'month' => date('M', strtotime("-$i months")),
        'amount' => $monthData['amount'] ?? 0
    ];
}

// Category breakdown
$categorySpending = $db->fetchAll("
    SELECT category, COALESCE(SUM(amount), 0) as total
    FROM finance 
    WHERE user_id = ? AND type = 'expense' 
    AND EXTRACT(MONTH FROM date) = EXTRACT(MONTH FROM CURRENT_DATE)
    GROUP BY category
    ORDER BY total DESC
    LIMIT 5
", [$userId]);

?>

<div class="container mx-auto px-4 py-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <i class="fas fa-chart-bar text-primary"></i>
                Analytics Dashboard
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Comprehensive insights across all modules</p>
        </div>
        <div class="flex gap-2">
            <select class="px-4 py-2 border border-gray-300 rounded-lg" id="timeRange">
                <option value="7">Last 7 Days</option>
                <option value="30" selected>Last 30 Days</option>
                <option value="90">Last 90 Days</option>
                <option value="365">Last Year</option>
            </select>
            <button onclick="exportAnalytics()" class="btn bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-download"></i>
                <span class="hidden md:inline">Export</span>
            </button>
        </div>
    </div>

    <!-- Key Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg p-6 text-white">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-dollar-sign text-2xl"></i>
                </div>
                <span class="text-sm opacity-90">This Month</span>
            </div>
            <h3 class="text-3xl font-bold"><?php echo formatCurrency($financialStats['total_expense']); ?></h3>
            <p class="text-sm opacity-90 mt-1">Total Expenses</p>
        </div>

        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg p-6 text-white">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-arrow-up text-2xl"></i>
                </div>
                <span class="text-sm opacity-90">This Month</span>
            </div>
            <h3 class="text-3xl font-bold"><?php echo formatCurrency($financialStats['total_income']); ?></h3>
            <p class="text-sm opacity-90 mt-1">Total Income</p>
        </div>

        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg p-6 text-white">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-tasks text-2xl"></i>
                </div>
                <span class="text-sm opacity-90">Productivity</span>
            </div>
            <h3 class="text-3xl font-bold"><?php echo round($completionRate); ?>%</h3>
            <p class="text-sm opacity-90 mt-1">Task Completion</p>
        </div>

        <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg p-6 text-white">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-chart-line text-2xl"></i>
                </div>
                <span class="text-sm opacity-90">Savings Rate</span>
            </div>
            <h3 class="text-3xl font-bold">
                <?php 
                $savingsRate = $financialStats['total_income'] > 0 
                    ? (($financialStats['total_income'] - $financialStats['total_expense']) / $financialStats['total_income']) * 100 
                    : 0;
                echo round($savingsRate); 
                ?>%
            </h3>
            <p class="text-sm opacity-90 mt-1">Monthly Savings</p>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Expense Trend -->
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Expense Trend</h3>
            <canvas id="expenseTrendChart" height="200"></canvas>
        </div>

        <!-- Category Breakdown -->
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Top Categories</h3>
            <canvas id="categoryChart" height="200"></canvas>
        </div>
    </div>

    <!-- Module Insights -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Financial Health -->
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2 mb-4">
                <i class="fas fa-wallet text-green-500"></i>
                Financial Health
            </h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-400">Income/Expense Ratio</span>
                    <span class="font-semibold text-green-600">
                        <?php 
                        $ratio = $financialStats['total_expense'] > 0 
                            ? $financialStats['total_income'] / $financialStats['total_expense'] 
                            : 0;
                        echo number_format($ratio, 2); 
                        ?>
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-400">Net Cash Flow</span>
                    <span class="font-semibold <?php echo ($financialStats['total_income'] - $financialStats['total_expense']) >= 0 ? 'text-green-600' : 'text-red-600'; ?>">
                        <?php echo formatCurrency($financialStats['total_income'] - $financialStats['total_expense']); ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Productivity Insights -->
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2 mb-4">
                <i class="fas fa-chart-line text-blue-500"></i>
                Productivity
            </h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-400">Total Tasks</span>
                    <span class="font-semibold text-gray-900 dark:text-white"><?php echo $taskStats['total']; ?></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-400">Completed</span>
                    <span class="font-semibold text-green-600"><?php echo $taskStats['completed']; ?></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-400">Overdue</span>
                    <span class="font-semibold text-red-600"><?php echo $taskStats['overdue']; ?></span>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2 mb-4">
                <i class="fas fa-bolt text-yellow-500"></i>
                Quick Actions
            </h3>
            <div class="space-y-2">
                <a href="/finance.php" class="block p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                    <i class="fas fa-plus-circle text-primary mr-2"></i>
                    Add Transaction
                </a>
                <a href="/tasks.php" class="block p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                    <i class="fas fa-tasks text-primary mr-2"></i>
                    Create Task
                </a>
                <a href="/bills.php" class="block p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                    <i class="fas fa-file-invoice text-primary mr-2"></i>
                    Add Bill
                </a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Expense Trend Chart
const expenseTrendCtx = document.getElementById('expenseTrendChart').getContext('2d');
new Chart(expenseTrendCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode(array_column($expenseTrend, 'month')); ?>,
        datasets: [{
            label: 'Expenses',
            data: <?php echo json_encode(array_column($expenseTrend, 'amount')); ?>,
            borderColor: 'rgb(59, 130, 246)',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '$' + value.toLocaleString();
                    }
                }
            }
        }
    }
});

// Category Chart
const categoryCtx = document.getElementById('categoryChart').getContext('2d');
new Chart(categoryCtx, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode(array_column($categorySpending, 'category')); ?>,
        datasets: [{
            data: <?php echo json_encode(array_column($categorySpending, 'total')); ?>,
            backgroundColor: [
                'rgb(59, 130, 246)',
                'rgb(16, 185, 129)',
                'rgb(251, 146, 60)',
                'rgb(139, 92, 246)',
                'rgb(236, 72, 153)'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

function exportAnalytics() {
    window.location.href = '/api/export.php?type=analytics';
}
</script>

<?php include 'includes/footer.php'; ?>
