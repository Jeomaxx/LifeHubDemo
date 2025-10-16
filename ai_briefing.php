<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$pageTitle = 'Daily Briefing';
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <i class="fas fa-newspaper text-primary"></i>
                Daily Briefing
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1"><?php echo date('l, F j, Y'); ?></p>
        </div>
        <button onclick="refreshBriefing()" class="btn bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-lg flex items-center gap-2">
            <i class="fas fa-sync"></i>
            <span>Refresh</span>
        </button>
    </div>

    <!-- Greeting Card -->
    <div class="bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg p-8 text-white mb-6">
        <h2 class="text-3xl font-bold mb-2">Good <?php echo date('H') < 12 ? 'Morning' : (date('H') < 18 ? 'Afternoon' : 'Evening'); ?>!</h2>
        <p class="text-lg opacity-90">Here's your personalized briefing for today</p>
    </div>

    <!-- Briefing Sections -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Finance Summary -->
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2 mb-4">
                <i class="fas fa-wallet text-green-500"></i>
                Financial Overview
            </h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-400">This Month's Expenses</span>
                    <span class="font-semibold text-gray-900 dark:text-white">$0.00</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-400">Pending Bills</span>
                    <span class="font-semibold text-yellow-600">0</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-400">Budget Status</span>
                    <span class="font-semibold text-green-600">On Track</span>
                </div>
            </div>
        </div>

        <!-- Tasks Summary -->
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2 mb-4">
                <i class="fas fa-tasks text-blue-500"></i>
                Tasks & Goals
            </h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-400">Tasks Due Today</span>
                    <span class="font-semibold text-gray-900 dark:text-white">0</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-400">Active Goals</span>
                    <span class="font-semibold text-gray-900 dark:text-white">0</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-400">Completion Rate</span>
                    <span class="font-semibold text-green-600">0%</span>
                </div>
            </div>
        </div>

        <!-- Health Summary -->
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2 mb-4">
                <i class="fas fa-heartbeat text-red-500"></i>
                Health & Wellness
            </h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-400">Habits Completed</span>
                    <span class="font-semibold text-gray-900 dark:text-white">0/0</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-400">Water Intake</span>
                    <span class="font-semibold text-blue-600">0 ml</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-400">Exercise Today</span>
                    <span class="font-semibold text-gray-900 dark:text-white">No</span>
                </div>
            </div>
        </div>

        <!-- Upcoming Events -->
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2 mb-4">
                <i class="fas fa-calendar text-purple-500"></i>
                Upcoming Events
            </h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-400">Birthdays This Week</span>
                    <span class="font-semibold text-gray-900 dark:text-white">0</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-400">Calendar Events</span>
                    <span class="font-semibold text-gray-900 dark:text-white">0</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-400">Subscription Renewals</span>
                    <span class="font-semibold text-gray-900 dark:text-white">0</span>
                </div>
            </div>
        </div>
    </div>

    <!-- AI Insights (if configured) -->
    <div class="mt-6 bg-gradient-to-r from-purple-500 to-pink-500 rounded-lg p-6 text-white">
        <h3 class="text-xl font-semibold mb-3 flex items-center gap-2">
            <i class="fas fa-lightbulb"></i>
            AI Insights
        </h3>
        <p class="opacity-90">Configure your AI assistant to get personalized insights and recommendations based on your data.</p>
        <a href="/settings.php" class="inline-block mt-4 px-6 py-2 bg-white text-purple-600 rounded-lg font-semibold hover:bg-gray-100">
            Configure AI Settings
        </a>
    </div>
</div>

<script>
function refreshBriefing() {
    location.reload();
}
</script>

<?php include 'includes/footer.php'; ?>
