// Unified Analytics - Data Visualization

// Generate comprehensive report
async function generateReport() {
    if (!confirm('Generate comprehensive life analytics report?')) return;
    
    try {
        const response = await fetch('/api/analytics_report.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'generate',
                format: 'pdf',
                period: '30days'
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            window.open(result.report_url, '_blank');
            showNotification('Report generated successfully', 'success');
        } else {
            showNotification('Failed to generate report', 'error');
        }
    } catch (error) {
        console.error('Report generation error:', error);
        showNotification('Error generating report', 'error');
    }
}

// Load analytics data
async function loadAnalyticsData() {
    try {
        const response = await fetch('/api/unified_analytics.php?action=get_data');
        const data = await response.json();
        
        if (data.success) {
            updateDashboard(data);
        }
    } catch (error) {
        console.error('Analytics load error:', error);
    }
}

// Update dashboard with data
function updateDashboard(data) {
    // Update task completion
    const taskCompletion = data.productivity.task_completion || 0;
    document.getElementById('taskCompletion').textContent = Math.round(taskCompletion) + '%';
    document.getElementById('taskProgress').style.width = taskCompletion + '%';
    
    // Update goal progress
    const goalProgress = data.productivity.goal_progress || 0;
    document.getElementById('goalProgress').textContent = Math.round(goalProgress) + '%';
    document.getElementById('goalProgressBar').style.width = goalProgress + '%';
    
    // Update savings rate
    const savingsRate = data.finance.savings_rate || 0;
    document.getElementById('savingsRate').textContent = Math.round(savingsRate) + '%';
    document.getElementById('savingsProgress').style.width = savingsRate + '%';
    
    // Update budget adherence
    const budgetAdherence = data.finance.budget_adherence || 0;
    document.getElementById('budgetAdherence').textContent = Math.round(budgetAdherence) + '%';
    document.getElementById('budgetProgress').style.width = budgetAdherence + '%';
    
    // Update exercise rate
    const exerciseRate = data.health.exercise_rate || 0;
    document.getElementById('exerciseRate').textContent = Math.round(exerciseRate) + '%';
    document.getElementById('exerciseProgress').style.width = exerciseRate + '%';
    
    // Update wellness score
    const wellnessScore = data.health.wellness_score || 0;
    document.getElementById('wellnessScore').textContent = Math.round(wellnessScore) + '/100';
    document.getElementById('wellnessProgress').style.width = wellnessScore + '%';
}

// Initialize on page load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', loadAnalyticsData);
} else {
    loadAnalyticsData();
}

function showNotification(message, type) {
    if (typeof window.showNotification === 'function') {
        window.showNotification(message, type);
    } else {
        alert(message);
    }
}
