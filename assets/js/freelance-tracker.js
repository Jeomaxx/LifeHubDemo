let currentTab = 'clients';

document.addEventListener('DOMContentLoaded', function() {
    loadStats();
    loadClients();
    
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const tab = this.dataset.tab;
            switchTab(tab);
        });
    });
    
    document.getElementById('clientForm').addEventListener('submit', saveClient);
    document.getElementById('projectStatusFilter')?.addEventListener('change', function() {
        loadProjects(this.value);
    });
    document.getElementById('invoiceStatusFilter')?.addEventListener('change', function() {
        loadInvoices(this.value);
    });
});

function switchTab(tab) {
    currentTab = tab;
    
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active', 'text-primary', 'border-primary', 'border-b-2');
        btn.classList.add('text-gray-600', 'dark:text-gray-400');
    });
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    const activeBtn = document.querySelector(`[data-tab="${tab}"]`);
    activeBtn.classList.add('active', 'text-primary', 'border-primary', 'border-b-2');
    activeBtn.classList.remove('text-gray-600', 'dark:text-gray-400');
    
    document.getElementById(tab + 'Tab').classList.remove('hidden');
    
    if (tab === 'clients') loadClients();
    else if (tab === 'projects') loadProjects();
    else if (tab === 'invoices') loadInvoices();
    else if (tab === 'time') loadTimeEntries();
}

async function loadStats() {
    try {
        const response = await fetch('/api/freelance.php?action=stats');
        const result = await response.json();
        
        if (result.success) {
            document.getElementById('activeProjects').textContent = result.data.active_projects;
            document.getElementById('activeClients').textContent = result.data.active_clients;
            document.getElementById('pendingInvoices').textContent = result.data.pending_invoices;
            document.getElementById('totalEarned').textContent = '$' + parseFloat(result.data.total_earned).toFixed(2);
        }
    } catch (error) {
        console.error('Error loading stats:', error);
    }
}

async function loadClients() {
    try {
        const response = await fetch('/api/freelance.php?type=clients');
        const result = await response.json();
        
        if (result.success) {
            const clientsList = document.getElementById('clientsList');
            if (result.data.length === 0) {
                clientsList.innerHTML = '<p class="text-gray-500 text-center py-8">No clients yet. Add your first client to get started!</p>';
                return;
            }
            
            clientsList.innerHTML = result.data.map(client => `
                <div class="border-b border-gray-200 dark:border-gray-700 py-4 flex justify-between items-start">
                    <div class="flex-1">
                        <h3 class="font-semibold text-lg">${escapeHtml(client.client_name)}</h3>
                        ${client.company_name ? `<p class="text-sm text-gray-600">${escapeHtml(client.company_name)}</p>` : ''}
                        <div class="mt-2 flex gap-4 text-sm text-gray-600 dark:text-gray-400">
                            ${client.email ? `<span><i data-lucide="mail" class="w-4 h-4 inline"></i> ${escapeHtml(client.email)}</span>` : ''}
                            ${client.phone ? `<span><i data-lucide="phone" class="w-4 h-4 inline"></i> ${escapeHtml(client.phone)}</span>` : ''}
                        </div>
                        ${client.notes ? `<p class="mt-2 text-sm text-gray-500">${escapeHtml(client.notes)}</p>` : ''}
                    </div>
                    <div class="flex gap-2">
                        <button onclick="deleteClient(${client.id})" class="text-red-600 hover:text-red-700">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
            `).join('');
            
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }
    } catch (error) {
        console.error('Error loading clients:', error);
    }
}

async function loadProjects(status = '') {
    try {
        const url = status ? `/api/freelance.php?type=projects&status=${status}` : '/api/freelance.php?type=projects';
        const response = await fetch(url);
        const result = await response.json();
        
        if (result.success) {
            const projectsList = document.getElementById('projectsList');
            if (result.data.length === 0) {
                projectsList.innerHTML = '<p class="text-gray-500 text-center py-8">No projects found.</p>';
                return;
            }
            
            projectsList.innerHTML = result.data.map(project => {
                const statusColors = {
                    'in_progress': 'bg-blue-100 text-blue-800',
                    'completed': 'bg-green-100 text-green-800',
                    'on_hold': 'bg-yellow-100 text-yellow-800',
                    'cancelled': 'bg-red-100 text-red-800'
                };
                
                return `
                <div class="border-b border-gray-200 dark:border-gray-700 py-4">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <h3 class="font-semibold text-lg">${escapeHtml(project.project_name)}</h3>
                            ${project.client_name ? `<p class="text-sm text-gray-600">Client: ${escapeHtml(project.client_name)}</p>` : ''}
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs ${statusColors[project.status] || 'bg-gray-100 text-gray-800'}">
                            ${project.status.replace('_', ' ').toUpperCase()}
                        </span>
                    </div>
                    ${project.description ? `<p class="text-sm text-gray-500 mb-2">${escapeHtml(project.description)}</p>` : ''}
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                        ${project.budget ? `<div><span class="text-gray-600">Budget:</span> $${parseFloat(project.budget).toFixed(2)}</div>` : ''}
                        ${project.hourly_rate ? `<div><span class="text-gray-600">Rate:</span> $${parseFloat(project.hourly_rate).toFixed(2)}/hr</div>` : ''}
                        ${project.actual_hours ? `<div><span class="text-gray-600">Hours:</span> ${parseFloat(project.actual_hours).toFixed(1)}h</div>` : ''}
                        ${project.deadline ? `<div><span class="text-gray-600">Deadline:</span> ${project.deadline}</div>` : ''}
                    </div>
                </div>
                `;
            }).join('');
        }
    } catch (error) {
        console.error('Error loading projects:', error);
    }
}

async function loadInvoices(status = '') {
    try {
        const url = status ? `/api/freelance.php?type=invoices&status=${status}` : '/api/freelance.php?type=invoices';
        const response = await fetch(url);
        const result = await response.json();
        
        if (result.success) {
            const invoicesList = document.getElementById('invoicesList');
            if (result.data.length === 0) {
                invoicesList.innerHTML = '<p class="text-gray-500 text-center py-8">No invoices found.</p>';
                return;
            }
            
            invoicesList.innerHTML = result.data.map(invoice => {
                const statusColors = {
                    'draft': 'bg-gray-100 text-gray-800',
                    'sent': 'bg-blue-100 text-blue-800',
                    'paid': 'bg-green-100 text-green-800',
                    'overdue': 'bg-red-100 text-red-800'
                };
                
                return `
                <div class="border-b border-gray-200 dark:border-gray-700 py-4 flex justify-between items-start">
                    <div class="flex-1">
                        <h3 class="font-semibold text-lg">${escapeHtml(invoice.invoice_number)}</h3>
                        ${invoice.client_name ? `<p class="text-sm text-gray-600">Client: ${escapeHtml(invoice.client_name)}</p>` : ''}
                        <div class="mt-2 text-sm text-gray-600">
                            <span>Date: ${invoice.invoice_date}</span> | 
                            <span>Due: ${invoice.due_date || 'N/A'}</span> | 
                            <span class="font-semibold text-lg">$${parseFloat(invoice.total_amount).toFixed(2)}</span>
                        </div>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs ${statusColors[invoice.status] || 'bg-gray-100 text-gray-800'}">
                        ${invoice.status.toUpperCase()}
                    </span>
                </div>
                `;
            }).join('');
        }
    } catch (error) {
        console.error('Error loading invoices:', error);
    }
}

async function loadTimeEntries() {
    try {
        const response = await fetch('/api/freelance.php?type=time-entries');
        const result = await response.json();
        
        if (result.success) {
            const timeList = document.getElementById('timeEntriesList');
            if (result.data.length === 0) {
                timeList.innerHTML = '<p class="text-gray-500 text-center py-8">No time entries yet.</p>';
                return;
            }
            
            timeList.innerHTML = result.data.map(entry => `
                <div class="border-b border-gray-200 dark:border-gray-700 py-3 flex justify-between items-center">
                    <div>
                        <p class="font-medium">${escapeHtml(entry.project_name || 'No Project')}</p>
                        <p class="text-sm text-gray-600">${escapeHtml(entry.description || '')}</p>
                        <p class="text-xs text-gray-500">${entry.entry_date}</p>
                    </div>
                    <div class="text-right">
                        <p class="font-semibold">${parseFloat(entry.hours).toFixed(2)}h</p>
                        <span class="text-xs ${entry.billable ? 'text-green-600' : 'text-gray-500'}">
                            ${entry.billable ? 'Billable' : 'Non-billable'}
                        </span>
                    </div>
                </div>
            `).join('');
        }
    } catch (error) {
        console.error('Error loading time entries:', error);
    }
}

function openClientModal() {
    document.getElementById('clientModal').classList.remove('hidden');
}

function openProjectModal() {
    alert('Project modal coming soon! Use API to create projects.');
}

function openInvoiceModal() {
    alert('Invoice modal coming soon! Use API to create invoices.');
}

function openTimeEntryModal() {
    alert('Time entry modal coming soon! Use API to log time.');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

async function saveClient(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    
    try {
        const response = await fetch('/api/freelance.php?type=client', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        if (result.success) {
            closeModal('clientModal');
            e.target.reset();
            loadClients();
            loadStats();
            showToast('Client added successfully!', 'success');
        } else {
            showToast(result.message, 'error');
        }
    } catch (error) {
        showToast('Error saving client', 'error');
        console.error(error);
    }
}

async function deleteClient(id) {
    if (!confirm('Are you sure you want to delete this client?')) return;
    
    try {
        const response = await fetch(`/api/freelance.php?type=client&id=${id}`, {
            method: 'DELETE'
        });
        
        const result = await response.json();
        if (result.success) {
            loadClients();
            loadStats();
            showToast('Client deleted successfully!', 'success');
        } else {
            showToast(result.message, 'error');
        }
    } catch (error) {
        showToast('Error deleting client', 'error');
        console.error(error);
    }
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showToast(message, type = 'info') {
    console.log(`[${type.toUpperCase()}] ${message}`);
}
