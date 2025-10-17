<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$activeGoals = $db->fetchAll("SELECT * FROM smart_goals WHERE user_id = ? AND status = 'active' ORDER BY time_bound_deadline ASC", [$userId]);
$activeGoals = is_array($activeGoals) && $activeGoals !== false ? $activeGoals : [];
$completedGoals = $db->fetchColumn("SELECT COUNT(*) FROM smart_goals WHERE user_id = ? AND status = 'completed'", [$userId]);
$completedGoals = is_numeric($completedGoals) ? (int)$completedGoals : 0;

$pageTitle = 'SMART Goals';
$extraScripts = ['/assets/js/smart_goals.js'];
include 'includes/header.php';
?>

<div class="page-header">
    <h1><i class="fas fa-bullseye"></i> Goal Achievement Engine</h1>
    <p class="page-subtitle">Track and optimize your SMART goals with AI-powered insights</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon bg-blue">
            <i class="fas fa-tasks"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo count($activeGoals); ?></h3>
            <p>Active Goals</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-green">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $completedGoals; ?></h3>
            <p>Completed Goals</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-purple">
            <i class="fas fa-chart-line"></i>
        </div>
        <div class="stat-info">
            <h3 id="avg-progress">-</h3>
            <p>Average Progress</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-orange">
            <i class="fas fa-trophy"></i>
        </div>
        <div class="stat-info">
            <h3 id="success-rate">-</h3>
            <p>Success Rate</p>
        </div>
    </div>
</div>

<div class="action-bar">
    <button class="btn btn-primary" onclick="showAddGoalModal()">
        <i class="fas fa-plus"></i> Create SMART Goal
    </button>
</div>

<div class="dashboard-grid">
    <div class="dashboard-card full-width">
        <div class="card-header">
            <h3><i class="fas fa-list"></i> Active Goals</h3>
        </div>
        <div class="card-body">
            <div id="goals-container">
                <?php if (empty($activeGoals)): ?>
                <p class="text-muted">No active goals. Create your first SMART goal!</p>
                <?php else: ?>
                <?php foreach ($activeGoals as $goal): ?>
                <div class="goal-card">
                    <div class="goal-header">
                        <h4><?php echo sanitize($goal['title']); ?></h4>
                        <span class="badge badge-<?php echo $goal['goal_type']; ?>"><?php echo ucfirst($goal['goal_type']); ?></span>
                    </div>
                    <div class="goal-smart">
                        <p><strong>Specific:</strong> <?php echo sanitize($goal['specific_target']); ?></p>
                        <p><strong>Measurable:</strong> <?php echo sanitize($goal['measurable_metric']); ?></p>
                        <p><strong>Deadline:</strong> <?php echo date('M d, Y', strtotime($goal['time_bound_deadline'])); ?></p>
                    </div>
                    <div class="progress-container">
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo $goal['current_progress']; ?>%"></div>
                        </div>
                        <span class="progress-text"><?php echo $goal['current_progress']; ?>%</span>
                    </div>
                    <?php if ($goal['ai_feedback']): ?>
                    <div class="ai-feedback">
                        <i class="fas fa-robot"></i> <?php echo sanitize($goal['ai_feedback']); ?>
                    </div>
                    <?php endif; ?>
                    <div class="goal-actions">
                        <button class="btn btn-sm btn-primary" onclick="updateProgress(<?php echo $goal['id']; ?>, <?php echo $goal['current_progress']; ?>)">
                            <i class="fas fa-edit"></i> Update Progress
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteGoal(<?php echo $goal['id']; ?>)">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div id="addGoalModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class="fas fa-bullseye"></i> Create SMART Goal</h2>
            <button class="modal-close" onclick="closeModal('addGoalModal')">&times;</button>
        </div>
        <div class="modal-body">
            <form id="addGoalForm" onsubmit="addGoal(event)">
                <div class="form-group">
                    <label>Goal Type</label>
                    <select id="goal_type" name="goal_type" class="form-control" required>
                        <option value="finance">Financial</option>
                        <option value="health">Health & Fitness</option>
                        <option value="productivity">Productivity</option>
                        <option value="personal">Personal Development</option>
                        <option value="career">Career</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Goal Title</label>
                    <input type="text" id="title" name="title" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>Specific Target</label>
                    <input type="text" id="specific_target" name="specific_target" class="form-control" required placeholder="What exactly do you want to achieve?">
                </div>
                
                <div class="form-group">
                    <label>Measurable Metric</label>
                    <input type="text" id="measurable_metric" name="measurable_metric" class="form-control" required placeholder="How will you measure progress?">
                </div>
                
                <div class="form-group">
                    <label>Achievable Plan</label>
                    <textarea id="achievable_plan" name="achievable_plan" class="form-control" rows="3" required placeholder="How will you achieve this?"></textarea>
                </div>
                
                <div class="form-group">
                    <label>Relevant Reason</label>
                    <textarea id="relevant_reason" name="relevant_reason" class="form-control" rows="2" required placeholder="Why is this important to you?"></textarea>
                </div>
                
                <div class="form-group">
                    <label>Time-Bound Deadline</label>
                    <input type="date" id="time_bound_deadline" name="time_bound_deadline" class="form-control" required>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addGoalModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Goal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.goal-card {
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 16px;
    background: var(--card-bg);
}

.goal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}

.goal-smart {
    margin: 16px 0;
    color: var(--text-light);
}

.goal-smart p {
    margin: 8px 0;
}

.progress-container {
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 16px 0;
}

.progress-bar {
    flex: 1;
    height: 24px;
    background: var(--light);
    border-radius: 12px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--primary), var(--secondary));
    transition: width 0.3s;
}

.progress-text {
    font-weight: 600;
    min-width: 40px;
}

.ai-feedback {
    background: rgba(139, 92, 246, 0.1);
    padding: 12px;
    border-radius: 8px;
    margin: 12px 0;
    color: var(--secondary);
}

.goal-actions {
    display: flex;
    gap: 8px;
    margin-top: 12px;
}
</style>

<?php include 'includes/footer.php'; ?>
