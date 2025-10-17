function showAddGoalModal() {
    document.getElementById('addGoalModal').style.display = 'flex';
}

async function addGoal(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const data = Object.fromEntries(formData);
    
    try {
        const response = await fetch('/api/smart_goals.php?action=add', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('success', 'Success', 'SMART goal created successfully!');
            closeModal('addGoalModal');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast('error', 'Error', result.message || 'Failed to create goal');
        }
    } catch (error) {
        console.error('Error creating goal:', error);
        showToast('error', 'Error', 'Failed to create goal');
    }
}

async function updateProgress(goalId, currentProgress) {
    const newProgress = prompt(`Enter new progress (0-100). Current: ${currentProgress}%`, currentProgress);
    
    if (newProgress === null || newProgress === '') return;
    
    const progress = parseInt(newProgress);
    if (isNaN(progress) || progress < 0 || progress > 100) {
        showToast('error', 'Invalid Input', 'Progress must be between 0 and 100');
        return;
    }
    
    try {
        showToast('info', 'Updating', 'AI is analyzing your progress...');
        
        const response = await fetch('/api/smart_goals.php?action=update_progress', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ goal_id: goalId, progress: progress })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('success', 'Updated', 'Goal progress updated with AI feedback!');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast('error', 'Error', result.message || 'Failed to update progress');
        }
    } catch (error) {
        console.error('Error updating progress:', error);
        showToast('error', 'Error', 'Failed to update progress');
    }
}

async function deleteGoal(id) {
    if (!confirm('Are you sure you want to delete this goal?')) return;
    
    try {
        const response = await fetch(`/api/smart_goals.php?action=delete&id=${id}`, {
            method: 'DELETE'
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('success', 'Deleted', 'Goal deleted successfully');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast('error', 'Error', result.message || 'Failed to delete goal');
        }
    } catch (error) {
        console.error('Error deleting goal:', error);
        showToast('error', 'Error', 'Failed to delete goal');
    }
}

async function loadStats() {
    try {
        const response = await fetch('/api/smart_goals.php?action=get_goals&status=active');
        const result = await response.json();
        
        if (result.success && result.goals.length > 0) {
            const avgProgress = result.goals.reduce((sum, g) => sum + g.current_progress, 0) / result.goals.length;
            document.getElementById('avg-progress').textContent = Math.round(avgProgress) + '%';
        }
    } catch (error) {
        console.error('Error loading stats:', error);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    loadStats();
});
