<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    redirect('/login.php');
}

$db = getDB();
$userId = $auth->getUserId();

$pageTitle = 'Life Automation';
$activePage = 'automation';
include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="main-content">
    <div class="page-header">
        <h1><i class="fas fa-robot"></i> Life Automation & Smart Actions</h1>
        <p>Create custom automation rules to streamline your life</p>
    </div>

    <div class="automation-controls">
        <button class="btn btn-primary" onclick="showCreateRuleModal()">
            <i class="fas fa-plus"></i> Create Automation Rule
        </button>
        <button class="btn btn-secondary" onclick="showAutomationTemplates()">
            <i class="fas fa-lightbulb"></i> Browse Templates
        </button>
    </div>

    <div class="automation-grid">
        <div class="automation-card active-rules">
            <h3><i class="fas fa-toggle-on"></i> Active Automations</h3>
            <div id="activeRulesContainer"></div>
        </div>

        <div class="automation-card recent-executions">
            <h3><i class="fas fa-history"></i> Recent Executions</h3>
            <div id="recentExecutionsContainer"></div>
        </div>
    </div>
</div>

<div id="createRuleModal" class="modal" style="display: none;">
    <div class="modal-content large">
        <div class="modal-header">
            <h2>Create Automation Rule</h2>
            <button class="close-modal" onclick="closeModal('createRuleModal')">&times;</button>
        </div>
        <div class="modal-body">
            <form id="automationRuleForm">
                <div class="form-group">
                    <label>Rule Name</label>
                    <input type="text" name="rule_name" required>
                </div>
                
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="3"></textarea>
                </div>

                <div class="automation-builder">
                    <div class="trigger-section">
                        <h4>When (Trigger)</h4>
                        <select name="trigger_type" onchange="updateTriggerConfig(this.value)">
                            <option value="">Select Trigger...</option>
                            <option value="schedule">On Schedule</option>
                            <option value="event">On Event</option>
                            <option value="condition">When Condition Met</option>
                            <option value="manual">Manual Trigger</option>
                        </select>
                        <div id="triggerConfig"></div>
                    </div>

                    <div class="action-section">
                        <h4>Then (Action)</h4>
                        <select name="action_type" onchange="updateActionConfig(this.value)">
                            <option value="">Select Action...</option>
                            <option value="finance">Finance Action</option>
                            <option value="goal">Update Goal</option>
                            <option value="task">Create Task</option>
                            <option value="notification">Send Notification</option>
                            <option value="calendar">Add to Calendar</option>
                        </select>
                        <div id="actionConfig"></div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Create Rule</button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal('createRuleModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function loadAutomationRules() {
    fetch('api/get_rules.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayActiveRules(data.rules);
            }
        });
}

function loadRecentExecutions() {
    fetch('api/get_executions.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayExecutions(data.executions);
            }
        });
}

function displayActiveRules(rules) {
    const container = document.getElementById('activeRulesContainer');
    if (rules.length === 0) {
        container.innerHTML = '<p class="no-data">No automation rules yet. Create your first rule!</p>';
        return;
    }

    container.innerHTML = rules.map(rule => `
        <div class="rule-item ${rule.is_active ? 'active' : 'inactive'}">
            <div class="rule-header">
                <h4>${escapeHtml(rule.rule_name)}</h4>
                <div class="rule-controls">
                    <button class="btn-icon" onclick="toggleRule(${rule.id})" title="${rule.is_active ? 'Disable' : 'Enable'}">
                        <i class="fas fa-toggle-${rule.is_active ? 'on' : 'off'}"></i>
                    </button>
                    <button class="btn-icon" onclick="editRule(${rule.id})" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn-icon" onclick="deleteRule(${rule.id})" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <p>${escapeHtml(rule.description || '')}</p>
            <div class="rule-stats">
                <span><i class="fas fa-bolt"></i> ${rule.execution_count} executions</span>
                <span><i class="fas fa-clock"></i> Last: ${formatDate(rule.last_triggered_at)}</span>
            </div>
        </div>
    `).join('');
}

function showCreateRuleModal() {
    document.getElementById('createRuleModal').style.display = 'flex';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

document.getElementById('automationRuleForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch('api/create_rule.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Automation rule created successfully', 'success');
            closeModal('createRuleModal');
            loadAutomationRules();
            this.reset();
        } else {
            showNotification(data.message || 'Failed to create rule', 'error');
        }
    });
});

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatDate(date) {
    if (!date) return 'Never';
    return new Date(date).toLocaleString();
}

loadAutomationRules();
loadRecentExecutions();
</script>

<style>
.automation-controls {
    margin: 20px 0;
    display: flex;
    gap: 10px;
}

.automation-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-top: 20px;
}

.automation-card {
    background: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.automation-card h3 {
    margin-bottom: 15px;
    color: #333;
}

.rule-item {
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 15px;
    margin-bottom: 10px;
}

.rule-item.active {
    border-left: 4px solid #4CAF50;
}

.rule-item.inactive {
    border-left: 4px solid #999;
    opacity: 0.7;
}

.rule-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.rule-controls {
    display: flex;
    gap: 5px;
}

.automation-builder {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin: 20px 0;
}

.trigger-section, .action-section {
    background: #f5f5f5;
    padding: 15px;
    border-radius: 6px;
}

@media (max-width: 768px) {
    .automation-grid, .automation-builder {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include '../../includes/footer.php'; ?>
