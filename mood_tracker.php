<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$recentEntries = $db->fetchAll("SELECT * FROM mood_entries WHERE user_id = ? ORDER BY mood_date DESC LIMIT 10", [$userId]);
$avgMood = $db->fetchColumn("SELECT COALESCE(AVG(mood_rating), 0) FROM mood_entries WHERE user_id = ? AND mood_date >= CURRENT_DATE - INTERVAL '7 days'", [$userId]);
$entriesThisWeek = $db->fetchColumn("SELECT COUNT(*) FROM mood_entries WHERE user_id = ? AND mood_date >= CURRENT_DATE - INTERVAL '7 days'", [$userId]);

$pageTitle = 'Mood Tracker';
$extraScripts = ['/assets/js/mood_tracker.js'];
include 'includes/header.php';
?>

<div class="page-header">
    <h1><i class="fas fa-smile"></i> Mood & Emotional Insights</h1>
    <p class="page-subtitle">Track your emotional well-being with AI-powered insights</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon bg-purple">
            <i class="fas fa-heart"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo number_format($avgMood, 1); ?>/10</h3>
            <p>Average Mood (7 Days)</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-blue">
            <i class="fas fa-calendar-check"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $entriesThisWeek; ?></h3>
            <p>Entries This Week</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-green">
            <i class="fas fa-brain"></i>
        </div>
        <div class="stat-info">
            <h3 id="ai-sentiment">-</h3>
            <p>AI Sentiment</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-orange">
            <i class="fas fa-chart-line"></i>
        </div>
        <div class="stat-info">
            <h3 id="mood-trend">-</h3>
            <p>Mood Trend</p>
        </div>
    </div>
</div>

<div class="action-bar">
    <button class="btn btn-primary" onclick="showAddMoodModal()">
        <i class="fas fa-plus"></i> Log Mood
    </button>
    <button class="btn btn-secondary" onclick="loadTrends()">
        <i class="fas fa-sync-alt"></i> Refresh Trends
    </button>
</div>

<div class="dashboard-grid">
    <div class="dashboard-card full-width">
        <div class="card-header">
            <h3><i class="fas fa-chart-area"></i> Mood Trends (30 Days)</h3>
        </div>
        <div class="card-body">
            <canvas id="moodTrendsChart"></canvas>
        </div>
    </div>
    
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-list"></i> Recent Mood Entries</h3>
        </div>
        <div class="card-body">
            <div class="mood-list" id="mood-list">
                <?php if (empty($recentEntries)): ?>
                <p class="text-muted">No mood entries yet. Start tracking your emotions!</p>
                <?php else: ?>
                <?php foreach ($recentEntries as $entry): ?>
                <div class="mood-entry">
                    <div class="mood-header">
                        <span class="mood-emoji"><?php echo $entry['mood_emoji']; ?></span>
                        <span class="mood-rating"><?php echo $entry['mood_rating']; ?>/10</span>
                        <span class="mood-date"><?php echo date('M d, Y', strtotime($entry['mood_date'])); ?></span>
                    </div>
                    <?php if ($entry['mood_notes']): ?>
                    <p class="mood-notes"><?php echo sanitize($entry['mood_notes']); ?></p>
                    <?php endif; ?>
                    <?php if ($entry['ai_insights']): ?>
                    <div class="ai-insights">
                        <i class="fas fa-robot"></i> <?php echo sanitize($entry['ai_insights']); ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-lightbulb"></i> AI Recommendations</h3>
        </div>
        <div class="card-body">
            <div id="ai-recommendations">
                <p class="text-muted">Log your moods to get personalized AI recommendations</p>
            </div>
        </div>
    </div>
</div>

<div id="addMoodModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class="fas fa-smile"></i> Log Your Mood</h2>
            <button class="modal-close" onclick="closeModal('addMoodModal')">&times;</button>
        </div>
        <div class="modal-body">
            <form id="addMoodForm" onsubmit="addMoodEntry(event)">
                <div class="form-group">
                    <label>Date</label>
                    <input type="date" id="mood_date" name="mood_date" class="form-control" required value="<?php echo date('Y-m-d'); ?>">
                </div>
                
                <div class="form-group">
                    <label>Mood Rating (1-10)</label>
                    <input type="range" id="mood_rating" name="mood_rating" class="form-control" min="1" max="10" value="5" oninput="document.getElementById('rating-value').textContent = this.value">
                    <div class="text-center mt-2">
                        <strong id="rating-value">5</strong>/10
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Mood Emoji</label>
                    <div class="emoji-selector">
                        <button type="button" class="emoji-btn" onclick="selectEmoji('😢')">😢</button>
                        <button type="button" class="emoji-btn" onclick="selectEmoji('😐')">😐</button>
                        <button type="button" class="emoji-btn" onclick="selectEmoji('🙂')">🙂</button>
                        <button type="button" class="emoji-btn" onclick="selectEmoji('😊')">😊</button>
                        <button type="button" class="emoji-btn" onclick="selectEmoji('😁')">😁</button>
                    </div>
                    <input type="hidden" id="mood_emoji" name="mood_emoji" value="😐">
                </div>
                
                <div class="form-group">
                    <label>Notes (Optional)</label>
                    <textarea id="mood_notes" name="mood_notes" class="form-control" rows="4" placeholder="How are you feeling today?"></textarea>
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-robot"></i> AI will analyze your notes to provide emotional insights
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addMoodModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Mood Entry</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.mood-entry {
    padding: 16px;
    border-bottom: 1px solid var(--border);
    margin-bottom: 12px;
}

.mood-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 8px;
}

.mood-emoji {
    font-size: 2rem;
}

.mood-rating {
    font-weight: 600;
    color: var(--primary);
}

.mood-date {
    color: var(--text-light);
    font-size: 0.875rem;
    margin-left: auto;
}

.mood-notes {
    color: var(--text);
    margin: 8px 0;
}

.ai-insights {
    background: rgba(99, 102, 241, 0.1);
    padding: 8px 12px;
    border-radius: 6px;
    color: var(--secondary);
    font-size: 0.875rem;
}

.emoji-selector {
    display: flex;
    gap: 8px;
    justify-content: center;
}

.emoji-btn {
    font-size: 2rem;
    padding: 8px 16px;
    border: 2px solid var(--border);
    border-radius: 8px;
    background: var(--card-bg);
    cursor: pointer;
    transition: all 0.3s;
}

.emoji-btn:hover, .emoji-btn.selected {
    border-color: var(--primary);
    transform: scale(1.1);
}
</style>

<?php include 'includes/footer.php'; ?>
