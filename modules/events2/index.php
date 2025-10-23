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

$pageTitle = 'Smart Reminders';
$activePage = 'events2';
include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="main-content">
    <div class="page-header">
        <h1><i class="fas fa-bell"></i> Event & Reminder System 2.0</h1>
        <p>Advanced reminders with smart triggers and external calendar sync</p>
    </div>

    <div class="reminders-dashboard">
        <div class="create-reminder">
            <h3>Create Smart Reminder</h3>
            <form id="reminderForm">
                <input type="text" name="title" placeholder="Reminder title" required>
                <textarea name="description" rows="2" placeholder="Description"></textarea>
                
                <div class="reminder-type-select">
                    <label>
                        <input type="radio" name="reminder_type" value="time_based" checked> Time-Based
                    </label>
                    <label>
                        <input type="radio" name="reminder_type" value="location_based"> Location-Based
                    </label>
                    <label>
                        <input type="radio" name="reminder_type" value="context_based"> Context-Based
                    </label>
                </div>

                <div id="timeBasedConfig">
                    <input type="datetime-local" name="trigger_time">
                    <select name="recurrence_pattern">
                        <option value="">No recurrence</option>
                        <option value="daily">Daily</option>
                        <option value="weekly">Weekly</option>
                        <option value="monthly">Monthly</option>
                    </select>
                </div>

                <div class="smart-snooze">
                    <label>
                        <input type="checkbox" name="smart_snooze_enabled"> Enable Smart Snooze
                    </label>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create Reminder
                </button>
            </form>
        </div>

        <div class="active-reminders">
            <h3>Active Reminders</h3>
            <div id="remindersContainer"></div>
        </div>

        <div class="calendar-sync">
            <h3><i class="fas fa-calendar-alt"></i> Calendar Integration</h3>
            <button class="btn btn-secondary" onclick="syncGoogleCalendar()">
                <i class="fab fa-google"></i> Sync Google Calendar
            </button>
            <button class="btn btn-secondary" onclick="syncOutlookCalendar()">
                <i class="fab fa-microsoft"></i> Sync Outlook Calendar
            </button>
            <div id="syncStatusContainer"></div>
        </div>
    </div>
</div>

<script>
document.getElementById('reminderForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch('api/create_reminder.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Reminder created successfully', 'success');
            loadReminders();
            this.reset();
        } else {
            showNotification(data.message || 'Failed to create reminder', 'error');
        }
    });
});

function loadReminders() {
    fetch('api/get_reminders.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayReminders(data.reminders);
            }
        });
}

function displayReminders(reminders) {
    const container = document.getElementById('remindersContainer');
    if (reminders.length === 0) {
        container.innerHTML = '<p class="no-data">No active reminders. Create your first one!</p>';
        return;
    }

    container.innerHTML = reminders.map(reminder => `
        <div class="reminder-card">
            <div class="reminder-header">
                <h4>${escapeHtml(reminder.title)}</h4>
                <button class="btn-icon" onclick="toggleReminder(${reminder.id})">
                    <i class="fas fa-toggle-${reminder.is_active ? 'on' : 'off'}"></i>
                </button>
            </div>
            <p>${escapeHtml(reminder.description || '')}</p>
            <div class="reminder-meta">
                <span class="type-badge">${reminder.reminder_type}</span>
                <span class="next-trigger">Next: ${formatDate(reminder.next_trigger_at)}</span>
            </div>
        </div>
    `).join('');
}

function formatDate(date) {
    if (!date) return 'Not set';
    return new Date(date).toLocaleString();
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function syncGoogleCalendar() {
    showNotification('Google Calendar sync coming soon!', 'info');
}

function syncOutlookCalendar() {
    showNotification('Outlook Calendar sync coming soon!', 'info');
}

loadReminders();
</script>

<style>
.reminders-dashboard {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-top: 20px;
}

.create-reminder, .active-reminders, .calendar-sync {
    background: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.reminder-type-select {
    display: flex;
    gap: 15px;
    margin: 15px 0;
}

.reminder-card {
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 15px;
    margin-bottom: 15px;
}

.reminder-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.reminder-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 10px;
}

.type-badge {
    background: #667eea;
    color: white;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
}

.next-trigger {
    font-size: 12px;
    color: #666;
}
</style>

<?php include '../../includes/footer.php'; ?>
