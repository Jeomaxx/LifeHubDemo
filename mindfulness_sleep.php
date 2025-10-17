<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$recentSleep = $db->fetchAll("SELECT * FROM sleep_logs WHERE user_id = ? ORDER BY sleep_date DESC LIMIT 7", [$userId]) ?: [];
$recentMeditation = $db->fetchAll("SELECT * FROM meditation_sessions WHERE user_id = ? ORDER BY session_date DESC LIMIT 7", [$userId]) ?: [];

$avgSleepQuality = $db->fetchColumn("SELECT COALESCE(AVG(sleep_quality_rating), 0) FROM sleep_logs WHERE user_id = ? AND sleep_date >= CURRENT_DATE - INTERVAL '7 days'", [$userId]);
$totalMeditationMinutes = $db->fetchColumn("SELECT COALESCE(SUM(duration_minutes), 0) FROM meditation_sessions WHERE user_id = ? AND session_date >= CURRENT_DATE - INTERVAL '7 days'", [$userId]);

$pageTitle = 'Mindfulness & Sleep Tracker';
$extraScripts = ['/assets/js/mindfulness_sleep.js'];
include 'includes/header.php';
?>

<div class="page-header">
    <h1><i class="fas fa-spa"></i> Mindfulness & Sleep Tracker</h1>
    <p class="page-subtitle">Track your rest quality and meditation practice</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon bg-purple">
            <i class="fas fa-bed"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo number_format($avgSleepQuality, 1); ?>/10</h3>
            <p>Avg Sleep Quality (7d)</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-blue">
            <i class="fas fa-moon"></i>
        </div>
        <div class="stat-info">
            <h3>
                <?php
                $avgSleepHours = $db->fetchColumn("SELECT COALESCE(AVG(sleep_duration_hours), 0) FROM sleep_logs WHERE user_id = ? AND sleep_date >= CURRENT_DATE - INTERVAL '7 days'", [$userId]);
                echo number_format($avgSleepHours, 1) . 'h';
                ?>
            </h3>
            <p>Avg Sleep Duration</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-green">
            <i class="fas fa-om"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $totalMeditationMinutes; ?> min</h3>
            <p>Meditation This Week</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-orange">
            <i class="fas fa-fire"></i>
        </div>
        <div class="stat-info">
            <h3>
                <?php
                $streak = 0;
                $checkDate = date('Y-m-d');
                for ($i = 0; $i < 30; $i++) {
                    $hasEntry = $db->fetchColumn("SELECT COUNT(*) FROM meditation_sessions WHERE user_id = ? AND session_date = ?", [$userId, $checkDate]);
                    if ($hasEntry > 0) {
                        $streak++;
                        $checkDate = date('Y-m-d', strtotime($checkDate . ' -1 day'));
                    } else {
                        break;
                    }
                }
                echo $streak;
                ?>
            </h3>
            <p>Day Streak</p>
        </div>
    </div>
</div>

<div class="action-bar">
    <button class="btn btn-primary" onclick="showLogSleepModal()">
        <i class="fas fa-bed"></i> Log Sleep
    </button>
    <button class="btn btn-secondary" onclick="showLogMeditationModal()">
        <i class="fas fa-om"></i> Log Meditation
    </button>
    <button class="btn btn-success" onclick="viewInsights()">
        <i class="fas fa-brain"></i> AI Insights
    </button>
</div>

<!-- Sleep Log Chart -->
<div class="dashboard-card full-width">
    <div class="card-header">
        <h3><i class="fas fa-chart-line"></i> Sleep Quality Trend (7 Days)</h3>
    </div>
    <div class="card-body">
        <canvas id="sleepChart"></canvas>
    </div>
</div>

<div class="dashboard-grid">
    <!-- Recent Sleep Logs -->
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-moon"></i> Recent Sleep Logs</h3>
        </div>
        <div class="card-body">
            <?php if (empty($recentSleep)): ?>
            <p class="text-muted">No sleep logs yet. Start tracking your sleep!</p>
            <?php else: ?>
            <div class="log-list">
                <?php foreach ($recentSleep as $sleep): ?>
                <div class="log-item">
                    <div class="log-date"><?php echo date('M d', strtotime($sleep['sleep_date'])); ?></div>
                    <div class="log-details">
                        <span class="badge badge-blue"><?php echo $sleep['sleep_duration_hours']; ?>h</span>
                        <span class="quality-rating">
                            <?php
                            $rating = $sleep['sleep_quality_rating'];
                            echo str_repeat('★', $rating) . str_repeat('☆', 10 - $rating);
                            ?>
                        </span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Recent Meditation Sessions -->
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-om"></i> Recent Meditation</h3>
        </div>
        <div class="card-body">
            <?php if (empty($recentMeditation)): ?>
            <p class="text-muted">No meditation sessions yet. Start your practice!</p>
            <?php else: ?>
            <div class="log-list">
                <?php foreach ($recentMeditation as $med): ?>
                <div class="log-item">
                    <div class="log-date"><?php echo date('M d', strtotime($med['session_date'])); ?></div>
                    <div class="log-details">
                        <span class="badge badge-purple"><?php echo $med['duration_minutes']; ?> min</span>
                        <span class="meditation-type"><?php echo sanitize($med['meditation_type']); ?></span>
                        <?php if ($med['mood_after'] && $med['mood_before']): ?>
                        <span class="mood-change">
                            <?php echo ($med['mood_after'] - $med['mood_before'] >= 0 ? '+' : '') . ($med['mood_after'] - $med['mood_before']); ?>
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Log Sleep Modal -->
<div id="logSleepModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class="fas fa-bed"></i> Log Sleep</h2>
            <button class="modal-close" onclick="closeModal('logSleepModal')">&times;</button>
        </div>
        <div class="modal-body">
            <form id="logSleepForm" onsubmit="logSleep(event)">
                <div class="form-group">
                    <label>Date</label>
                    <input type="date" name="sleep_date" class="form-control" value="<?php echo date('Y-m-d', strtotime('-1 day')); ?>" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Sleep Start Time</label>
                        <input type="time" name="sleep_start_time" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Sleep End Time</label>
                        <input type="time" name="sleep_end_time" class="form-control" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Sleep Quality (1-10)</label>
                    <input type="range" name="sleep_quality_rating" class="form-range" min="1" max="10" value="7" oninput="this.nextElementSibling.textContent = this.value">
                    <span class="range-value">7</span>
                </div>
                <div class="form-group">
                    <label>Notes (Optional)</label>
                    <textarea name="notes" class="form-control" rows="3"></textarea>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('logSleepModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Log Sleep</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Log Meditation Modal -->
<div id="logMeditationModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class="fas fa-om"></i> Log Meditation</h2>
            <button class="modal-close" onclick="closeModal('logMeditationModal')">&times;</button>
        </div>
        <div class="modal-body">
            <form id="logMeditationForm" onsubmit="logMeditation(event)">
                <div class="form-group">
                    <label>Date</label>
                    <input type="date" name="session_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="form-group">
                    <label>Duration (minutes)</label>
                    <input type="number" name="duration_minutes" class="form-control" min="1" value="10" required>
                </div>
                <div class="form-group">
                    <label>Meditation Type</label>
                    <select name="meditation_type" class="form-control" required>
                        <option value="Mindfulness">Mindfulness</option>
                        <option value="Breathing">Breathing</option>
                        <option value="Body Scan">Body Scan</option>
                        <option value="Loving-kindness">Loving-kindness</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Mood Before (1-10)</label>
                        <input type="range" name="mood_before" class="form-range" min="1" max="10" value="5" oninput="this.nextElementSibling.textContent = this.value">
                        <span class="range-value">5</span>
                    </div>
                    <div class="form-group">
                        <label>Mood After (1-10)</label>
                        <input type="range" name="mood_after" class="form-range" min="1" max="10" value="7" oninput="this.nextElementSibling.textContent = this.value">
                        <span class="range-value">7</span>
                    </div>
                </div>
                <div class="form-group">
                    <label>Notes (Optional)</label>
                    <textarea name="notes" class="form-control" rows="3"></textarea>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('logMeditationModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Log Session</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.form-range {
    width: 100%;
    margin: 8px 0;
}

.range-value {
    display: inline-block;
    font-weight: 600;
    color: var(--primary);
    margin-left: 8px;
}

.log-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.log-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px;
    background: var(--bg-secondary);
    border-radius: 8px;
}

.log-date {
    font-weight: 600;
    color: var(--primary);
}

.log-details {
    display: flex;
    align-items: center;
    gap: 12px;
}

.quality-rating {
    color: #fbbf24;
}

.meditation-type {
    color: var(--text-secondary);
    font-size: 0.9em;
}

.mood-change {
    padding: 2px 8px;
    background: rgba(34, 197, 94, 0.1);
    color: var(--success);
    border-radius: 12px;
    font-size: 0.85em;
    font-weight: 600;
}
</style>

<?php include 'includes/footer.php'; ?>
