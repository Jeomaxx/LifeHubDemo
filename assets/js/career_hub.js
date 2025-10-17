let draggedTask = null;

async function addProject(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    
    try {
        const response = await fetch('/api/career_hub.php?action=add_project', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.success) {
            showToast('success', 'Success', 'Project created successfully');
            closeModal('addProjectModal');
            event.target.reset();
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast('error', 'Error', result.message || 'Failed to create project');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('error', 'Error', 'Failed to create project');
    }
}

async function addTask(event, status = 'todo') {
    event.preventDefault();
    const formData = new FormData(event.target);
    formData.append('status', status);
    
    try {
        const response = await fetch('/api/career_hub.php?action=add_task', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.success) {
            showToast('success', 'Success', 'Task added successfully');
            closeModal('addTaskModal');
            event.target.reset();
            loadKanbanTasks();
        } else {
            showToast('error', 'Error', result.message || 'Failed to add task');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('error', 'Error', 'Failed to add task');
    }
}

async function loadKanbanTasks() {
    try {
        const response = await fetch('/api/career_hub.php?action=get_tasks');
        const result = await response.json();
        
        if (result.success) {
            const statuses = ['todo', 'in_progress', 'review', 'done'];
            statuses.forEach(status => {
                const tasksContainer = document.getElementById(`${status}-tasks`);
                const tasks = result.tasks.filter(t => t.status === status);
                
                tasksContainer.innerHTML = tasks.map(task => createTaskCard(task)).join('');
                
                const countEl = document.getElementById(`${status}-count`);
                if (countEl) countEl.textContent = tasks.length;
            });
            
            initDragAndDrop();
        }
    } catch (error) {
        console.error('Error loading tasks:', error);
    }
}

function createTaskCard(task) {
    return `
        <div class="kanban-task" draggable="true" data-task-id="${task.id}">
            <h5>${task.task_title}</h5>
            ${task.task_description ? `<p class="text-sm text-secondary">${task.task_description}</p>` : ''}
            ${task.project_id ? `<span class="badge badge-sm badge-info">Project Task</span>` : ''}
            <div class="task-actions mt-2">
                <button class="btn btn-xs btn-danger" onclick="deleteTask(${task.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `;
}

function initDragAndDrop() {
    const tasks = document.querySelectorAll('.kanban-task');
    const columns = document.querySelectorAll('.kanban-tasks');
    
    tasks.forEach(task => {
        task.addEventListener('dragstart', handleDragStart);
        task.addEventListener('dragend', handleDragEnd);
    });
    
    columns.forEach(column => {
        column.addEventListener('dragover', handleDragOver);
        column.addEventListener('drop', handleDrop);
        column.addEventListener('dragleave', handleDragLeave);
    });
}

function handleDragStart(e) {
    draggedTask = this;
    this.classList.add('dragging');
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/html', this.innerHTML);
}

function handleDragEnd(e) {
    this.classList.remove('dragging');
}

function handleDragOver(e) {
    if (e.preventDefault) e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
    this.classList.add('drag-over');
    return false;
}

function handleDragLeave(e) {
    this.classList.remove('drag-over');
}

async function handleDrop(e) {
    if (e.stopPropagation) e.stopPropagation();
    this.classList.remove('drag-over');
    
    if (draggedTask !== this) {
        const taskId = draggedTask.dataset.taskId;
        const newStatus = this.parentElement.dataset.status;
        
        await updateTaskStatus(taskId, newStatus);
        this.appendChild(draggedTask);
    }
    
    return false;
}

async function updateTaskStatus(taskId, status) {
    try {
        const formData = new FormData();
        formData.append('task_id', taskId);
        formData.append('status', status);
        
        const response = await fetch('/api/career_hub.php?action=update_task_status', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.success) {
            loadKanbanTasks();
        }
    } catch (error) {
        console.error('Error updating task:', error);
    }
}

async function deleteTask(taskId) {
    if (!confirm('Are you sure you want to delete this task?')) return;
    
    try {
        const formData = new FormData();
        formData.append('task_id', taskId);
        
        const response = await fetch('/api/career_hub.php?action=delete_task', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.success) {
            showToast('success', 'Deleted', 'Task deleted successfully');
            loadKanbanTasks();
        } else {
            showToast('error', 'Error', result.message || 'Failed to delete task');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('error', 'Error', 'Failed to delete task');
    }
}

async function logTime(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    
    try {
        const response = await fetch('/api/career_hub.php?action=log_time', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.success) {
            showToast('success', 'Success', 'Time logged successfully');
            closeModal('logTimeModal');
            event.target.reset();
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast('error', 'Error', result.message || 'Failed to log time');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('error', 'Error', 'Failed to log time');
    }
}

function showAddProjectModal() {
    openModal('addProjectModal');
}

function showAddTaskModal(status = 'todo') {
    const modal = document.getElementById('addTaskModal');
    if (!modal) {
        const modalHtml = `
            <div id="addTaskModal" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2><i class="fas fa-plus"></i> New Task</h2>
                        <button class="modal-close" onclick="closeModal('addTaskModal')">&times;</button>
                    </div>
                    <div class="modal-body">
                        <form id="addTaskForm" onsubmit="addTask(event, '${status}')">
                            <div class="form-group">
                                <label>Task Title</label>
                                <input type="text" name="task_title" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="task_description" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="form-actions">
                                <button type="button" class="btn btn-secondary" onclick="closeModal('addTaskModal')">Cancel</button>
                                <button type="submit" class="btn btn-primary">Add Task</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHtml);
    }
    openModal('addTaskModal');
}

function showLogTimeModal() {
    openModal('logTimeModal');
}

function showSalaryGoalModal() {
    const modal = document.getElementById('salaryGoalModal');
    if (!modal) {
        const modalHtml = `
            <div id="salaryGoalModal" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2><i class="fas fa-dollar-sign"></i> Set Salary Goal</h2>
                        <button class="modal-close" onclick="closeModal('salaryGoalModal')">&times;</button>
                    </div>
                    <div class="modal-body">
                        <form id="salaryGoalForm" onsubmit="setSalaryGoal(event)">
                            <div class="form-group">
                                <label>Current Salary</label>
                                <input type="number" name="current_salary" class="form-control" step="1000" required>
                            </div>
                            <div class="form-group">
                                <label>Target Salary</label>
                                <input type="number" name="target_salary" class="form-control" step="1000" required>
                            </div>
                            <div class="form-group">
                                <label>Target Date</label>
                                <input type="date" name="target_date" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>Notes</label>
                                <textarea name="notes" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="form-actions">
                                <button type="button" class="btn btn-secondary" onclick="closeModal('salaryGoalModal')">Cancel</button>
                                <button type="submit" class="btn btn-primary">Set Goal</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHtml);
    }
    openModal('salaryGoalModal');
}

async function setSalaryGoal(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    
    try {
        const response = await fetch('/api/career_hub.php?action=set_salary_goal', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.success) {
            showToast('success', 'Success', 'Salary goal set successfully');
            closeModal('salaryGoalModal');
            event.target.reset();
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast('error', 'Error', result.message || 'Failed to set salary goal');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('error', 'Error', 'Failed to set salary goal');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    loadKanbanTasks();
});
