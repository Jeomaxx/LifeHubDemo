document.addEventListener('DOMContentLoaded', function() {
    loadRules();
    loadAutomationStats();
});

let currentRules = [];

async function loadRules() {
    try {
        const response = await fetch('/api/life_orchestrator.php?action=rules');
        const result = await response.json();
        if (result.success) {
            currentRules = result.data || [];
            renderRules(currentRules);
        }
    } catch (error) {
        console.error('Error loading rules:', error);
    }
}

function renderRules(rules) {
    const container = document.getElementById('rulesContainer');
    if (!container) return;
    
    if (rules.length === 0) {
        container.innerHTML = '<p class="text-gray-500 text-center py-8">No automation rules yet. Create your first rule!</p>';
        return;
    }
    
    container.innerHTML = rules.map(rule => `
        <div class="rule-card bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
            <div class="flex justify-between items-start mb-3">
                <div class="flex-1">
                    <h3 class="text-lg font-semibold">${escapeHtml(rule.rule_name)}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">${escapeHtml(rule.description || '')}</p>
                </div>
                <div class="flex items-center gap-2">
                    <label class="switch">
                        <input type="checkbox" ${rule.is_active ? 'checked' : ''} onchange="toggleRule(${rule.id}, this.checked)">
                        <span class="slider"></span>
                    </label>
                </div>
            </div>
            <div class="mt-3 text-sm">
                <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                    <i class="fas fa-bolt text-blue-500"></i>
                    <span><strong>Trigger:</strong> ${escapeHtml(rule.trigger_type || 'Unknown')}</span>
                </div>
                <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400 mt-1">
                    <i class="fas fa-check-circle text-green-500"></i>
                    <span><strong>Action:</strong> ${escapeHtml(rule.action_type || 'Unknown')}</span>
                </div>
                ${rule.last_triggered_at ? `
                <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400 mt-1">
                    <i class="fas fa-clock"></i>
                    <span>Last triggered: ${formatDate(rule.last_triggered_at)}</span>
                </div>
                ` : ''}
            </div>
            <div class="flex gap-2 mt-4">
                <button onclick="editRule(${rule.id})" class="btn btn-sm btn-secondary">Edit</button>
                <button onclick="testRule(${rule.id})" class="btn btn-sm btn-info">Test</button>
                <button onclick="deleteRule(${rule.id})" class="btn btn-sm btn-danger">Delete</button>
            </div>
        </div>
    `).join('');
}

async function loadAutomationStats() {
    try {
        const response = await fetch('/api/life_orchestrator.php?action=stats');
        const result = await response.json();
        if (result.success) {
            updateStats(result.data);
        }
    } catch (error) {
        console.error('Error loading stats:', error);
    }
}

function updateStats(stats) {
    if (stats.active_rules !== undefined) {
        document.getElementById('activeRules').textContent = stats.active_rules;
    }
    if (stats.today_executions !== undefined) {
        document.getElementById('todayExecutions').textContent = stats.today_executions;
    }
    if (stats.success_rate !== undefined) {
        document.getElementById('successRate').textContent = stats.success_rate + '%';
    }
    if (stats.time_saved !== undefined) {
        document.getElementById('timeSaved').textContent = stats.time_saved + 'h';
    }
}

function openRuleModal() {
    const modal = document.getElementById('ruleModal');
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
}

function closeRuleModal() {
    const modal = document.getElementById('ruleModal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
    
    const form = document.getElementById('ruleForm');
    if (form) {
        form.reset();
        delete form.dataset.editId;
    }
}

async function saveRule() {
    const ruleName = document.getElementById('ruleName').value;
    const triggerType = document.getElementById('triggerType').value;
    const actionType = document.getElementById('actionType').value;
    const conditionsText = document.getElementById('conditions')?.value || '';
    
    if (!ruleName || !triggerType || !actionType) {
        showToast('error', 'Error', 'Please fill in all required fields');
        return;
    }
    
    let triggerConditions = {};
    let actionParameters = {};
    
    if (conditionsText) {
        try {
            const parsed = JSON.parse(conditionsText);
            triggerConditions = parsed;
            actionParameters = parsed;
        } catch (e) {
            triggerConditions = { raw: conditionsText };
            actionParameters = { raw: conditionsText };
        }
    }
    
    const form = document.getElementById('ruleForm');
    const editId = form?.dataset?.editId;
    
    const action = editId ? 'update-rule' : 'create-rule';
    const requestBody = {
        rule_name: ruleName,
        description: '',
        trigger_type: triggerType,
        trigger_conditions: triggerConditions,
        action_type: actionType,
        action_parameters: actionParameters,
        is_active: true
    };
    
    if (editId) {
        requestBody.id = editId;
    }
    
    try {
        const response = await fetch(`/api/life_orchestrator.php?action=${action}`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(requestBody)
        });
        
        const result = await response.json();
        if (result.success) {
            showToast('success', 'Success', editId ? 'Rule updated successfully' : 'Rule created successfully');
            closeRuleModal();
            loadRules();
            loadAutomationStats();
        } else {
            showToast('error', 'Error', result.message || 'Failed to save rule');
        }
    } catch (error) {
        console.error('Error saving rule:', error);
        showToast('error', 'Error', 'Failed to save rule');
    }
}

async function toggleRule(id, isActive) {
    try {
        const response = await fetch('/api/life_orchestrator.php?action=toggle-rule', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                id: id
            })
        });
        
        const result = await response.json();
        if (result.success) {
            showToast('success', 'Success', isActive ? 'Rule activated' : 'Rule deactivated');
            loadAutomationStats();
        } else {
            showToast('error', 'Error', result.message || 'Failed to toggle rule');
            loadRules();
        }
    } catch (error) {
        console.error('Error toggling rule:', error);
        loadRules();
    }
}

function editRule(id) {
    const rule = currentRules.find(r => r.id == id);
    if (!rule) {
        showToast('error', 'Error', 'Rule not found');
        return;
    }
    
    document.getElementById('ruleName').value = rule.rule_name || '';
    document.getElementById('triggerType').value = rule.trigger_type || '';
    document.getElementById('actionType').value = rule.action_type || '';
    
    const conditionsField = document.getElementById('conditions');
    if (conditionsField) {
        try {
            const conditions = typeof rule.trigger_conditions === 'string' 
                ? rule.trigger_conditions 
                : JSON.stringify(rule.trigger_conditions || {});
            conditionsField.value = conditions;
        } catch (e) {
            conditionsField.value = '';
        }
    }
    
    const form = document.getElementById('ruleForm');
    if (form) {
        form.dataset.editId = rule.id;
    }
    
    openRuleModal();
}

async function testRule(id) {
    try {
        showToast('info', 'Testing', 'Executing automation rule...');
        const response = await fetch('/api/life_orchestrator.php?action=execute-rule', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                id: id
            })
        });
        
        const result = await response.json();
        if (result.success) {
            showToast('success', 'Test Complete', result.message || 'Rule executed successfully');
            loadAutomationStats();
        } else {
            showToast('error', 'Test Failed', result.message || 'Rule execution failed');
        }
    } catch (error) {
        console.error('Error testing rule:', error);
        showToast('error', 'Error', 'Failed to test rule');
    }
}

async function deleteRule(id, skipConfirm = false) {
    if (!skipConfirm && !confirm('Delete this automation rule?')) return false;
    
    try {
        const response = await fetch(`/api/life_orchestrator.php?id=${id}`, {
            method: 'DELETE'
        });
        
        const result = await response.json();
        if (result.success) {
            if (!skipConfirm) {
                showToast('success', 'Success', 'Rule deleted');
                loadRules();
                loadAutomationStats();
            }
            return true;
        } else {
            if (!skipConfirm) {
                showToast('error', 'Error', result.message || 'Failed to delete rule');
            }
            return false;
        }
    } catch (error) {
        console.error('Error deleting rule:', error);
        if (!skipConfirm) {
            showToast('error', 'Error', 'Failed to delete rule');
        }
        return false;
    }
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
