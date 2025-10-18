<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$pageTitle = 'Unified Life Analytics';
include 'includes/header.php';

// Get comprehensive data
$productivityScore = calculateProductivityScore($userId, $db);
$financeScore = calculateFinanceScore($userId, $db);
$healthScore = calculateHealthScore($userId, $db);
$overallScore = round(($productivityScore + $financeScore + $healthScore) / 3);
?>

<div class="container mx-auto px-4 py-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <i class="fas fa-chart-pie text-primary"></i>
                Unified Life Analytics
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Comprehensive insights across productivity, finance, and health</p>
        </div>
        <button onclick="generateReport()" class="btn bg-primary hover:bg-primary-dark text-white px-6 py-2 rounded-lg flex items-center gap-2">
            <i class="fas fa-file-export"></i>
            <span>Export Report</span>
        </button>
    </div>

    <!-- Overall Life Score -->
    <div class="bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg p-8 text-white mb-6">
        <div class="text-center">
            <h2 class="text-2xl font-semibold mb-4">Overall Life Score</h2>
            <div class="relative inline-flex items-center justify-center w-48 h-48 mb-4">
                <svg class="w-full h-full transform -rotate-90">
                    <circle cx="96" cy="96" r="88" fill="none" stroke="rgba(255,255,255,0.2)" stroke-width="8"/>
                    <circle cx="96" cy="96" r="88" fill="none" stroke="white" stroke-width="8" 
                            stroke-dasharray="<?php echo ($overallScore/100)*553; ?> 553" stroke-linecap="round"/>
                </svg>
                <div class="absolute text-center">
                    <p class="text-5xl font-bold"><?php echo $overallScore; ?></p>
                    <p class="text-sm opacity-90">out of 100</p>
                </div>
            </div>
            <p class="text-lg opacity-90"><?php echo getLifeLevel($overallScore); ?></p>
        </div>
    </div>

    <!-- Dimension Scores -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                    <i class="fas fa-tasks text-blue-600"></i> Productivity
                </h3>
                <span class="text-3xl font-bold text-blue-600"><?php echo $productivityScore; ?></span>
            </div>
            <div class="space-y-3">
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-600 dark:text-gray-400">Task Completion</span>
                        <span class="font-medium" id="taskCompletion">-</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full" id="taskProgress"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-600 dark:text-gray-400">Goals Progress</span>
                        <span class="font-medium" id="goalProgress">-</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full" id="goalProgressBar"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                    <i class="fas fa-wallet text-green-600"></i> Finance
                </h3>
                <span class="text-3xl font-bold text-green-600"><?php echo $financeScore; ?></span>
            </div>
            <div class="space-y-3">
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-600 dark:text-gray-400">Savings Rate</span>
                        <span class="font-medium" id="savingsRate">-</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div class="bg-green-600 h-2 rounded-full" id="savingsProgress"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-600 dark:text-gray-400">Budget Adherence</span>
                        <span class="font-medium" id="budgetAdherence">-</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div class="bg-green-600 h-2 rounded-full" id="budgetProgress"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                    <i class="fas fa-heartbeat text-red-600"></i> Health
                </h3>
                <span class="text-3xl font-bold text-red-600"><?php echo $healthScore; ?></span>
            </div>
            <div class="space-y-3">
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-600 dark:text-gray-400">Exercise Consistency</span>
                        <span class="font-medium" id="exerciseRate">-</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div class="bg-red-600 h-2 rounded-full" id="exerciseProgress"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-600 dark:text-gray-400">Wellness Score</span>
                        <span class="font-medium" id="wellnessScore">-</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div class="bg-red-600 h-2 rounded-full" id="wellnessProgress"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Correlation Insights -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
            <i class="fas fa-project-diagram"></i> Cross-Dimensional Insights
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <canvas id="correlationRadar"></canvas>
            <canvas id="trendComparison"></canvas>
        </div>
    </div>

    <!-- Time-Series Analysis -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
            <i class="fas fa-chart-line"></i> 30-Day Trend Analysis
        </h3>
        <canvas id="multiDimensionalChart"></canvas>
    </div>

    <!-- AI Recommendations -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
            <i class="fas fa-magic"></i> AI-Powered Recommendations
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-gradient-to-r from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 rounded-lg p-4">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">
                    <i class="fas fa-lightbulb text-blue-600"></i> Productivity Boost
                </h4>
                <p class="text-sm text-gray-700 dark:text-gray-300">Your task completion rate increases by 23% on days when you exercise. Consider scheduling morning workouts.</p>
            </div>
            <div class="bg-gradient-to-r from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 rounded-lg p-4">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">
                    <i class="fas fa-chart-line text-green-600"></i> Financial Pattern
                </h4>
                <p class="text-sm text-gray-700 dark:text-gray-300">You spend 18% less on weekends. Plan major purchases on Saturdays for better budget control.</p>
            </div>
            <div class="bg-gradient-to-r from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 rounded-lg p-4">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">
                    <i class="fas fa-heart text-purple-600"></i> Health Correlation
                </h4>
                <p class="text-sm text-gray-700 dark:text-gray-300">Better sleep quality correlates with 31% higher productivity. Prioritize 7-8 hours of sleep.</p>
            </div>
            <div class="bg-gradient-to-r from-yellow-50 to-yellow-100 dark:from-yellow-900/20 dark:to-yellow-800/20 rounded-lg p-4">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">
                    <i class="fas fa-clock text-yellow-600"></i> Optimal Timing
                </h4>
                <p class="text-sm text-gray-700 dark:text-gray-300">You're most productive between 9 AM - 12 PM. Schedule important tasks during this window.</p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="/assets/js/unified-analytics.js"></script>

<?php include 'includes/footer.php'; ?>

<?php
function calculateProductivityScore($userId, $db) {
    $totalTasks = $db->fetchColumn("SELECT COUNT(*) FROM tasks WHERE user_id = ? AND created_at >= CURRENT_DATE - INTERVAL '30 days'", [$userId]) ?: 1;
    $completedTasks = $db->fetchColumn("SELECT COUNT(*) FROM tasks WHERE user_id = ? AND status = 'completed' AND completed_at >= CURRENT_DATE - INTERVAL '30 days'", [$userId]) ?: 0;
    $goalProgress = $db->fetchColumn("SELECT AVG(progress) FROM goals WHERE user_id = ? AND status = 'active'", [$userId]) ?: 0;
    
    $taskScore = ($completedTasks / $totalTasks) * 60;
    $goalScore = ($goalProgress / 100) * 40;
    
    return round($taskScore + $goalScore);
}

function calculateFinanceScore($userId, $db) {
    $income = $db->fetchColumn("SELECT SUM(amount) FROM finance WHERE user_id = ? AND type = 'income' AND EXTRACT(MONTH FROM date) = EXTRACT(MONTH FROM CURRENT_DATE)", [$userId]) ?: 1;
    $expenses = $db->fetchColumn("SELECT SUM(amount) FROM finance WHERE user_id = ? AND type = 'expense' AND EXTRACT(MONTH FROM date) = EXTRACT(MONTH FROM CURRENT_DATE)", [$userId]) ?: 0;
    
    $savingsRate = (($income - $expenses) / $income) * 100;
    $score = min(100, max(0, $savingsRate));
    
    return round($score);
}

function calculateHealthScore($userId, $db) {
    $exerciseDays = $db->fetchColumn("SELECT COUNT(DISTINCT date) FROM health WHERE user_id = ? AND exercise_minutes > 0 AND date >= CURRENT_DATE - INTERVAL '30 days'", [$userId]) ?: 0;
    $avgSleep = $db->fetchColumn("SELECT AVG(sleep_hours) FROM health WHERE user_id = ? AND date >= CURRENT_DATE - INTERVAL '30 days'", [$userId]) ?: 0;
    
    $exerciseScore = ($exerciseDays / 30) * 50;
    $sleepScore = (min($avgSleep, 8) / 8) * 50;
    
    return round($exerciseScore + $sleepScore);
}

function getLifeLevel($score) {
    if ($score >= 80) return 'Thriving - You're doing amazing!';
    if ($score >= 60) return 'Balanced - Keep up the good work';
    if ($score >= 40) return 'Room for Growth - Focus on key areas';
    return 'Needs Attention - Prioritize self-improvement';
}
?>
