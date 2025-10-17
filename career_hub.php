<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$projects = $db->fetchAll("SELECT * FROM career_projects WHERE user_id = ? ORDER BY created_at DESC", [$userId]) ?: [];
$activeProjects = array_filter($projects, fn($p) => $p['status'] == 'active');
$salaryProgress = $db->fetchOne("SELECT * FROM salary_progress WHERE user_id = ? ORDER BY created_at DESC LIMIT 1", [$userId]);

$pageTitle = 'Work & Career Hub';
$extraScripts = ['/assets/js/career_hub.js'];
include 'includes/header.php';
?>

<div class="page-header">
    <h1><i class="fas fa-briefcase"></i> Work & Career Hub</h1>
    <p class="page-subtitle">Manage projects, track time, and achieve career goals</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon bg-blue">
            <i class="fas fa-project-diagram"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo count($activeProjects); ?></h3>
            <p>Active Projects</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-green">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-info">
            <h3 id="total-hours">
                <?php
                $thisWeekHours = $db->fetchColumn("SELECT COALESCE(SUM(hours_logged), 0) FROM time_logs WHERE user_id = ? AND log_date >= CURRENT_DATE - INTERVAL '7 days'", [$userId]);
                echo number_format($thisWeekHours, 1);
                ?>
            </h3>
            <p>Hours This Week</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-purple">
            <i class="fas fa-chart-line"></i>
        </div>
        <div class="stat-info">
            <h3>
                <?php
                if ($salaryProgress) {
                    $percentage = ($salaryProgress['current_salary'] / $salaryProgress['target_salary']) * 100;
                    echo number_format($percentage, 0) . '%';
                } else {
                    echo '-';
                }
                ?>
            </h3>
            <p>Salary Goal Progress</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-orange">
            <i class="fas fa-tasks"></i>
        </div>
        <div class="stat-info">
            <h3 id="tasks-count">
                <?php
                $taskCount = $db->fetchColumn("SELECT COUNT(*) FROM career_tasks WHERE user_id = ? AND status != 'done'", [$userId]);
                echo $taskCount ?: 0;
                ?>
            </h3>
            <p>Pending Tasks</p>
        </div>
    </div>
</div>

<div class="action-bar">
    <button class="btn btn-primary" onclick="showAddProjectModal()">
        <i class="fas fa-plus"></i> New Project
    </button>
    <button class="btn btn-secondary" onclick="showLogTimeModal()">
        <i class="fas fa-clock"></i> Log Time
    </button>
    <button class="btn btn-success" onclick="showSalaryGoalModal()">
        <i class="fas fa-dollar-sign"></i> Set Salary Goal
    </button>
</div>

<!-- Kanban Board -->
<div class="kanban-board-container">
    <h3 class="mb-4"><i class="fas fa-columns"></i> Kanban Board</h3>
    <div class="kanban-board" id="kanban-board">
        <div class="kanban-column" data-status="todo">
            <div class="kanban-column-header">
                <h4>To Do</h4>
                <span class="badge badge-secondary" id="todo-count">0</span>
            </div>
            <div class="kanban-tasks" id="todo-tasks"></div>
            <button class="btn btn-sm btn-secondary mt-2" onclick="showAddTaskModal('todo')">
                <i class="fas fa-plus"></i> Add Task
            </button>
        </div>
        
        <div class="kanban-column" data-status="in_progress">
            <div class="kanban-column-header">
                <h4>In Progress</h4>
                <span class="badge badge-blue" id="in_progress-count">0</span>
            </div>
            <div class="kanban-tasks" id="in_progress-tasks"></div>
            <button class="btn btn-sm btn-secondary mt-2" onclick="showAddTaskModal('in_progress')">
                <i class="fas fa-plus"></i> Add Task
            </button>
        </div>
        
        <div class="kanban-column" data-status="review">
            <div class="kanban-column-header">
                <h4>Review</h4>
                <span class="badge badge-orange" id="review-count">0</span>
            </div>
            <div class="kanban-tasks" id="review-tasks"></div>
            <button class="btn btn-sm btn-secondary mt-2" onclick="showAddTaskModal('review')">
                <i class="fas fa-plus"></i> Add Task
            </button>
        </div>
        
        <div class="kanban-column" data-status="done">
            <div class="kanban-column-header">
                <h4>Done</h4>
                <span class="badge badge-green" id="done-count">0</span>
            </div>
            <div class="kanban-tasks" id="done-tasks"></div>
        </div>
    </div>
</div>

<!-- Projects List -->
<div class="dashboard-card full-width mt-4">
    <div class="card-header">
        <h3><i class="fas fa-folder-open"></i> Projects</h3>
    </div>
    <div class="card-body">
        <?php if (empty($projects)): ?>
        <p class="text-muted">No projects yet. Create your first project to get started!</p>
        <?php else: ?>
        <div class="projects-grid">
            <?php foreach ($projects as $project): ?>
            <div class="project-card" data-project-id="<?php echo $project['id']; ?>">
                <div class="project-header">
                    <h4><?php echo sanitize($project['project_name']); ?></h4>
                    <span class="badge badge-<?php echo $project['status'] == 'active' ? 'success' : 'secondary'; ?>">
                        <?php echo ucfirst($project['status']); ?>
                    </span>
                </div>
                <p class="project-desc"><?php echo sanitize($project['description'] ?: 'No description'); ?></p>
                <div class="project-meta">
                    <span><i class="fas fa-flag"></i> <?php echo ucfirst($project['priority']); ?></span>
                    <span><i class="fas fa-chart-pie"></i> <?php echo $project['progress_percentage']; ?>%</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?php echo $project['progress_percentage']; ?>%"></div>
                </div>
                <div class="project-actions mt-3">
                    <button class="btn btn-sm btn-secondary" onclick="editProject(<?php echo $project['id']; ?>)">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button class="btn btn-sm btn-primary" onclick="viewProjectTasks(<?php echo $project['id']; ?>)">
                        <i class="fas fa-tasks"></i> Tasks
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modals -->
<div id="addProjectModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class="fas fa-plus"></i> New Project</h2>
            <button class="modal-close" onclick="closeModal('addProjectModal')">&times;</button>
        </div>
        <div class="modal-body">
            <form id="addProjectForm" onsubmit="addProject(event)">
                <div class="form-group">
                    <label>Project Name</label>
                    <input type="text" name="project_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label>Priority</label>
                    <select name="priority" class="form-control">
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Start Date</label>
                    <input type="date" name="start_date" class="form-control">
                </div>
                <div class="form-group">
                    <label>End Date</label>
                    <input type="date" name="end_date" class="form-control">
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addProjectModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Project</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="logTimeModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class="fas fa-clock"></i> Log Time</h2>
            <button class="modal-close" onclick="closeModal('logTimeModal')">&times;</button>
        </div>
        <div class="modal-body">
            <form id="logTimeForm" onsubmit="logTime(event)">
                <div class="form-group">
                    <label>Project</label>
                    <select name="project_id" class="form-control">
                        <option value="">No Project</option>
                        <?php foreach ($activeProjects as $project): ?>
                        <option value="<?php echo $project['id']; ?>"><?php echo sanitize($project['project_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Activity Description</label>
                    <input type="text" name="activity_description" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Hours Logged</label>
                    <input type="number" name="hours_logged" class="form-control" step="0.5" min="0.5" required>
                </div>
                <div class="form-group">
                    <label>Date</label>
                    <input type="date" name="log_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('logTimeModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Log Time</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.kanban-board-container {
    margin: 24px 0;
}

.kanban-board {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    padding: 16px;
    background: var(--card-bg);
    border-radius: 8px;
}

.kanban-column {
    background: var(--bg-primary);
    border-radius: 8px;
    padding: 12px;
    min-height: 400px;
}

.kanban-column-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
    padding-bottom: 8px;
    border-bottom: 2px solid var(--border);
}

.kanban-tasks {
    min-height: 200px;
}

.kanban-task {
    background: var(--card-bg);
    border-radius: 6px;
    padding: 12px;
    margin-bottom: 8px;
    cursor: move;
    transition: all 0.3s;
}

.kanban-task:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.kanban-task.dragging {
    opacity: 0.5;
}

.projects-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 16px;
}

.project-card {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 16px;
}

.project-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.project-desc {
    color: var(--text-secondary);
    margin: 8px 0;
}

.project-meta {
    display: flex;
    gap: 16px;
    margin: 12px 0;
    font-size: 0.9em;
    color: var(--text-secondary);
}

.progress-bar {
    height: 6px;
    background: var(--border);
    border-radius: 3px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: var(--primary);
    transition: width 0.3s;
}

.project-actions {
    display: flex;
    gap: 8px;
}
</style>

<?php include 'includes/footer.php'; ?>
