<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$pageTitle = 'Mental Wellness Dashboard';
include 'includes/header.php';

// Get wellness data
$moodData = $db->fetchAll("SELECT * FROM mood_entries WHERE user_id = ? ORDER BY mood_date DESC LIMIT 30", [$userId]) ?: [];
$sleepData = $db->fetchAll("SELECT * FROM sleep_logs WHERE user_id = ? ORDER BY sleep_date DESC LIMIT 30", [$userId]) ?: [];
$meditationData = $db->fetchAll("SELECT * FROM meditation_sessions WHERE user_id = ? ORDER BY session_date DESC LIMIT 30", [$userId]) ?: [];

// Calculate wellness score
$avgMood = !empty($moodData) ? array_sum(array_column($moodData, 'mood_rating')) / count($moodData) : 0;
$avgSleep = !empty($sleepData) ? array_sum(array_column($sleepData, 'hours')) / count($sleepData) : 0;
$meditationMinutes = array_sum(array_column($meditationData, 'duration_minutes'));

$wellnessScore = calculateWellnessScore($avgMood, $avgSleep, $meditationMinutes);
?>

<div class="container mx-auto px-4 py-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <i class="fas fa-brain text-primary"></i>
                Mental Wellness Dashboard
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Comprehensive view of your mental and emotional health</p>
        </div>
    </div>

    <!-- Wellness Score Card -->
    <div class="bg-gradient-to-r from-purple-500 to-pink-600 rounded-lg p-8 text-white mb-6">
        <div class="flex flex-col md:flex-row items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold mb-2">Overall Wellness Score</h2>
                <p class="text-5xl font-bold"><?php echo $wellnessScore; ?>/100</p>
                <p class="mt-2 opacity-90"><?php echo getWellnessLevel($wellnessScore); ?></p>
            </div>
            <div class="mt-6 md:mt-0">
                <canvas id="wellnessGauge" width="200" height="200"></canvas>
            </div>
        </div>
    </div>

    <!-- Key Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Avg Mood</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1"><?php echo number_format($avgMood, 1); ?>/10</p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-smile text-yellow-600 dark:text-yellow-400 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Avg Sleep</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1"><?php echo number_format($avgSleep, 1); ?>h</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-bed text-blue-600 dark:text-blue-400 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Meditation</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1"><?php echo $meditationMinutes; ?> min</p>
                </div>
                <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-om text-green-600 dark:text-green-400 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Streak</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1" id="wellnessStreak">-</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-fire text-purple-600 dark:text-purple-400 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Mood Trend -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
                <i class="fas fa-chart-line"></i> Mood Trend (30 Days)
            </h3>
            <canvas id="moodChart"></canvas>
        </div>

        <!-- Sleep Pattern -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
                <i class="fas fa-moon"></i> Sleep Pattern (30 Days)
            </h3>
            <canvas id="sleepChart"></canvas>
        </div>

        <!-- Mindfulness Activity -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
                <i class="fas fa-spa"></i> Mindfulness Activity
            </h3>
            <canvas id="meditationChart"></canvas>
        </div>

        <!-- Correlation Matrix -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
                <i class="fas fa-project-diagram"></i> Sleep-Mood Correlation
            </h3>
            <canvas id="correlationChart"></canvas>
        </div>
    </div>

    <!-- AI Insights -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
            <i class="fas fa-lightbulb"></i> AI Wellness Insights
        </h3>
        <div id="aiInsights" class="space-y-3">
            <div class="flex items-start gap-3 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                <i class="fas fa-info-circle text-blue-600 text-xl mt-1"></i>
                <div>
                    <p class="font-semibold text-gray-900 dark:text-white">Sleep Pattern Analysis</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">You're getting an average of <?php echo number_format($avgSleep, 1); ?> hours of sleep. <?php echo $avgSleep < 7 ? 'Consider increasing your sleep time for better wellness.' : 'Great job maintaining healthy sleep habits!'; ?></p>
                </div>
            </div>
            <div class="flex items-start gap-3 p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
                <i class="fas fa-brain text-purple-600 text-xl mt-1"></i>
                <div>
                    <p class="font-semibold text-gray-900 dark:text-white">Mindfulness Practice</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1"><?php echo $meditationMinutes > 100 ? "Excellent meditation practice this month! Keep it up." : "Try to meditate for at least 10 minutes daily for better mental clarity."; ?></p>
                </div>
            </div>
            <div class="flex items-start gap-3 p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                <i class="fas fa-chart-line text-green-600 text-xl mt-1"></i>
                <div>
                    <p class="font-semibold text-gray-900 dark:text-white">Mood Trend</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Your average mood score is <?php echo number_format($avgMood, 1); ?>/10. <?php echo $avgMood >= 7 ? "You're doing great!" : "Consider practicing gratitude journaling to boost your mood."; ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Recommendations -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
            <i class="fas fa-tasks"></i> Personalized Recommendations
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                        <i class="fas fa-bed text-blue-600"></i>
                    </div>
                    <h4 class="font-semibold text-gray-900 dark:text-white">Better Sleep</h4>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Maintain a consistent sleep schedule and avoid screens 1 hour before bed</p>
            </div>
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                        <i class="fas fa-walking text-green-600"></i>
                    </div>
                    <h4 class="font-semibold text-gray-900 dark:text-white">Daily Exercise</h4>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400">30 minutes of physical activity can significantly improve mood and mental health</p>
            </div>
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900 rounded-full flex items-center justify-center">
                        <i class="fas fa-users text-purple-600"></i>
                    </div>
                    <h4 class="font-semibold text-gray-900 dark:text-white">Social Connection</h4>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Regular social interaction helps reduce stress and improve overall wellbeing</p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const moodData = <?php echo json_encode($moodData); ?>;
const sleepData = <?php echo json_encode($sleepData); ?>;
const meditationData = <?php echo json_encode($meditationData); ?>;

// Mood Trend Chart
new Chart(document.getElementById('moodChart'), {
    type: 'line',
    data: {
        labels: moodData.map(d => new Date(d.mood_date).toLocaleDateString()),
        datasets: [{
            label: 'Mood Rating',
            data: moodData.map(d => d.mood_rating),
            borderColor: 'rgb(234, 179, 8)',
            backgroundColor: 'rgba(234, 179, 8, 0.1)',
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                max: 10
            }
        }
    }
});

// Sleep Pattern Chart
new Chart(document.getElementById('sleepChart'), {
    type: 'bar',
    data: {
        labels: sleepData.map(d => new Date(d.sleep_date).toLocaleDateString()),
        datasets: [{
            label: 'Hours of Sleep',
            data: sleepData.map(d => d.hours),
            backgroundColor: 'rgba(59, 130, 246, 0.5)',
            borderColor: 'rgb(59, 130, 246)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                max: 12
            }
        }
    }
});

// Meditation Chart
new Chart(document.getElementById('meditationChart'), {
    type: 'doughnut',
    data: {
        labels: ['Meditated', 'Remaining Goal'],
        datasets: [{
            data: [<?php echo $meditationMinutes; ?>, Math.max(0, 300 - <?php echo $meditationMinutes; ?>)],
            backgroundColor: ['rgb(34, 197, 94)', 'rgb(229, 231, 235)']
        }]
    }
});

// Correlation Scatter Chart
new Chart(document.getElementById('correlationChart'), {
    type: 'scatter',
    data: {
        datasets: [{
            label: 'Sleep vs Mood',
            data: sleepData.slice(0, moodData.length).map((sleep, i) => ({
                x: sleep.hours,
                y: moodData[i]?.mood_rating || 0
            })),
            backgroundColor: 'rgba(168, 85, 247, 0.5)'
        }]
    },
    options: {
        responsive: true,
        scales: {
            x: {
                title: {
                    display: true,
                    text: 'Hours of Sleep'
                }
            },
            y: {
                title: {
                    display: true,
                    text: 'Mood Rating'
                },
                beginAtZero: true,
                max: 10
            }
        }
    }
});
</script>

<?php include 'includes/footer.php'; ?>

<?php
function calculateWellnessScore($avgMood, $avgSleep, $meditationMinutes) {
    $moodScore = ($avgMood / 10) * 40; // 40% weight
    $sleepScore = (min($avgSleep, 8) / 8) * 40; // 40% weight
    $meditationScore = (min($meditationMinutes, 300) / 300) * 20; // 20% weight
    
    return round($moodScore + $sleepScore + $meditationScore);
}

function getWellnessLevel($score) {
    if ($score >= 80) return 'Excellent - Keep up the great work!';
    if ($score >= 60) return 'Good - You\'re doing well';
    if ($score >= 40) return 'Fair - Room for improvement';
    return 'Needs Attention - Focus on self-care';
}
?>
