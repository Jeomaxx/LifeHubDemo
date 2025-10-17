async function generateDailyBriefing() {
    showToast('info', 'Processing', 'AI is analyzing your data to generate daily briefing...');
    
    try {
        const response = await fetch('/api/life_advisor.php?action=generate', {
            method: 'POST'
        });
        const result = await response.json();
        
        if (result.success) {
            showToast('success', 'Success', 'Daily briefing generated successfully!');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast('error', 'Error', result.message || 'Failed to generate briefing');
        }
    } catch (error) {
        console.error('Error generating briefing:', error);
        showToast('error', 'Error', 'Failed to generate briefing');
    }
}

async function toggleAction(actionId, isCompleted) {
    try {
        const response = await fetch('/api/life_advisor.php?action=toggle_action', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action_id=${actionId}&is_completed=${isCompleted ? 1 : 0}`
        });
        const result = await response.json();
        
        if (result.success) {
            const actionItem = document.querySelector(`[data-action-id="${actionId}"]`);
            if (actionItem) {
                if (isCompleted) {
                    actionItem.classList.add('completed');
                    showToast('success', 'Done', 'Action marked as completed');
                } else {
                    actionItem.classList.remove('completed');
                }
            }
            
            updateActionCounts();
        } else {
            showToast('error', 'Error', result.message || 'Failed to update action');
        }
    } catch (error) {
        console.error('Error toggling action:', error);
        showToast('error', 'Error', 'Failed to update action');
    }
}

function updateActionCounts() {
    const totalActions = document.querySelectorAll('.action-item').length;
    const completedActions = document.querySelectorAll('.action-item.completed').length;
    const pendingActions = totalActions - completedActions;
    
    const actionCountEl = document.getElementById('action-count');
    const completedCountEl = document.getElementById('completed-count');
    
    if (actionCountEl) actionCountEl.textContent = pendingActions;
    if (completedCountEl) completedCountEl.textContent = completedActions;
}

async function viewBriefing(briefingId) {
    try {
        const response = await fetch(`/api/life_advisor.php?action=view&briefing_id=${briefingId}`);
        const result = await response.json();
        
        if (result.success) {
            const briefing = result.briefing;
            const modal = createBriefingModal(briefing);
            document.body.appendChild(modal);
            modal.style.display = 'flex';
        } else {
            showToast('error', 'Error', result.message || 'Failed to load briefing');
        }
    } catch (error) {
        console.error('Error viewing briefing:', error);
        showToast('error', 'Error', 'Failed to load briefing');
    }
}

function createBriefingModal(briefing) {
    const modal = document.createElement('div');
    modal.className = 'modal';
    modal.style.display = 'none';
    
    const actionsHtml = briefing.actions.map(action => `
        <div class="action-item ${action.is_completed ? 'completed' : ''}">
            ${action.is_completed ? '✓' : '○'} ${action.action_text} (${action.action_type})
        </div>
    `).join('');
    
    modal.innerHTML = `
        <div class="modal-content" style="max-width: 800px;">
            <div class="modal-header">
                <h2><i class="fas fa-calendar"></i> Briefing - ${briefing.briefing_date}</h2>
                <button class="modal-close" onclick="this.closest('.modal').remove()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="briefing-section">
                    <h4><i class="fas fa-align-left"></i> Daily Summary</h4>
                    <p>${briefing.daily_summary}</p>
                </div>
                
                ${briefing.ai_recommendations ? `
                <div class="briefing-section">
                    <h4><i class="fas fa-lightbulb"></i> AI Recommendations</h4>
                    <p>${briefing.ai_recommendations}</p>
                </div>
                ` : ''}
                
                <div class="briefing-section">
                    <h4><i class="fas fa-list-check"></i> Action Items</h4>
                    ${actionsHtml || '<p class="text-muted">No action items</p>'}
                </div>
            </div>
        </div>
    `;
    
    modal.onclick = (e) => {
        if (e.target === modal) modal.remove();
    };
    
    return modal;
}

async function exportBriefing() {
    const today = new Date().toISOString().split('T')[0];
    const briefingId = document.querySelector('[data-briefing-id]')?.dataset.briefingId;
    
    if (!briefingId) {
        showToast('error', 'Error', 'No briefing to export');
        return;
    }
    
    window.location.href = `/api/life_advisor.php?action=export&briefing_id=${briefingId}`;
    showToast('success', 'Exported', 'Briefing downloaded successfully');
}

function playAudioBriefing() {
    const summary = document.querySelector('.briefing-content')?.textContent;
    
    if (!summary) {
        showToast('error', 'Error', 'No briefing to play');
        return;
    }
    
    if ('speechSynthesis' in window) {
        speechSynthesis.cancel();
        const utterance = new SpeechSynthesisUtterance(summary);
        utterance.rate = 0.9;
        utterance.pitch = 1;
        utterance.volume = 1;
        speechSynthesis.speak(utterance);
        showToast('info', 'Playing', 'Audio briefing started');
    } else {
        showToast('error', 'Not Supported', 'Text-to-speech not supported in your browser');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    updateActionCounts();
});
