document.addEventListener('DOMContentLoaded', function() {
    loadRules();
    loadAutomationStats();
});

async function loadRules() {
    try {
        const response = await fetch('/api/life_orchestrator.php?action=rules');
        const result = await response.json();
        if (result.success) {
            renderRules(result.data || []);
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
        document.getElementById('ruleForm')?.reset();
    }
}

async function saveRule() {
    const ruleName = document.getElementById('ruleName').value;
    const triggerType = document.getElementById('triggerType').value;
    const actionType = document.getElementById('actionType').value;
    const conditions = document.getElementById('conditions').value;
    
    if (!ruleName || !triggerType || !actionType) {
        showToast('error', 'Error', 'Please fill in all required fields');
        return;
    }
    
    try {
        const response = await fetch('/api/life_orchestrator.php?action=create-rule', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                rule_name: ruleName,
                description: '',
                trigger_type: triggerType,
                trigger_conditions: {},
                action_type: actionType,
                action_parameters: {conditions: conditions},
                is_active: true
            })
        });
        
        const result = await response.json();
        if (result.success) {
            showToast('success', 'Success', 'Rule created successfully');
            closeRuleModal();
            loadRules();
            loadAutomationStats();
        } else {
            showToast('error', 'Error', result.message || 'Failed to create rule');
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

async function editRule(id) {
    try {
        const response = await fetch(`/api/life_orchestrator.php?action=get_rule&id=${id}`);
        const result = await response.json();
        if (result.success && result.rule) {
            populateEditForm(result.rule);
        }
    } catch (error) {
        console.error('Error loading rule:', error);
    }
}

function populateEditForm(rule) {
    document.getElementById('ruleName').value = rule.rule_name;
    document.getElementById('triggerType').value = rule.trigger_type;
    document.getElementById('actionType').value = rule.action_type;
    document.getElementById('conditions').value = rule.conditions || '';
    
    const form = document.getElementById('ruleForm');
    form.dataset.editId = rule.id;
    openRuleModal();
}

async function testRule(id) {
    try {
        showToast('info', 'Testing', 'Testing automation rule...');
        const response = await fetch('/api/life_orchestrator.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action: 'test_rule',
                id: id
            })
        });
        
        const result = await response.json();
        if (result.success) {
            showToast('success', 'Test Complete', result.message || 'Rule tested successfully');
        } else {
            showToast('error', 'Test Failed', result.message || 'Rule test failed');
        }
    } catch (error) {
        console.error('Error testing rule:', error);
        showToast('error', 'Error', 'Failed to test rule');
    }
}

async function deleteRule(id) {
    if (!confirm('Delete this automation rule?')) return;
    
    try {
        const response = await fetch('/api/life_orchestrator.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action: 'delete_rule',
                id: id
            })
        });
        
        const result = await response.json();
        if (result.success) {
            showToast('success', 'Success', 'Rule deleted');
            loadRules();
            loadAutomationStats();
        }
    } catch (error) {
        console.error('Error deleting rule:', error);
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
