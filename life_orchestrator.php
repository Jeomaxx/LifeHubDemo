<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$pageTitle = 'Life Orchestrator';
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <i data-lucide="zap" class="text-primary"></i>
                Life Orchestrator
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Automate your life with intelligent rules and triggers</p>
        </div>
        <button onclick="openRuleModal()" class="btn bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-lg flex items-center gap-2">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Create Rule</span>
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Active Rules</p>
                    <p id="activeRules" class="text-2xl font-bold text-gray-900 dark:text-white mt-1">0</p>
                </div>
                <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                    <i data-lucide="check-circle" class="text-green-600 w-6 h-6"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Executions (Today)</p>
                    <p id="todayExecutions" class="text-2xl font-bold text-gray-900 dark:text-white mt-1">0</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                    <i data-lucide="activity" class="text-blue-600 w-6 h-6"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Success Rate</p>
                    <p id="successRate" class="text-2xl font-bold text-green-600 mt-1">0%</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                    <i data-lucide="trending-up" class="text-purple-600 w-6 h-6"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Time Saved</p>
                    <p id="timeSaved" class="text-2xl font-bold text-orange-600 mt-1">0h</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900 rounded-lg flex items-center justify-center">
                    <i data-lucide="clock" class="text-orange-600 w-6 h-6"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4">Automation Rules</h2>
                <div id="rulesList"></div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold mb-4">Execution History</h2>
                <div id="executionLog"></div>
            </div>
        </div>

        <div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4">Rule Templates</h2>
                <div class="space-y-3">
                    <div class="p-3 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700" onclick="useTemplate('weekly-report')">
                        <div class="font-medium">Weekly Report</div>
                        <div class="text-sm text-gray-600">Every Monday: Generate & send weekly summary</div>
                    </div>
                    <div class="p-3 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700" onclick="useTemplate('budget-alert')">
                        <div class="font-medium">Budget Alert</div>
                        <div class="text-sm text-gray-600">If budget < $500: Send alert notification</div>
                    </div>
                    <div class="p-3 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700" onclick="useTemplate('sleep-warning')">
                        <div class="font-medium">Sleep Warning</div>
                        <div class="text-sm text-gray-600">If sleep < 6h for 3 days: Reduce task load</div>
                    </div>
                    <div class="p-3 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700" onclick="useTemplate('bill-sync')">
                        <div class="font-medium">Bill Sync</div>
                        <div class="text-sm text-gray-600">Daily: Sync bank transactions & categorize</div>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-blue-500 to-primary rounded-lg p-6 text-white">
                <h3 class="font-semibold mb-2 flex items-center gap-2">
                    <i data-lucide="sparkles" class="w-5 h-5"></i>
                    Automation Power
                </h3>
                <p class="text-sm opacity-90 mb-4">Create intelligent rules that adapt to your patterns and automatically handle routine tasks.</p>
                <button onclick="openRuleModal()" class="btn bg-white text-primary px-4 py-2 rounded-lg w-full font-semibold">
                    Create First Rule
                </button>
            </div>
        </div>
    </div>
</div>

<script src="/assets/js/life-orchestrator.js"></script>
<script>
if (typeof lucide !== 'undefined') lucide.createIcons();
</script>

<?php include 'includes/footer.php'; ?>
