<?php
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$auth = new Auth();
requireLogin();

$pageTitle = 'Family Manager';
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <i data-lucide="users" class="text-primary"></i>
                Household & Family Manager
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2">Manage family tasks, expenses, and grocery lists</p>
        </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold mb-4">Family Members</h2>
            <div id="membersList"></div>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold mb-4">Household Tasks</h2>
            <div id="tasksList"></div>
        </div>
    </div>
</div>

<script>
async function loadData() {
    const members = await fetch('/api/family.php?type=members').then(r => r.json());
    if (members.success) {
        document.getElementById('membersList').innerHTML = members.data.length > 0 
            ? members.data.map(m => `<div class="p-3 border-b">${m.name} - ${m.relationship || 'Family'}</div>`).join('') 
            : '<p class="text-gray-500">No family members added yet.</p>';
    }
    
    const tasks = await fetch('/api/family.php?type=tasks').then(r => r.json());
    if (tasks.success) {
        document.getElementById('tasksList').innerHTML = tasks.data.length > 0 
            ? tasks.data.map(t => `<div class="p-3 border-b">${t.title}</div>`).join('') 
            : '<p class="text-gray-500">No tasks yet.</p>';
    }
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

document.addEventListener('DOMContentLoaded', loadData);
</script>

<?php include 'includes/footer.php'; ?>
