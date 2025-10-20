document.addEventListener('DOMContentLoaded', function() {
    loadTeamMembers();
    loadSharedTasks();
    loadDocuments();
    initChatIfAvailable();
});

async function loadTeamMembers() {
    try {
        const response = await fetch('/api/team_collaboration.php?action=get_members');
        const result = await response.json();
        if (result.success) {
            renderTeamMembers(result.members || []);
        }
    } catch (error) {
        console.error('Error loading team members:', error);
    }
}

function renderTeamMembers(members) {
    const container = document.getElementById('teamMembersContainer');
    if (!container) return;
    
    if (members.length === 0) {
        container.innerHTML = '<p class="text-gray-500 text-center py-4">No team members yet</p>';
        return;
    }
    
    container.innerHTML = members.map(member => `
        <div class="member-card flex items-center gap-3 p-3 bg-white dark:bg-gray-800 rounded-lg shadow-sm">
            <div class="w-10 h-10 bg-primary rounded-full flex items-center justify-center text-white font-semibold">
                ${member.name ? member.name.charAt(0).toUpperCase() : 'U'}
            </div>
            <div class="flex-1">
                <h4 class="font-semibold">${escapeHtml(member.name)}</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400">${escapeHtml(member.role || 'Member')}</p>
            </div>
            <div class="flex gap-2">
                <button onclick="messageUser(${member.id})" class="btn btn-sm btn-secondary">
                    <i class="fas fa-comment"></i>
                </button>
                ${member.can_remove ? `<button onclick="removeMember(${member.id})" class="btn btn-sm btn-danger"><i class="fas fa-times"></i></button>` : ''}
            </div>
        </div>
    `).join('');
}

async function loadSharedTasks() {
    try {
        const response = await fetch('/api/team_collaboration.php?action=get_tasks');
        const result = await response.json();
        if (result.success) {
            renderSharedTasks(result.tasks || []);
        }
    } catch (error) {
        console.error('Error loading shared tasks:', error);
    }
}

function renderSharedTasks(tasks) {
    const container = document.getElementById('sharedTasksContainer');
    if (!container) return;
    
    if (tasks.length === 0) {
        container.innerHTML = '<p class="text-gray-500 text-center py-4">No shared tasks</p>';
        return;
    }
    
    container.innerHTML = tasks.map(task => `
        <div class="task-card p-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm">
            <div class="flex justify-between items-start">
                <div class="flex-1">
                    <h4 class="font-semibold">${escapeHtml(task.title)}</h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">${escapeHtml(task.description || '')}</p>
                    <div class="mt-2 flex gap-2">
                        ${task.assigned_to ? `<span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">Assigned to: ${escapeHtml(task.assigned_to_name)}</span>` : ''}
                        ${task.due_date ? `<span class="text-xs bg-gray-100 text-gray-800 px-2 py-1 rounded">Due: ${formatDate(task.due_date)}</span>` : ''}
                    </div>
                </div>
                <span class="badge badge-${task.status}">${task.status || 'pending'}</span>
            </div>
        </div>
    `).join('');
}

async function loadDocuments() {
    try {
        const response = await fetch('/api/team_collaboration.php?action=get_documents');
        const result = await response.json();
        if (result.success) {
            renderDocuments(result.documents || []);
        }
    } catch (error) {
        console.error('Error loading documents:', error);
    }
}

function renderDocuments(documents) {
    const container = document.getElementById('documentsContainer');
    if (!container) return;
    
    if (documents.length === 0) {
        container.innerHTML = '<p class="text-gray-500 text-center py-4">No shared documents</p>';
        return;
    }
    
    container.innerHTML = documents.map(doc => `
        <div class="document-card flex items-center gap-3 p-3 bg-white dark:bg-gray-800 rounded-lg shadow-sm">
            <i class="fas fa-file-alt text-2xl text-gray-400"></i>
            <div class="flex-1">
                <h4 class="font-semibold">${escapeHtml(doc.title)}</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400">Shared by ${escapeHtml(doc.shared_by_name)} · ${formatDate(doc.created_at)}</p>
            </div>
            <button onclick="viewDocument(${doc.id})" class="btn btn-sm btn-primary">View</button>
        </div>
    `).join('');
}

function initChatIfAvailable() {
    const chatInput = document.getElementById('chatInput');
    if (chatInput) {
        chatInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });
    }
}

async function inviteMember() {
    const email = prompt('Enter team member email:');
    if (!email) return;
    
    try {
        const response = await fetch('/api/team_collaboration.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action: 'invite_member',
                email: email
            })
        });
        
        const result = await response.json();
        if (result.success) {
            showToast('success', 'Success', 'Invitation sent successfully');
            loadTeamMembers();
        } else {
            showToast('error', 'Error', result.message || 'Failed to send invitation');
        }
    } catch (error) {
        console.error('Error inviting member:', error);
        showToast('error', 'Error', 'Failed to send invitation');
    }
}

async function removeMember(id) {
    if (!confirm('Remove this team member?')) return;
    
    try {
        const response = await fetch('/api/team_collaboration.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action: 'remove_member',
                id: id
            })
        });
        
        const result = await response.json();
        if (result.success) {
            showToast('success', 'Success', 'Member removed');
            loadTeamMembers();
        }
    } catch (error) {
        console.error('Error removing member:', error);
    }
}

async function sendMessage() {
    const input = document.getElementById('chatInput');
    if (!input || !input.value.trim()) return;
    
    const message = input.value.trim();
    input.value = '';
    
    try {
        const response = await fetch('/api/team_collaboration.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action: 'send_message',
                message: message
            })
        });
        
        const result = await response.json();
        if (result.success) {
            appendMessage({text: message, is_own: true, created_at: new Date()});
        }
    } catch (error) {
        console.error('Error sending message:', error);
    }
}

function appendMessage(msg) {
    const container = document.getElementById('chatMessages');
    if (!container) return;
    
    const msgDiv = document.createElement('div');
    msgDiv.className = `message ${msg.is_own ? 'own-message' : 'other-message'}`;
    msgDiv.innerHTML = `
        <p>${escapeHtml(msg.text)}</p>
        <span class="text-xs text-gray-500">${formatTime(msg.created_at)}</span>
    `;
    container.appendChild(msgDiv);
    container.scrollTop = container.scrollHeight;
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

function formatTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
