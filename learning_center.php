<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$courses = $db->fetchAll("SELECT * FROM learning_courses WHERE user_id = ? ORDER BY created_at DESC", [$userId]) ?: [];
$activeCourses = array_filter($courses, fn($c) => $c['status'] == 'in_progress');
$completedCourses = array_filter($courses, fn($c) => $c['status'] == 'completed');

$pageTitle = 'Learning & Skill Growth Center';
$extraScripts = ['/assets/js/learning_center.js'];
include 'includes/header.php';
?>

<div class="page-header">
    <h1><i class="fas fa-graduation-cap"></i> Learning & Skill Growth Center</h1>
    <p class="page-subtitle">Track courses, develop skills, and achieve learning goals</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon bg-blue">
            <i class="fas fa-book-open"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo count($activeCourses); ?></h3>
            <p>Courses In Progress</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-green">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo count($completedCourses); ?></h3>
            <p>Courses Completed</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-purple">
            <i class="fas fa-chart-line"></i>
        </div>
        <div class="stat-info">
            <h3>
                <?php
                if (!empty($activeCourses)) {
                    $avgProgress = array_sum(array_column($activeCourses, 'progress_percentage')) / count($activeCourses);
                    echo number_format($avgProgress, 0) . '%';
                } else {
                    echo '0%';
                }
                ?>
            </h3>
            <p>Average Progress</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-orange">
            <i class="fas fa-brain"></i>
        </div>
        <div class="stat-info">
            <h3>
                <?php
                $skillCount = $db->fetchColumn("SELECT COUNT(DISTINCT skill_category) FROM learning_courses WHERE user_id = ?", [$userId]);
                echo $skillCount ?: 0;
                ?>
            </h3>
            <p>Skills Learning</p>
        </div>
    </div>
</div>

<div class="action-bar">
    <button class="btn btn-primary" onclick="showAddCourseModal()">
        <i class="fas fa-plus"></i> Add Course
    </button>
    <button class="btn btn-secondary" onclick="getAIRecommendations()">
        <i class="fas fa-magic"></i> AI Skill Recommendations
    </button>
</div>

<!-- Courses Grid -->
<div class="courses-section">
    <h3 class="mb-4"><i class="fas fa-book-reader"></i> In Progress Courses</h3>
    <?php if (empty($activeCourses)): ?>
    <div class="text-center py-8">
        <i class="fas fa-book fa-3x text-gray-400 mb-4"></i>
        <p class="text-gray-500">No courses in progress. Start learning today!</p>
    </div>
    <?php else: ?>
    <div class="courses-grid">
        <?php foreach ($activeCourses as $course): ?>
        <div class="course-card">
            <div class="course-header">
                <h4><?php echo sanitize($course['course_name']); ?></h4>
                <span class="badge badge-blue"><?php echo sanitize($course['skill_category']); ?></span>
            </div>
            <p class="course-platform">
                <i class="fas fa-laptop"></i> <?php echo sanitize($course['course_platform'] ?: 'Self-study'); ?>
            </p>
            <div class="progress-section">
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?php echo $course['progress_percentage']; ?>%"></div>
                </div>
                <span class="progress-text"><?php echo $course['progress_percentage']; ?>% Complete</span>
            </div>
            <?php if ($course['target_completion_date']): ?>
            <p class="course-deadline">
                <i class="fas fa-calendar"></i> Target: <?php echo date('M d, Y', strtotime($course['target_completion_date'])); ?>
            </p>
            <?php endif; ?>
            <div class="course-actions">
                <button class="btn btn-sm btn-secondary" onclick="updateProgress(<?php echo $course['id']; ?>)">
                    <i class="fas fa-percentage"></i> Update Progress
                </button>
                <button class="btn btn-sm btn-primary" onclick="viewNotes(<?php echo $course['id']; ?>)">
                    <i class="fas fa-sticky-note"></i> Notes
                </button>
                <?php if ($course['course_url']): ?>
                <a href="<?php echo sanitize($course['course_url']); ?>" target="_blank" class="btn btn-sm btn-success">
                    <i class="fas fa-external-link-alt"></i> Open
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Completed Courses -->
<?php if (!empty($completedCourses)): ?>
<div class="courses-section mt-4">
    <h3 class="mb-4"><i class="fas fa-trophy"></i> Completed Courses</h3>
    <div class="completed-courses-list">
        <?php foreach ($completedCourses as $course): ?>
        <div class="completed-course-item">
            <div class="course-info">
                <h5><?php echo sanitize($course['course_name']); ?></h5>
                <span class="badge badge-success">Completed</span>
                <span class="skill-tag"><?php echo sanitize($course['skill_category']); ?></span>
            </div>
            <button class="btn btn-sm btn-secondary" onclick="viewNotes(<?php echo $course['id']; ?>)">
                <i class="fas fa-sticky-note"></i> View Notes
            </button>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Add Course Modal -->
<div id="addCourseModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class="fas fa-plus"></i> Add Course</h2>
            <button class="modal-close" onclick="closeModal('addCourseModal')">&times;</button>
        </div>
        <div class="modal-body">
            <form id="addCourseForm" onsubmit="addCourse(event)">
                <div class="form-group">
                    <label>Course Name</label>
                    <input type="text" name="course_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Platform</label>
                    <select name="course_platform" class="form-control">
                        <option value="">Self-study</option>
                        <option value="Udemy">Udemy</option>
                        <option value="Coursera">Coursera</option>
                        <option value="edX">edX</option>
                        <option value="YouTube">YouTube</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Course URL</label>
                    <input type="url" name="course_url" class="form-control">
                </div>
                <div class="form-group">
                    <label>Skill Category</label>
                    <input type="text" name="skill_category" class="form-control" placeholder="e.g., Web Development, Data Science" required>
                </div>
                <div class="form-group">
                    <label>Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="form-group">
                    <label>Target Completion Date</label>
                    <input type="date" name="target_completion_date" class="form-control">
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addCourseModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Course</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.courses-section {
    margin: 24px 0;
}

.courses-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 20px;
}

.course-card {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 20px;
    transition: all 0.3s;
}

.course-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.1);
}

.course-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 12px;
    gap: 12px;
}

.course-header h4 {
    flex: 1;
    margin: 0;
}

.course-platform {
    color: var(--text-secondary);
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.progress-section {
    margin: 16px 0;
}

.progress-bar {
    height: 8px;
    background: var(--border);
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 8px;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--primary), var(--primary-dark));
    transition: width 0.3s;
}

.progress-text {
    font-size: 0.9em;
    color: var(--text-secondary);
}

.course-deadline {
    color: var(--text-secondary);
    margin: 12px 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.course-actions {
    display: flex;
    gap: 8px;
    margin-top: 16px;
    flex-wrap: wrap;
}

.completed-courses-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.completed-course-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px;
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 8px;
}

.course-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.skill-tag {
    padding: 4px 12px;
    background: rgba(59, 130, 246, 0.1);
    color: var(--primary);
    border-radius: 12px;
    font-size: 0.85em;
}
</style>

<?php include 'includes/footer.php'; ?>
