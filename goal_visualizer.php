<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$pageTitle = 'Goal Progress Visualizer';
include 'includes/header.php';

// Get active goals with progress history
$goals = $db->fetchAll("SELECT * FROM goals WHERE user_id = ? AND status = 'active' ORDER BY deadline ASC", [$userId]) ?: [];
?>

<div class="container mx-auto px-4 py-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <i class="fas fa-chart-line text-primary"></i>
                Goal Progress Visualizer
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Visualize your goal progress with heatmaps and timeline graphs</p>
        </div>
        <a href="/goals.php" class="btn bg-primary hover:bg-primary-dark text-white px-6 py-2 rounded-lg">
            <i class="fas fa-bullseye"></i> Manage Goals
        </a>
    </div>

    <!-- Progress Heatmap -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
            <i class="fas fa-fire"></i> Progress Heatmap - Last 30 Days
        </h3>
        <div id="heatmapContainer" class="overflow-x-auto">
            <div id="progressHeatmap"></div>
        </div>
    </div>

    <!-- Timeline Graphs -->
    <div class="grid grid-cols-1 gap-6 mb-6">
        <?php foreach ($goals as $goal): ?>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white"><?php echo htmlspecialchars($goal['title']); ?></h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Deadline: <?php echo date('M d, Y', strtotime($goal['deadline'])); ?></p>
                </div>
                <div class="text-center">
                    <div class="text-4xl font-bold text-primary"><?php echo $goal['progress']; ?>%</div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Current</p>
                </div>
            </div>
            
            <!-- Progress Bar -->
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4 mb-4">
                <div class="bg-gradient-to-r from-blue-500 to-green-500 h-4 rounded-full transition-all duration-500" 
                     style="width: <?php echo $goal['progress']; ?>%"></div>
            </div>
            
            <!-- Timeline Chart -->
            <canvas id="goalTimeline<?php echo $goal['id']; ?>" height="100"></canvas>
            
            <!-- Milestones -->
            <div class="mt-4">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-3">Milestones</h4>
                <div class="space-y-2">
                    <?php
                    $milestones = $db->fetchAll("SELECT * FROM goal_milestones WHERE goal_id = ? ORDER BY target_date", [$goal['id']]) ?: [];
                    if (empty($milestones)) {
                        echo '<p class="text-gray-500 dark:text-gray-400 text-sm">No milestones set</p>';
                    } else {
                        foreach ($milestones as $milestone) {
                            $completed = $milestone['completed'] ? 'line-through opacity-50' : '';
                            $icon = $milestone['completed'] ? 'check-circle text-green-600' : 'circle text-gray-400';
                            echo '<div class="flex items-center gap-3 ' . $completed . '">';
                            echo '<i class="fas fa-' . $icon . '"></i>';
                            echo '<span class="text-sm text-gray-700 dark:text-gray-300">' . htmlspecialchars($milestone['title']) . '</span>';
                            echo '<span class="text-xs text-gray-500 ml-auto">' . date('M d', strtotime($milestone['target_date'])) . '</span>';
                            echo '</div>';
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        
        <?php if (empty($goals)): ?>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-12 text-center">
            <i class="fas fa-bullseye text-6xl text-gray-400 mb-4"></i>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">No Active Goals</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-6">Create goals to see your progress visualized here</p>
            <a href="/goals.php" class="btn bg-primary hover:bg-primary-dark text-white px-6 py-3 rounded-lg inline-flex items-center gap-2">
                <i class="fas fa-plus"></i>
                <span>Create Your First Goal</span>
            </a>
        </div>
        <?php endif; ?>
    </div>

    <!-- Analytics Summary -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
            <i class="fas fa-chart-bar"></i> Goal Analytics Summary
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="text-center">
                <p class="text-3xl font-bold text-blue-600"><?php echo count($goals); ?></p>
                <p class="text-sm text-gray-600 dark:text-gray-400">Active Goals</p>
            </div>
            <div class="text-center">
                <p class="text-3xl font-bold text-green-600">
                    <?php echo count(array_filter($goals, fn($g) => $g['progress'] >= 75)); ?>
                </p>
                <p class="text-sm text-gray-600 dark:text-gray-400">Near Completion</p>
            </div>
            <div class="text-center">
                <p class="text-3xl font-bold text-yellow-600">
                    <?php 
                    $avgProgress = !empty($goals) ? array_sum(array_column($goals, 'progress')) / count($goals) : 0;
                    echo round($avgProgress);
                    ?>%
                </p>
                <p class="text-sm text-gray-600 dark:text-gray-400">Avg Progress</p>
            </div>
            <div class="text-center">
                <p class="text-3xl font-bold text-purple-600">
                    <?php echo count(array_filter($goals, fn($g) => strtotime($g['deadline']) < strtotime('+7 days'))); ?>
                </p>
                <p class="text-sm text-gray-600 dark:text-gray-400">Due This Week</p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@kurkle/color@0.3.0"></script>
<script>
const goals = <?php echo json_encode($goals); ?>;

// Generate timeline charts for each goal
goals.forEach(goal => {
    const ctx = document.getElementById(`goalTimeline${goal.id}`);
    if (!ctx) return;
    
    // Simulate progress history data
    const days = 30;
    const data = [];
    const labels = [];
    const currentProgress = parseInt(goal.progress);
    
    for (let i = days; i >= 0; i--) {
        const date = new Date();
        date.setDate(date.getDate() - i);
        labels.push(date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }));
        
        // Simulate gradual progress
        const progressPoint = Math.max(0, currentProgress - (i * (currentProgress / days)));
        data.push(Math.round(progressPoint));
    }
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Progress %',
                data: data,
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
                    max: 100,
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            }
        }
    });
});

// Generate progress heatmap
function generateHeatmap() {
    const container = document.getElementById('progressHeatmap');
    const days = 30;
    const weeks = Math.ceil(days / 7);
    
    let html = '<div class="flex gap-1">';
    
    for (let week = 0; week < weeks; week++) {
        html += '<div class="flex flex-col gap-1">';
        for (let day = 0; day < 7; day++) {
            const dayIndex = week * 7 + day;
            if (dayIndex >= days) break;
            
            // Simulate activity level
            const activity = Math.floor(Math.random() * 5);
            const colors = [
                'bg-gray-200 dark:bg-gray-700',
                'bg-green-200 dark:bg-green-900',
                'bg-green-300 dark:bg-green-700',
                'bg-green-400 dark:bg-green-600',
                'bg-green-500 dark:bg-green-500'
            ];
            
            html += `<div class="w-4 h-4 rounded ${colors[activity]}" title="Day ${days - dayIndex}: ${activity * 25}% activity"></div>`;
        }
        html += '</div>';
    }
    
    html += '</div>';
    container.innerHTML = html;
}

generateHeatmap();
</script>

<?php include 'includes/footer.php'; ?>
