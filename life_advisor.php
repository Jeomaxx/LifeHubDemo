<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$todayBriefing = $db->fetchOne("SELECT * FROM life_advisor_briefings WHERE user_id = ? AND briefing_date = CURRENT_DATE", [$userId]);
$recentBriefings = $db->fetchAll("SELECT * FROM life_advisor_briefings WHERE user_id = ? ORDER BY briefing_date DESC LIMIT 7", [$userId]) ?: [];

$pageTitle = 'AI Life Advisor';
$extraScripts = ['/assets/js/life_advisor.js'];
include 'includes/header.php';
?>

<div class="page-header">
    <h1><i class="fas fa-robot"></i> AI Life Advisor</h1>
    <p class="page-subtitle">Your intelligent daily life operating system</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon bg-purple">
            <i class="fas fa-calendar-day"></i>
        </div>
        <div class="stat-info">
            <h3 id="daily-priority">
                <?php echo $todayBriefing ? 'Ready' : 'Generate'; ?>
            </h3>
            <p>Today's Briefing</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-blue">
            <i class="fas fa-tasks"></i>
        </div>
        <div class="stat-info">
            <h3 id="action-count">
                <?php 
                if ($todayBriefing) {
                    $actionCount = $db->fetchColumn("SELECT COUNT(*) FROM life_advisor_actions WHERE briefing_id = ? AND is_completed = FALSE", [$todayBriefing['id']]);
                    echo $actionCount ?: 0;
                } else {
                    echo '0';
                }
                ?>
            </h3>
            <p>Pending Actions</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-green">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-info">
            <h3 id="completed-count">
                <?php 
                if ($todayBriefing) {
                    $completedCount = $db->fetchColumn("SELECT COUNT(*) FROM life_advisor_actions WHERE briefing_id = ? AND is_completed = TRUE", [$todayBriefing['id']]);
                    echo $completedCount ?: 0;
                } else {
                    echo '0';
                }
                ?>
            </h3>
            <p>Completed Today</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-orange">
            <i class="fas fa-fire"></i>
        </div>
        <div class="stat-info">
            <h3 id="streak-count">7</h3>
            <p>Day Streak</p>
        </div>
    </div>
</div>

<div class="action-bar">
    <button class="btn btn-primary" onclick="generateDailyBriefing()">
        <i class="fas fa-magic"></i> Generate Daily Briefing
    </button>
    <button class="btn btn-secondary" onclick="playAudioBriefing()" <?php echo !$todayBriefing ? 'disabled' : ''; ?>>
        <i class="fas fa-volume-up"></i> Play Audio
    </button>
    <button class="btn btn-success" onclick="exportBriefing()">
        <i class="fas fa-download"></i> Export
    </button>
</div>

<!-- Today's Briefing Card -->
<?php if ($todayBriefing): ?>
<div class="dashboard-card full-width ai-briefing-card">
    <div class="card-header">
        <h3><i class="fas fa-sunrise"></i> Good Morning Briefing - <?php echo date('l, F j, Y'); ?></h3>
        <span class="badge badge-success">AI Generated</span>
    </div>
    <div class="card-body">
        <div class="briefing-content">
            <div class="briefing-section">
                <h4><i class="fas fa-align-left"></i> Daily Summary</h4>
                <p><?php echo nl2br(sanitize($todayBriefing['daily_summary'])); ?></p>
            </div>
            
            <?php if ($todayBriefing['ai_recommendations']): ?>
            <div class="briefing-section">
                <h4><i class="fas fa-lightbulb"></i> AI Recommendations</h4>
                <p><?php echo nl2br(sanitize($todayBriefing['ai_recommendations'])); ?></p>
            </div>
            <?php endif; ?>
            
            <div class="briefing-section">
                <h4><i class="fas fa-list-check"></i> Action Items</h4>
                <div id="action-items-list">
                    <?php
                    $actions = $db->fetchAll("SELECT * FROM life_advisor_actions WHERE briefing_id = ? ORDER BY is_completed ASC, id ASC", [$todayBriefing['id']]) ?: [];
                    if (empty($actions)):
                    ?>
                        <p class="text-muted">No action items for today</p>
                    <?php else: ?>
                        <?php foreach ($actions as $action): ?>
                        <div class="action-item <?php echo $action['is_completed'] ? 'completed' : ''; ?>" data-action-id="<?php echo $action['id']; ?>">
                            <input type="checkbox" 
                                   <?php echo $action['is_completed'] ? 'checked' : ''; ?> 
                                   onchange="toggleAction(<?php echo $action['id']; ?>, this.checked)">
                            <span><?php echo sanitize($action['action_text']); ?></span>
                            <span class="action-type badge badge-<?php echo $action['action_type'] == 'urgent' ? 'danger' : 'info'; ?>">
                                <?php echo ucfirst($action['action_type'] ?: 'normal'); ?>
                            </span>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<div class="dashboard-card full-width text-center py-8">
    <i class="fas fa-robot fa-4x text-gray-400 mb-4"></i>
    <h3 class="text-gray-600 dark:text-gray-400">No briefing for today yet</h3>
    <p class="text-gray-500 mb-4">Generate your daily briefing to get personalized insights and action items</p>
    <button class="btn btn-primary" onclick="generateDailyBriefing()">
        <i class="fas fa-magic"></i> Generate Daily Briefing
    </button>
</div>
<?php endif; ?>

<!-- Recent Briefings -->
<div class="dashboard-card full-width">
    <div class="card-header">
        <h3><i class="fas fa-history"></i> Recent Briefings (7 Days)</h3>
    </div>
    <div class="card-body">
        <?php if (empty($recentBriefings)): ?>
        <p class="text-muted">No briefings yet. Start by generating your first daily briefing!</p>
        <?php else: ?>
        <div class="briefing-history">
            <?php foreach ($recentBriefings as $briefing): ?>
            <div class="briefing-history-item">
                <div class="briefing-date">
                    <i class="fas fa-calendar"></i>
                    <?php echo date('M d, Y', strtotime($briefing['briefing_date'])); ?>
                </div>
                <div class="briefing-summary">
                    <?php echo substr(sanitize($briefing['daily_summary']), 0, 150) . '...'; ?>
                </div>
                <button class="btn btn-sm btn-secondary" onclick="viewBriefing(<?php echo $briefing['id']; ?>)">
                    <i class="fas fa-eye"></i> View
                </button>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.ai-briefing-card {
    background: linear-gradient(135deg, rgba(139, 92, 246, 0.1) 0%, rgba(59, 130, 246, 0.1) 100%);
    border-left: 4px solid var(--primary);
}

.briefing-content {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.briefing-section h4 {
    color: var(--primary);
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.action-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: var(--card-bg);
    border-radius: 8px;
    margin-bottom: 8px;
    transition: all 0.3s;
}

.action-item.completed {
    opacity: 0.6;
    text-decoration: line-through;
}

.action-item input[type="checkbox"] {
    width: 20px;
    height: 20px;
    cursor: pointer;
}

.action-type {
    margin-left: auto;
}

.briefing-history-item {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px;
    border: 1px solid var(--border);
    border-radius: 8px;
    margin-bottom: 12px;
}

.briefing-date {
    font-weight: 600;
    color: var(--primary);
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 120px;
}

.briefing-summary {
    flex: 1;
    color: var(--text-secondary);
}
</style>

<?php include 'includes/footer.php'; ?>
