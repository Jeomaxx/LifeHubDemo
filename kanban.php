<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

// Get tasks grouped by status
$statuses = ['pending', 'in_progress', 'completed'];
$tasksByStatus = [];

foreach ($statuses as $status) {
    $tasksByStatus[$status] = $db->fetchAll(
        "SELECT * FROM tasks WHERE user_id = ? AND status = ? ORDER BY created_at DESC",
        [$userId, $status]
    );
}

$pageTitle = 'Kanban Board';
include 'includes/header.php';
?>

<div class="mb-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-columns text-primary"></i>
                Kanban Board
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Visualize and manage your tasks</p>
        </div>
        <a href="/tasks.php" class="text-primary hover:text-blue-600">
            <i class="fas fa-list"></i> List View
        </a>
    </div>
</div>

<!-- Kanban Board -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <?php foreach ($statuses as $status): 
        $statusConfig = [
            'pending' => ['label' => 'To Do', 'color' => 'gray', 'icon' => 'fa-circle'],
            'in_progress' => ['label' => 'In Progress', 'color' => 'blue', 'icon' => 'fa-spinner'],
            'completed' => ['label' => 'Done', 'color' => 'green', 'icon' => 'fa-check-circle']
        ];
        $config = $statusConfig[$status];
    ?>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 h-fit">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-<?php echo $config['color']; ?>-50 dark:bg-<?php echo $config['color']; ?>-900/20">
            <h3 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <i class="fas <?php echo $config['icon']; ?> text-<?php echo $config['color']; ?>-600"></i>
                <?php echo $config['label']; ?>
                <span class="ml-auto bg-<?php echo $config['color']; ?>-100 dark:bg-<?php echo $config['color']; ?>-900 text-<?php echo $config['color']; ?>-800 dark:text-<?php echo $config['color']; ?>-200 text-xs px-2 py-1 rounded-full">
                    <?php echo count($tasksByStatus[$status]); ?>
                </span>
            </h3>
        </div>
        
        <div class="p-4 space-y-3 min-h-[400px]" data-status="<?php echo $status; ?>">
            <?php foreach ($tasksByStatus[$status] as $task): ?>
            <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-lg border border-gray-200 dark:border-gray-700 cursor-move hover:shadow-md transition-shadow" data-task-id="<?php echo $task['id']; ?>" draggable="true">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2"><?php echo sanitize($task['title']); ?></h4>
                <?php if ($task['description']): ?>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-3"><?php echo sanitize($task['description']); ?></p>
                <?php endif; ?>
                
                <div class="flex items-center justify-between text-xs">
                    <?php if ($task['priority']): ?>
                    <span class="px-2 py-1 rounded-full <?php 
                        echo $task['priority'] == 'high' ? 'bg-red-100 text-red-800' : 
                            ($task['priority'] == 'medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800'); 
                    ?>">
                        <?php echo ucfirst($task['priority']); ?>
                    </span>
                    <?php endif; ?>
                    
                    <?php if ($task['due_date']): ?>
                    <span class="text-gray-500 dark:text-gray-400">
                        <i class="fas fa-calendar"></i> <?php echo date('M d', strtotime($task['due_date'])); ?>
                    </span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<script>
// Drag and drop functionality
let draggedElement = null;

document.querySelectorAll('[draggable="true"]').forEach(item => {
    item.addEventListener('dragstart', function(e) {
        draggedElement = this;
        this.classList.add('opacity-50');
    });
    
    item.addEventListener('dragend', function(e) {
        this.classList.remove('opacity-50');
    });
});

document.querySelectorAll('[data-status]').forEach(column => {
    column.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.classList.add('bg-blue-50', 'dark:bg-blue-900/20');
    });
    
    column.addEventListener('dragleave', function(e) {
        this.classList.remove('bg-blue-50', 'dark:bg-blue-900/20');
    });
    
    column.addEventListener('drop', async function(e) {
        e.preventDefault();
        this.classList.remove('bg-blue-50', 'dark:bg-blue-900/20');
        
        const taskId = draggedElement.dataset.taskId;
        const newStatus = this.dataset.status;
        
        // Update task status via API
        const response = await fetch('/api/crud.php?action=update&module=tasks', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ id: taskId, status: newStatus })
        });
        
        if (response.ok) {
            this.appendChild(draggedElement);
            // Update counter
            document.querySelectorAll('[data-status]').forEach(col => {
                const count = col.querySelectorAll('[data-task-id]').length;
                col.previousElementSibling.querySelector('span').textContent = count;
            });
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>
