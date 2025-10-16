<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$pageTitle = 'Water Tracker';
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <i class="fas fa-tint text-primary"></i>
                <?php echo t('Water Tracker'); ?>
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1"><?php echo t('Stay hydrated! Track your daily water intake'); ?></p>
        </div>
    </div>

    <!-- Today's Progress -->
    <div class="bg-gradient-to-r from-blue-500 to-cyan-500 rounded-lg p-6 text-white mb-6">
        <div class="text-center">
            <h2 class="text-2xl font-bold mb-2">Today's Water Intake</h2>
            <div class="relative w-48 h-48 mx-auto mb-4">
                <svg class="w-full h-full transform -rotate-90">
                    <circle cx="96" cy="96" r="88" stroke="rgba(255,255,255,0.3)" stroke-width="12" fill="none"/>
                    <circle id="progressCircle" cx="96" cy="96" r="88" stroke="white" stroke-width="12" fill="none" 
                        stroke-dasharray="552.92" stroke-dashoffset="552.92" stroke-linecap="round"/>
                </svg>
                <div class="absolute inset-0 flex flex-col items-center justify-center">
                    <span id="currentIntake" class="text-4xl font-bold">0</span>
                    <span class="text-sm opacity-90">/ <span id="goalAmount">2000</span> ml</span>
                </div>
            </div>
            <div class="flex gap-4 justify-center">
                <button onclick="addWater(250)" class="bg-white/20 hover:bg-white/30 px-6 py-2 rounded-lg backdrop-blur-sm">+250ml</button>
                <button onclick="addWater(500)" class="bg-white/20 hover:bg-white/30 px-6 py-2 rounded-lg backdrop-blur-sm">+500ml</button>
                <button onclick="openCustomModal()" class="bg-white/20 hover:bg-white/30 px-6 py-2 rounded-lg backdrop-blur-sm">Custom</button>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Weekly Average</p>
                    <p id="weeklyAvg" class="text-2xl font-bold text-gray-900 dark:text-white">0 ml</p>
                </div>
                <i class="fas fa-chart-line text-3xl text-blue-500"></i>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Hydration Streak</p>
                    <p id="streak" class="text-2xl font-bold text-gray-900 dark:text-white">0 days</p>
                </div>
                <i class="fas fa-fire text-3xl text-orange-500"></i>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Goal Achievement</p>
                    <p id="goalRate" class="text-2xl font-bold text-gray-900 dark:text-white">0%</p>
                </div>
                <i class="fas fa-trophy text-3xl text-yellow-500"></i>
            </div>
        </div>
    </div>

    <!-- History -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
        <div class="p-4 border-b dark:border-gray-700">
            <h3 class="font-semibold dark:text-white">Recent Logs</h3>
        </div>
        <div id="logsContainer" class="divide-y dark:divide-gray-700"></div>
    </div>
</div>

<!-- Custom Amount Modal -->
<div id="customModal" class="modal hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white dark:bg-gray-800 rounded-lg max-w-md w-full p-6">
        <h2 class="text-2xl font-bold mb-4 dark:text-white">Add Custom Amount</h2>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1 dark:text-gray-300">Amount (ml)*</label>
                <input type="number" id="customAmount" min="1" class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
            </div>
            <div class="flex justify-end gap-2">
                <button onclick="closeCustomModal()" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg dark:text-gray-400 dark:hover:bg-gray-700">Cancel</button>
                <button onclick="addCustomWater()" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark">Add Water</button>
            </div>
        </div>
    </div>
</div>

<script>
let todayIntake = 0;
let goal = 2000;
let logs = [];

async function loadWaterData() {
    const response = await fetch('/api/water.php?action=today');
    const data = await response.json();
    if (data.success) {
        todayIntake = data.total_intake || 0;
        goal = data.daily_goal || 2000;
        updateProgress();
    }
    
    loadLogs();
    loadStats();
}

async function loadLogs() {
    const response = await fetch('/api/water.php?action=logs&limit=10');
    const data = await response.json();
    if (data.success) {
        logs = data.logs;
        renderLogs();
    }
}

async function loadStats() {
    const response = await fetch('/api/water.php?action=stats');
    const data = await response.json();
    if (data.success) {
        document.getElementById('weeklyAvg').textContent = Math.round(data.weekly_avg || 0) + ' ml';
        document.getElementById('streak').textContent = (data.streak || 0) + ' days';
        document.getElementById('goalRate').textContent = Math.round(data.goal_rate || 0) + '%';
    }
}

function updateProgress() {
    document.getElementById('currentIntake').textContent = todayIntake;
    document.getElementById('goalAmount').textContent = goal;
    
    const percentage = Math.min((todayIntake / goal) * 100, 100);
    const circumference = 552.92;
    const offset = circumference - (circumference * percentage / 100);
    document.getElementById('progressCircle').style.strokeDashoffset = offset;
    
    if (todayIntake >= goal) {
        showToast('🎉 Daily goal achieved! Great job!', 'success');
    }
}

function renderLogs() {
    const container = document.getElementById('logsContainer');
    container.innerHTML = logs.map(log => `
        <div class="p-4 flex justify-between items-center">
            <div>
                <p class="font-medium dark:text-white">${log.amount_ml} ml</p>
                <p class="text-sm text-gray-500">${new Date(log.logged_at).toLocaleString()}</p>
            </div>
            <button onclick="deleteLog(${log.id})" class="text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 px-3 py-1 rounded">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    `).join('') || '<div class="p-8 text-center text-gray-500">No logs yet today</div>';
}

async function addWater(amount) {
    const response = await fetch('/api/water.php?action=log', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({amount_ml: amount})
    });
    
    const result = await response.json();
    if (result.success) {
        todayIntake += amount;
        updateProgress();
        loadLogs();
        showToast(`Added ${amount}ml`, 'success');
    }
}

function openCustomModal() {
    document.getElementById('customModal').classList.remove('hidden');
    document.getElementById('customAmount').value = '';
    document.getElementById('customAmount').focus();
}

function closeCustomModal() {
    document.getElementById('customModal').classList.add('hidden');
}

async function addCustomWater() {
    const amount = parseInt(document.getElementById('customAmount').value);
    if (!amount || amount <= 0) return;
    
    await addWater(amount);
    closeCustomModal();
}

async function deleteLog(id) {
    if (!confirm('Delete this log?')) return;
    const response = await fetch(`/api/water.php?id=${id}`, {method: 'DELETE'});
    const result = await response.json();
    if (result.success) {
        loadWaterData();
        showToast('Log deleted', 'success');
    }
}

loadWaterData();
</script>

<?php include 'includes/footer.php'; ?>
