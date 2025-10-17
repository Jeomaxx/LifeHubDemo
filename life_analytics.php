<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$pageTitle = 'Life Analytics Center';
include 'includes/header.php';
?>

<div class="page-header">
    <h1><i class="fas fa-chart-pie"></i> Life Analytics Center</h1>
    <p class="page-subtitle">Comprehensive view of all your life metrics</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon bg-blue">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <div class="stat-info">
            <h3>
                <?php
                $netBalance = $db->fetchColumn("SELECT COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE -amount END), 0) FROM finance WHERE user_id = ? AND EXTRACT(MONTH FROM date) = EXTRACT(MONTH FROM CURRENT_DATE)", [$userId]);
                echo formatCurrency($netBalance);
                ?>
            </h3>
            <p>Net Balance (Month)</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-green">
            <i class="fas fa-tasks"></i>
        </div>
        <div class="stat-info">
            <h3>
                <?php
                $completedTasks = $db->fetchColumn("SELECT COUNT(*) FROM tasks WHERE user_id = ? AND status = 'completed'", [$userId]);
                echo $completedTasks ?: 0;
                ?>
            </h3>
            <p>Total Tasks Completed</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-purple">
            <i class="fas fa-heart"></i>
        </div>
        <div class="stat-info">
            <h3>
                <?php
                $avgMood = $db->fetchColumn("SELECT COALESCE(AVG(mood_rating), 0) FROM mood_entries WHERE user_id = ? AND mood_date >= CURRENT_DATE - INTERVAL '30 days'", [$userId]);
                echo number_format($avgMood, 1) . '/10';
                ?>
            </h3>
            <p>Avg Mood (30d)</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-orange">
            <i class="fas fa-graduation-cap"></i>
        </div>
        <div class="stat-info">
            <h3>
                <?php
                $completedCourses = $db->fetchColumn("SELECT COUNT(*) FROM learning_courses WHERE user_id = ? AND status = 'completed'", [$userId]);
                echo $completedCourses ?: 0;
                ?>
            </h3>
            <p>Courses Completed</p>
        </div>
    </div>
</div>

<div class="dashboard-grid">
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-wallet"></i> Financial Overview</h3>
        </div>
        <div class="card-body">
            <canvas id="financialChart"></canvas>
        </div>
    </div>
    
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-smile"></i> Wellness Metrics</h3>
        </div>
        <div class="card-body">
            <canvas id="wellnessChart"></canvas>
        </div>
    </div>
</div>

<div class="dashboard-card full-width">
    <div class="card-header">
        <h3><i class="fas fa-chart-line"></i> Life Score Index</h3>
    </div>
    <div class="card-body">
        <?php
        $financialScore = min(100, ($netBalance > 0 ? 80 : 40));
        $productivityScore = min(100, ($completedTasks / max(1, date('d'))) * 10);
        $wellnessScore = $avgMood * 10;
        $learningScore = $completedCourses * 20;
        $lifeScore = ($financialScore + $productivityScore + $wellnessScore + $learningScore) / 4;
        ?>
        <div class="life-score-container">
            <div class="life-score-circle">
                <svg viewBox="0 0 200 200">
                    <circle cx="100" cy="100" r="90" fill="none" stroke="#e5e7eb" stroke-width="20"/>
                    <circle cx="100" cy="100" r="90" fill="none" stroke="url(#gradient)" stroke-width="20" 
                            stroke-dasharray="<?php echo ($lifeScore / 100) * 565; ?> 565" 
                            transform="rotate(-90 100 100)" stroke-linecap="round"/>
                    <defs>
                        <linearGradient id="gradient" x1="0%" y1="0%" x2="100%" y2="0%">
                            <stop offset="0%" style="stop-color:#8b5cf6;stop-opacity:1" />
                            <stop offset="100%" style="stop-color:#3b82f6;stop-opacity:1" />
                        </linearGradient>
                    </defs>
                </svg>
                <div class="life-score-value">
                    <h2><?php echo number_format($lifeScore, 0); ?></h2>
                    <p>Life Score</p>
                </div>
            </div>
            <div class="score-breakdown">
                <div class="score-item">
                    <span>Financial</span>
                    <div class="score-bar">
                        <div class="score-fill bg-blue" style="width: <?php echo $financialScore; ?>%"></div>
                    </div>
                    <span><?php echo number_format($financialScore, 0); ?>%</span>
                </div>
                <div class="score-item">
                    <span>Productivity</span>
                    <div class="score-bar">
                        <div class="score-fill bg-green" style="width: <?php echo $productivityScore; ?>%"></div>
                    </div>
                    <span><?php echo number_format($productivityScore, 0); ?>%</span>
                </div>
                <div class="score-item">
                    <span>Wellness</span>
                    <div class="score-bar">
                        <div class="score-fill bg-purple" style="width: <?php echo $wellnessScore; %>%"></div>
                    </div>
                    <span><?php echo number_format($wellnessScore, 0); ?>%</span>
                </div>
                <div class="score-item">
                    <span>Learning</span>
                    <div class="score-bar">
                        <div class="score-fill bg-orange" style="width: <?php echo $learningScore; ?>%"></div>
                    </div>
                    <span><?php echo number_format($learningScore, 0); ?>%</span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.life-score-container {
    display: flex;
    gap: 48px;
    align-items: center;
    padding: 24px;
}

.life-score-circle {
    position: relative;
    width: 200px;
    height: 200px;
}

.life-score-value {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
}

.life-score-value h2 {
    font-size: 48px;
    font-weight: 700;
    background: linear-gradient(135deg, #8b5cf6, #3b82f6);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin: 0;
}

.life-score-value p {
    margin: 4px 0 0;
    color: var(--text-secondary);
}

.score-breakdown {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.score-item {
    display: flex;
    align-items: center;
    gap: 16px;
}

.score-item > span:first-child {
    width: 100px;
    font-weight: 600;
}

.score-bar {
    flex: 1;
    height: 24px;
    background: var(--border);
    border-radius: 12px;
    overflow: hidden;
}

.score-fill {
    height: 100%;
    transition: width 0.3s;
}

@media (max-width: 768px) {
    .life-score-container {
        flex-direction: column;
    }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const financialCtx = document.getElementById('financialChart');
    if (financialCtx) {
        new Chart(financialCtx, {
            type: 'doughnut',
            data: {
                labels: ['Income', 'Expenses', 'Savings'],
                datasets: [{
                    data: [60, 30, 10],
                    backgroundColor: ['#10b981', '#ef4444', '#3b82f6']
                }]
            }
        });
    }
    
    const wellnessCtx = document.getElementById('wellnessChart');
    if (wellnessCtx) {
        new Chart(wellnessCtx, {
            type: 'radar',
            data: {
                labels: ['Sleep', 'Mood', 'Exercise', 'Nutrition', 'Meditation'],
                datasets: [{
                    label: 'Wellness Score',
                    data: [<?php echo $avgMood * 10; ?>, <?php echo $avgMood * 10; ?>, 70, 65, 75],
                    backgroundColor: 'rgba(139, 92, 246, 0.2)',
                    borderColor: 'rgb(139, 92, 246)',
                    pointBackgroundColor: 'rgb(139, 92, 246)'
                }]
            },
            options: {
                scales: {
                    r: {
                        beginAtZero: true,
                        max: 100
                    }
                }
            }
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>
