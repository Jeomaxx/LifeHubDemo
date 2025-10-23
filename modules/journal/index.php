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

$pageTitle = 'Journal';
$activePage = 'journal';
include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="main-content">
    <div class="page-header">
        <h1><i class="fas fa-book"></i> Communication & Journaling Suite</h1>
        <p>Reflect, remember, and grow with AI-powered journaling</p>
    </div>

    <div class="journal-dashboard">
        <div class="quick-entry">
            <h3>Quick Journal Entry</h3>
            <form id="quickJournalForm">
                <input type="text" name="title" placeholder="Entry Title" required>
                <textarea name="content" rows="6" placeholder="What's on your mind..."></textarea>
                <div class="entry-controls">
                    <select name="mood">
                        <option value="">Select mood...</option>
                        <option value="happy">😊 Happy</option>
                        <option value="excited">🎉 Excited</option>
                        <option value="calm">😌 Calm</option>
                        <option value="neutral">😐 Neutral</option>
                        <option value="sad">😢 Sad</option>
                        <option value="anxious">😰 Anxious</option>
                        <option value="angry">😠 Angry</option>
                    </select>
                    <button type="button" class="btn btn-secondary" onclick="startVoiceRecording()">
                        <i class="fas fa-microphone"></i> Voice Entry
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Entry
                    </button>
                </div>
            </form>
        </div>

        <div class="journal-entries">
            <h3>Recent Entries</h3>
            <div id="entriesContainer"></div>
        </div>

        <div class="journal-memories">
            <h3><i class="fas fa-clock"></i> On This Day...</h3>
            <div id="memoriesContainer"></div>
        </div>

        <div class="journal-insights">
            <h3><i class="fas fa-lightbulb"></i> AI Insights</h3>
            <div id="insightsContainer"></div>
        </div>
    </div>
</div>

<script>
document.getElementById('quickJournalForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch('api/create_entry.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Journal entry saved successfully', 'success');
            loadJournalEntries();
            this.reset();
        } else {
            showNotification(data.message || 'Failed to save entry', 'error');
        }
    });
});

function loadJournalEntries() {
    fetch('api/get_entries.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayEntries(data.entries);
            }
        });
}

function displayEntries(entries) {
    const container = document.getElementById('entriesContainer');
    if (entries.length === 0) {
        container.innerHTML = '<p class="no-data">No journal entries yet. Start writing!</p>';
        return;
    }

    container.innerHTML = entries.map(entry => `
        <div class="entry-card">
            <div class="entry-header">
                <h4>${escapeHtml(entry.title)}</h4>
                <span class="entry-date">${formatDate(entry.entry_date)}</span>
            </div>
            <p>${escapeHtml(entry.content ? entry.content.substring(0, 200) + '...' : '')}</p>
            ${entry.mood ? `<span class="mood-indicator">${getMoodEmoji(entry.mood)}</span>` : ''}
            ${entry.ai_summary ? `<div class="ai-summary"><i class="fas fa-robot"></i> ${entry.ai_summary}</div>` : ''}
        </div>
    `).join('');
}

function loadMemories() {
    fetch('api/get_memories.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayMemories(data.memories);
            }
        });
}

function displayMemories(memories) {
    const container = document.getElementById('memoriesContainer');
    if (memories.length === 0) {
        container.innerHTML = '<p class="no-data">No memories from this day in previous years.</p>';
        return;
    }

    container.innerHTML = memories.map(memory => `
        <div class="memory-card">
            <p class="memory-date">${formatDate(memory.memory_date)}</p>
            <p>${escapeHtml(memory.content)}</p>
        </div>
    `).join('');
}

function getMoodEmoji(mood) {
    const emojis = {
        'happy': '😊',
        'excited': '🎉',
        'calm': '😌',
        'neutral': '😐',
        'sad': '😢',
        'anxious': '😰',
        'angry': '😠'
    };
    return emojis[mood] || '';
}

function formatDate(date) {
    return new Date(date).toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function startVoiceRecording() {
    showNotification('Voice recording feature coming soon!', 'info');
}

loadJournalEntries();
loadMemories();
</script>

<style>
.journal-dashboard {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-top: 20px;
}

.quick-entry {
    background: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    grid-column: 1 / -1;
}

.quick-entry form {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.quick-entry input, .quick-entry textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
}

.entry-controls {
    display: flex;
    gap: 10px;
    align-items: center;
}

.entry-controls select {
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 6px;
}

.journal-entries, .journal-memories, .journal-insights {
    background: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.entry-card, .memory-card {
    border-left: 4px solid #667eea;
    padding: 15px;
    margin-bottom: 15px;
    background: #f9f9f9;
    border-radius: 6px;
}

.entry-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.entry-date {
    font-size: 12px;
    color: #666;
}

.mood-indicator {
    font-size: 24px;
    margin-top: 10px;
    display: inline-block;
}

.ai-summary {
    margin-top: 12px;
    padding: 10px;
    background: #E3F2FD;
    border-radius: 6px;
    font-size: 14px;
    font-style: italic;
}

.memory-date {
    font-weight: bold;
    color: #667eea;
    margin-bottom: 8px;
}

@media (max-width: 768px) {
    .journal-dashboard {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include '../../includes/footer.php'; ?>
