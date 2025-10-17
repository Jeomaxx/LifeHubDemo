<?php
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$auth = new Auth();
requireLogin();

$pageTitle = 'Mindfulness & Wellness';
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <i data-lucide="heart" class="text-primary"></i>
                Wellness & Mindfulness Hub
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2">Track meditation, breathing exercises, and sleep</p>
        </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="stat-card hover-lift">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Meditation Minutes</p>
                    <h3 class="text-3xl font-bold" id="totalMeditation">0</h3>
                </div>
                <i data-lucide="brain" class="text-purple-500 w-12 h-12"></i>
            </div>
        </div>
        <div class="stat-card hover-lift">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Avg Sleep Hours</p>
                    <h3 class="text-3xl font-bold" id="avgSleep">0</h3>
                </div>
                <i data-lucide="moon" class="text-blue-500 w-12 h-12"></i>
            </div>
        </div>
        <div class="stat-card hover-lift">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Sessions</p>
                    <h3 class="text-3xl font-bold" id="totalSessions">0</h3>
                </div>
                <i data-lucide="activity" class="text-green-500 w-12 h-12"></i>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
        <h2 class="text-xl font-bold mb-4">Recent Meditation Sessions</h2>
        <div id="sessionsList"></div>
    </div>
</div>

<script>
async function loadData() {
    const stats = await fetch('/api/wellness.php?type=stats').then(r => r.json());
    if (stats.success) {
        document.getElementById('totalMeditation').textContent = stats.data.total_meditation_mins || 0;
        document.getElementById('avgSleep').textContent = parseFloat(stats.data.avg_sleep_hours || 0).toFixed(1);
        document.getElementById('totalSessions').textContent = stats.data.meditation_sessions || 0;
    }
    
    const sessions = await fetch('/api/wellness.php?type=meditation').then(r => r.json());
    if (sessions.success) {
        document.getElementById('sessionsList').innerHTML = sessions.data.length > 0 
            ? sessions.data.slice(0,5).map(s => `<div class="p-3 border-b">${s.meditation_type || 'Meditation'} - ${s.duration_minutes} minutes on ${s.session_date}</div>`).join('') 
            : '<p class="text-gray-500">No meditation sessions yet. Start your mindfulness journey!</p>';
    }
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

document.addEventListener('DOMContentLoaded', loadData);
</script>

<?php include 'includes/footer.php'; ?>
