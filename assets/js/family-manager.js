let currentTab = 'members';
let familyMembers = [];

// Tab Switching
function switchTab(tab) {
    currentTab = tab;
    
    // Update tab buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active', 'border-primary', 'text-primary');
        btn.classList.add('border-transparent', 'text-gray-500', 'dark:text-gray-400');
    });
    document.getElementById(`tab-${tab}`).classList.add('active', 'border-primary', 'text-primary');
    document.getElementById(`tab-${tab}`).classList.remove('border-transparent', 'text-gray-500', 'dark:text-gray-400');
    
    // Update content
    document.querySelectorAll('.tab-content').forEach(content => content.classList.add('hidden'));
    document.getElementById(`content-${tab}`).classList.remove('hidden');
    
    // Load data
    loadTabData(tab);
}

// Load data for active tab
async function loadTabData(tab) {
    switch (tab) {
        case 'members':
            await loadMembers();
            break;
        case 'tasks':
            await loadTasks();
            break;
        case 'expenses':
            await loadExpenses();
            break;
        case 'grocery':
            await loadGroceryLists();
            break;
    }
}

// Members Functions
async function loadMembers() {
    try {
        const response = await fetch('/api/family.php?type=members');
        const data = await response.json();
        
        if (data.success) {
            familyMembers = data.data;
            renderMembers(data.data);
            updateMemberSelects();
        }
    } catch (error) {
        console.error('Error loading members:', error);
        showToast('Error loading family members', 'error');
    }
}

function renderMembers(members) {
    const container = document.getElementById('membersList');
    if (members.length === 0) {
        container.innerHTML = '<p class="text-gray-500 dark:text-gray-400 text-center col-span-full">No family members added yet. Click "Add Member" to get started.</p>';
        return;
    }
    
    container.innerHTML = members.map(member => `
        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
            <div class="flex justify-between items-start mb-2">
                <h3 class="font-semibold text-gray-900 dark:text-white">${escapeHtml(member.name)}</h3>
                <button onclick="deleteMember(${member.id})" class="text-red-500 hover:text-red-700">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            ${member.relationship ? `<p class="text-sm text-gray-600 dark:text-gray-300 mb-1"><i class="fas fa-heart text-xs"></i> ${escapeHtml(member.relationship)}</p>` : ''}
            ${member.email ? `<p class="text-sm text-gray-600 dark:text-gray-300 mb-1"><i class="fas fa-envelope text-xs"></i> ${escapeHtml(member.email)}</p>` : ''}
            ${member.phone ? `<p class="text-sm text-gray-600 dark:text-gray-300 mb-1"><i class="fas fa-phone text-xs"></i> ${escapeHtml(member.phone)}</p>` : ''}
            ${member.birthday ? `<p class="text-sm text-gray-600 dark:text-gray-300"><i class="fas fa-birthday-cake text-xs"></i> ${formatDate(member.birthday)}</p>` : ''}
        </div>
    `).join('');
}

function updateMemberSelects() {
    const taskSelect = document.getElementById('taskAssignedTo');
    const expenseSelect = document.getElementById('expensePaidBy');
    
    if (taskSelect) {
        taskSelect.innerHTML = '<option value="">Unassigned</option>' + 
            familyMembers.map(m => `<option value="${m.id}">${escapeHtml(m.name)}</option>`).join('');
    }
    
    if (expenseSelect) {
        expenseSelect.innerHTML = '<option value="">Select Member</option>' + 
            familyMembers.map(m => `<option value="${m.id}">${escapeHtml(m.name)}</option>`).join('');
    }
}

function showMemberModal() {
    document.getElementById('memberForm').reset();
    document.getElementById('memberId').value = '';
    document.getElementById('memberModal').classList.remove('hidden');
}

function closeMemberModal() {
    document.getElementById('memberModal').classList.add('hidden');
}

document.getElementById('memberForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const data = {
        type: 'member',
        name: document.getElementById('memberName').value,
        relationship: document.getElementById('memberRelationship').value,
        email: document.getElementById('memberEmail').value,
        phone: document.getElementById('memberPhone').value,
        birthday: document.getElementById('memberBirthday').value,
        notes: document.getElementById('memberNotes').value
    };
    
    try {
        const response = await fetch('/api/family.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        if (result.success) {
            showToast('Family member added successfully', 'success');
            closeMemberModal();
            loadMembers();
        } else {
            showToast('Error adding member', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error adding member', 'error');
    }
});

async function deleteMember(id) {
    if (!confirm('Are you sure you want to remove this family member?')) return;
    
    try {
        const response = await fetch(`/api/family.php?type=member&id=${id}`, {
            method: 'DELETE'
        });
        
        const result = await response.json();
        if (result.success) {
            showToast('Member removed successfully', 'success');
            loadMembers();
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error removing member', 'error');
    }
}

// Tasks Functions
async function loadTasks() {
    try {
        const response = await fetch('/api/family.php?type=tasks');
        const data = await response.json();
        
        if (data.success) {
            renderTasks(data.data);
        }
    } catch (error) {
        console.error('Error loading tasks:', error);
        showToast('Error loading tasks', 'error');
    }
}

function renderTasks(tasks) {
    const container = document.getElementById('tasksList');
    if (tasks.length === 0) {
        container.innerHTML = '<p class="text-gray-500 dark:text-gray-400 text-center">No household tasks yet. Click "Add Task" to create one.</p>';
        return;
    }
    
    container.innerHTML = tasks.map(task => {
        const priorityColors = {
            low: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
            medium: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
            high: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
        };
        
        const assignedMember = familyMembers.find(m => m.id == task.assigned_to_member_id);
        
        return `
            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <h3 class="font-semibold text-gray-900 dark:text-white mb-1">${escapeHtml(task.title)}</h3>
                        ${task.description ? `<p class="text-sm text-gray-600 dark:text-gray-300 mb-2">${escapeHtml(task.description)}</p>` : ''}
                        <div class="flex flex-wrap gap-2 text-sm">
                            <span class="px-2 py-1 rounded ${priorityColors[task.priority] || priorityColors.medium}">
                                ${task.priority || 'medium'}
                            </span>
                            ${assignedMember ? `<span class="px-2 py-1 bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 rounded">
                                <i class="fas fa-user text-xs"></i> ${escapeHtml(assignedMember.name)}
                            </span>` : ''}
                            ${task.due_date ? `<span class="px-2 py-1 bg-gray-200 text-gray-700 dark:bg-gray-600 dark:text-gray-300 rounded">
                                <i class="fas fa-calendar text-xs"></i> ${formatDate(task.due_date)}
                            </span>` : ''}
                            ${task.category ? `<span class="px-2 py-1 bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200 rounded">
                                ${escapeHtml(task.category)}
                            </span>` : ''}
                        </div>
                    </div>
                    <button onclick="deleteTask(${task.id})" class="text-red-500 hover:text-red-700 ml-2">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
    }).join('');
}

function showTaskModal() {
    document.getElementById('taskForm').reset();
    document.getElementById('taskId').value = '';
    document.getElementById('taskModal').classList.remove('hidden');
}

function closeTaskModal() {
    document.getElementById('taskModal').classList.add('hidden');
}

document.getElementById('taskForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const data = {
        type: 'task',
        title: document.getElementById('taskTitle').value,
        description: document.getElementById('taskDescription').value,
        assigned_to_member_id: document.getElementById('taskAssignedTo').value || null,
        due_date: document.getElementById('taskDueDate').value || null,
        priority: document.getElementById('taskPriority').value,
        category: document.getElementById('taskCategory').value
    };
    
    try {
        const response = await fetch('/api/family.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        if (result.success) {
            showToast('Task added successfully', 'success');
            closeTaskModal();
            loadTasks();
        } else {
            showToast('Error adding task', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error adding task', 'error');
    }
});

async function deleteTask(id) {
    if (!confirm('Are you sure you want to delete this task?')) return;
    
    try {
        const response = await fetch(`/api/family.php?type=task&id=${id}`, {
            method: 'DELETE'
        });
        
        const result = await response.json();
        if (result.success) {
            showToast('Task deleted successfully', 'success');
            loadTasks();
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error deleting task', 'error');
    }
}

// Expenses Functions
async function loadExpenses() {
    try {
        const response = await fetch('/api/family.php?type=expenses');
        const data = await response.json();
        
        if (data.success) {
            renderExpenses(data.data);
        }
    } catch (error) {
        console.error('Error loading expenses:', error);
        showToast('Error loading expenses', 'error');
    }
}

function renderExpenses(expenses) {
    const container = document.getElementById('expensesList');
    if (expenses.length === 0) {
        container.innerHTML = '<p class="text-gray-500 dark:text-gray-400 text-center">No shared expenses yet. Click "Add Expense" to track one.</p>';
        return;
    }
    
    container.innerHTML = expenses.map(expense => {
        const paidByMember = familyMembers.find(m => m.id == expense.paid_by_member_id);
        
        return `
            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="font-semibold text-gray-900 dark:text-white">${escapeHtml(expense.description)}</h3>
                            <span class="text-lg font-bold text-primary">$${parseFloat(expense.total_amount).toFixed(2)}</span>
                        </div>
                        <div class="flex flex-wrap gap-2 text-sm">
                            <span class="px-2 py-1 bg-gray-200 text-gray-700 dark:bg-gray-600 dark:text-gray-300 rounded">
                                <i class="fas fa-calendar text-xs"></i> ${formatDate(expense.expense_date)}
                            </span>
                            ${paidByMember ? `<span class="px-2 py-1 bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 rounded">
                                <i class="fas fa-user text-xs"></i> Paid by ${escapeHtml(paidByMember.name)}
                            </span>` : ''}
                            ${expense.category ? `<span class="px-2 py-1 bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200 rounded">
                                ${escapeHtml(expense.category)}
                            </span>` : ''}
                            <span class="px-2 py-1 bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 rounded">
                                ${expense.split_type || 'equal'} split
                            </span>
                        </div>
                    </div>
                    <button onclick="deleteExpense(${expense.id})" class="text-red-500 hover:text-red-700 ml-2">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
    }).join('');
}

function showExpenseModal() {
    document.getElementById('expenseForm').reset();
    document.getElementById('expenseId').value = '';
    document.getElementById('expenseDate').value = new Date().toISOString().split('T')[0];
    document.getElementById('expenseModal').classList.remove('hidden');
}

function closeExpenseModal() {
    document.getElementById('expenseModal').classList.add('hidden');
}

document.getElementById('expenseForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const data = {
        type: 'expense',
        description: document.getElementById('expenseDescription').value,
        total_amount: document.getElementById('expenseAmount').value,
        expense_date: document.getElementById('expenseDate').value,
        paid_by_member_id: document.getElementById('expensePaidBy').value || null,
        category: document.getElementById('expenseCategory').value,
        split_type: document.getElementById('expenseSplitType').value
    };
    
    try {
        const response = await fetch('/api/family.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        if (result.success) {
            showToast('Expense added successfully', 'success');
            closeExpenseModal();
            loadExpenses();
        } else {
            showToast('Error adding expense', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error adding expense', 'error');
    }
});

async function deleteExpense(id) {
    if (!confirm('Are you sure you want to delete this expense?')) return;
    
    try {
        const response = await fetch(`/api/family.php?type=expense&id=${id}`, {
            method: 'DELETE'
        });
        
        const result = await response.json();
        if (result.success) {
            showToast('Expense deleted successfully', 'success');
            loadExpenses();
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error deleting expense', 'error');
    }
}

// Grocery Lists Functions
async function loadGroceryLists() {
    try {
        const response = await fetch('/api/family.php?type=grocery');
        const data = await response.json();
        
        if (data.success) {
            renderGroceryLists(data.data);
        }
    } catch (error) {
        console.error('Error loading grocery lists:', error);
        showToast('Error loading grocery lists', 'error');
    }
}

function renderGroceryLists(lists) {
    const container = document.getElementById('groceryLists');
    if (lists.length === 0) {
        container.innerHTML = '<p class="text-gray-500 dark:text-gray-400 text-center">No grocery lists yet.</p>';
        return;
    }
    
    container.innerHTML = lists.map(list => `
        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="font-semibold text-gray-900 dark:text-white">${escapeHtml(list.name || 'Grocery List')}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300">${list.status || 'Active'}</p>
                </div>
                <button onclick="deleteGroceryList(${list.id})" class="text-red-500 hover:text-red-700">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `).join('');
}

function showGroceryModal() {
    showToast('Grocery list feature coming soon!', 'info');
}

async function deleteGroceryList(id) {
    if (!confirm('Are you sure you want to delete this grocery list?')) return;
    
    try {
        const response = await fetch(`/api/family.php?type=grocery&id=${id}`, {
            method: 'DELETE'
        });
        
        const result = await response.json();
        if (result.success) {
            showToast('Grocery list deleted successfully', 'success');
            loadGroceryLists();
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error deleting grocery list', 'error');
    }
}

// Utility Functions
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatDate(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

function formatDateTime(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
}

function showToast(message, type = 'info') {
    if (typeof window.showToast === 'function') {
        window.showToast(message, type);
    } else {
        alert(message);
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    loadMembers();
});
