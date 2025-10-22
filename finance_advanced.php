<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$auth = new Auth();
$auth->requireLogin();
$userId = $auth->getUserId();
$db = Database::getInstance();

// Get financial data
$investments = $db->fetchAll("SELECT * FROM investments WHERE user_id = ? ORDER BY created_at DESC LIMIT 10", [$userId]) ?: [];
$totalInvestments = $db->fetchColumn("SELECT COALESCE(SUM(current_value), 0) FROM investments WHERE user_id = ?", [$userId]) ?: 0;

$bills = $db->fetchAll("SELECT * FROM bills WHERE user_id = ? AND payment_status != 'paid' ORDER BY due_date ASC LIMIT 5", [$userId]) ?: [];
$totalDue = $db->fetchColumn("SELECT COALESCE(SUM(amount), 0) FROM bills WHERE user_id = ? AND payment_status != 'paid'", [$userId]) ?: 0;

$income = $db->fetchColumn("SELECT COALESCE(SUM(amount), 0) FROM finance WHERE user_id = ? AND type = 'income' AND EXTRACT(MONTH FROM date) = EXTRACT(MONTH FROM CURRENT_DATE)", [$userId]) ?: 0;
$expenses = $db->fetchColumn("SELECT COALESCE(SUM(amount), 0) FROM finance WHERE user_id = ? AND type = 'expense' AND EXTRACT(MONTH FROM date) = EXTRACT(MONTH FROM CURRENT_DATE)", [$userId]) ?: 0;

$pageTitle = 'Finance Advanced';
include 'includes/header.php';
?>
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <i class="fas fa-chart-line text-primary"></i>
                Finance Advanced
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Comprehensive financial overview and analytics</p>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Monthly Income</p>
                    <p class="text-2xl font-bold text-green-600 dark:text-green-400"><?php echo formatCurrency($income); ?></p>
                </div>
                <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-arrow-up text-green-600 dark:text-green-400 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Monthly Expenses</p>
                    <p class="text-2xl font-bold text-red-600 dark:text-red-400"><?php echo formatCurrency($expenses); ?></p>
                </div>
                <div class="w-12 h-12 bg-red-100 dark:bg-red-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-arrow-down text-red-600 dark:text-red-400 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Investments</p>
                    <p class="text-2xl font-bold text-blue-600 dark:text-blue-400"><?php echo formatCurrency($totalInvestments); ?></p>
                </div>
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-chart-pie text-blue-600 dark:text-blue-400 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Bills Due</p>
                    <p class="text-2xl font-bold text-orange-600 dark:text-orange-400"><?php echo formatCurrency($totalDue); ?></p>
                </div>
                <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-file-invoice-dollar text-orange-600 dark:text-orange-400 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Access Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
        <a href="investments.php" class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-chart-line text-blue-600 dark:text-blue-400 text-xl"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Investment Portfolio</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Track stocks, crypto, and investments</p>
                </div>
            </div>
        </a>

        <a href="bills.php" class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-file-invoice-dollar text-orange-600 dark:text-orange-400 text-xl"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Bills & Payments</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Manage bills and recurring payments</p>
                </div>
            </div>
        </a>

        <a href="budgets.php" class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-wallet text-green-600 dark:text-green-400 text-xl"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Budget Manager</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Set and track spending budgets</p>
                </div>
            </div>
        </a>

        <a href="debts.php" class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-red-100 dark:bg-red-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-hand-holding-usd text-red-600 dark:text-red-400 text-xl"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Debt Payoff Planner</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Track and optimize debt payoff</p>
                </div>
            </div>
        </a>

        <a href="crypto.php" class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-bitcoin text-yellow-600 dark:text-yellow-400 text-xl"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Cryptocurrency Portfolio</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Track crypto investments & prices</p>
                </div>
            </div>
        </a>

        <a href="financial_forecast.php" class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-crystal-ball text-purple-600 dark:text-purple-400 text-xl"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Financial Forecast</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400">AI-powered financial predictions</p>
                </div>
            </div>
        </a>
    </div>

    <!-- Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                <i class="fas fa-chart-line mr-2"></i>Recent Investments
            </h3>
            <?php if (empty($investments)): ?>
                <p class="text-gray-500 dark:text-gray-400 text-center py-8">No investments recorded</p>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach (array_slice($investments, 0, 5) as $investment): ?>
                        <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white"><?php echo sanitize($investment['name']); ?></p>
                                <p class="text-sm text-gray-600 dark:text-gray-400"><?php echo sanitize($investment['type']); ?></p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-gray-900 dark:text-white"><?php echo formatCurrency($investment['current_value']); ?></p>
                                <?php
                                $roi = ($investment['current_value'] - $investment['amount_invested']) / $investment['amount_invested'] * 100;
                                ?>
                                <p class="text-sm <?php echo $roi >= 0 ? 'text-green-600' : 'text-red-600'; ?>">
                                    <?php echo ($roi >= 0 ? '+' : '') . number_format($roi, 2); ?>%
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                <i class="fas fa-file-invoice-dollar mr-2"></i>Upcoming Bills
            </h3>
            <?php if (empty($bills)): ?>
                <p class="text-gray-500 dark:text-gray-400 text-center py-8">No upcoming bills</p>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($bills as $bill): ?>
                        <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white"><?php echo sanitize($bill['name']); ?></p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Due: <?php echo formatDate($bill['due_date']); ?></p>
                            </div>
                            <p class="font-semibold text-gray-900 dark:text-white"><?php echo formatCurrency($bill['amount']); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
