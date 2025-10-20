<?php
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$auth = new Auth();
requireLogin();

$db = Database::getInstance();
$userId = $auth->getUserId();

$pageTitle = 'Sleep Tracker';
include 'includes/header.php';

$sleepData = $db->fetchAll("SELECT * FROM sleep_logs WHERE user_id = ? ORDER BY sleep_date DESC LIMIT 30", [$userId]) ?: [];

$stats = [
    'avg_sleep' => $db->fetchColumn("SELECT AVG(sleep_duration_hours) FROM sleep_logs WHERE user_id = ? AND sleep_date >= CURRENT_DATE - INTERVAL '30 days'", [$userId]) ?? 0,
    'total_logs' => $db->fetchColumn("SELECT COUNT(*) FROM sleep_logs WHERE user_id = ?", [$userId]) ?? 0,
    'avg_quality' => $db->fetchColumn("SELECT AVG(sleep_quality_rating) FROM sleep_logs WHERE user_id = ? AND sleep_date >= CURRENT_DATE - INTERVAL '30 days'", [$userId]) ?? 0,
];
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <i class="fas fa-moon text-primary"></i>
                Sleep Tracker
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2">Monitor your sleep patterns and improve sleep quality</p>
        </div>
        <button id="addSleepBtn" class="btn-primary">
            <i class="fas fa-plus mr-2"></i>
            Log Sleep
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="stat-card hover-lift">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Avg Sleep Duration</p>
                    <h3 class="text-3xl font-bold text-gray-900 dark:text-white">
                        <?php echo number_format($stats['avg_sleep'], 1); ?>h
                    </h3>
                    <p class="text-xs text-gray-500 mt-1">Last 30 days</p>
                </div>
                <i class="fas fa-bed text-blue-500 text-4xl"></i>
            </div>
        </div>

        <div class="stat-card hover-lift">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Avg Sleep Quality</p>
                    <h3 class="text-3xl font-bold text-gray-900 dark:text-white">
                        <?php echo number_format($stats['avg_quality'], 1); ?>/5
                    </h3>
                    <p class="text-xs text-gray-500 mt-1">Last 30 days</p>
                </div>
                <i class="fas fa-star text-yellow-500 text-4xl"></i>
            </div>
        </div>

        <div class="stat-card hover-lift">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Logs</p>
                    <h3 class="text-3xl font-bold text-gray-900 dark:text-white">
                        <?php echo $stats['total_logs']; ?>
                    </h3>
                    <p class="text-xs text-gray-500 mt-1">All time</p>
                </div>
                <i class="fas fa-chart-line text-green-500 text-4xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-xl font-bold mb-4 text-gray-900 dark:text-white">Sleep Chart (Last 30 Days)</h2>
        <canvas id="sleepChart" height="80"></canvas>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
        <h2 class="text-xl font-bold mb-4 text-gray-900 dark:text-white">Recent Sleep Logs</h2>
        
        <?php if (empty($sleepData)): ?>
            <div class="text-center py-8">
                <i class="fas fa-moon text-gray-400 text-5xl mb-4"></i>
                <p class="text-gray-500">No sleep logs yet. Start tracking your sleep!</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Date</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Sleep Time</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Wake Time</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Duration</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Quality</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sleepData as $log): ?>
                        <tr class="border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                <?php echo date('M d, Y', strtotime($log['sleep_date'])); ?>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                <?php echo $log['sleep_start_time'] ? date('g:i A', strtotime($log['sleep_start_time'])) : 'N/A'; ?>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                <?php echo $log['sleep_end_time'] ? date('g:i A', strtotime($log['sleep_end_time'])) : 'N/A'; ?>
                            </td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">
                                <?php echo number_format($log['sleep_duration_hours'], 1); ?>h
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <div class="flex items-center gap-1">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star text-xs <?php echo $i <= $log['sleep_quality_rating'] ? 'text-yellow-500' : 'text-gray-300'; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <button onclick="deleteSleep(<?php echo $log['id']; ?>)" class="text-red-600 hover:text-red-800">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Sleep Modal -->
<div id="sleepModal" class="modal hidden">
    <div class="modal-content">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white">Log Sleep</h3>
            <button class="modal-close text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <form id="sleepForm">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Sleep Date</label>
                <input type="date" id="sleepDate" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white" value="<?php echo date('Y-m-d'); ?>">
            </div>
            
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Sleep Time</label>
                    <input type="time" id="sleepStartTime" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Wake Time</label>
                    <input type="time" id="sleepEndTime" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                </div>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Sleep Quality (1-5)</label>
                <div class="flex gap-2">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <button type="button" onclick="setSleepQuality(<?php echo $i; ?>)" class="quality-btn w-12 h-12 border-2 border-gray-300 rounded-lg hover:border-primary" data-quality="<?php echo $i; ?>">
                            <i class="fas fa-star text-gray-300"></i>
                        </button>
                    <?php endfor; ?>
                </div>
                <input type="hidden" id="sleepQuality" value="3">
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Notes (Optional)</label>
                <textarea id="sleepNotes" rows="3" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white" placeholder="How did you sleep? Any dreams?"></textarea>
            </div>
            
            <div class="flex gap-2">
                <button type="submit" class="btn-primary flex-1">Save Sleep Log</button>
                <button type="button" class="modal-close btn-secondary">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const sleepData = <?php echo json_encode($sleepData); ?>;

document.getElementById('addSleepBtn').addEventListener('click', () => {
    document.getElementById('sleepModal').classList.remove('hidden');
});

document.querySelectorAll('.modal-close').forEach(btn => {
    btn.addEventListener('click', () => {
        document.getElementById('sleepModal').classList.add('hidden');
    });
});

function setSleepQuality(quality) {
    document.getElementById('sleepQuality').value = quality;
    document.querySelectorAll('.quality-btn').forEach(btn => {
        const btnQuality = parseInt(btn.dataset.quality);
        const star = btn.querySelector('i');
        if (btnQuality <= quality) {
            star.classList.remove('text-gray-300');
            star.classList.add('text-yellow-500');
            btn.classList.add('border-yellow-500');
        } else {
            star.classList.add('text-gray-300');
            star.classList.remove('text-yellow-500');
            btn.classList.remove('border-yellow-500');
        }
    });
}

document.getElementById('sleepForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData();
    formData.append('action', 'log_sleep');
    formData.append('sleep_date', document.getElementById('sleepDate').value);
    formData.append('sleep_start_time', document.getElementById('sleepStartTime').value);
    formData.append('sleep_end_time', document.getElementById('sleepEndTime').value);
    formData.append('sleep_quality_rating', document.getElementById('sleepQuality').value);
    formData.append('notes', document.getElementById('sleepNotes').value);
    
    const response = await fetch('/api/mindfulness_sleep.php', {
        method: 'POST',
        body: formData
    });
    
    const result = await response.json();
    
    if (result.success) {
        showNotification('Sleep log saved successfully!', 'success');
        setTimeout(() => location.reload(), 1000);
    } else {
        showNotification('Failed to save sleep log', 'error');
    }
});

async function deleteSleep(id) {
    if (!confirm('Are you sure you want to delete this sleep log?')) return;
    
    const formData = new FormData();
    formData.append('action', 'delete_sleep');
    formData.append('id', id);
    
    const response = await fetch('/api/mindfulness_sleep.php', {
        method: 'POST',
        body: formData
    });
    
    const result = await response.json();
    
    if (result.success) {
        showNotification('Sleep log deleted', 'success');
        setTimeout(() => location.reload(), 1000);
    }
}

const ctx = document.getElementById('sleepChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: sleepData.slice(0, 30).reverse().map(s => new Date(s.sleep_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })),
        datasets: [{
            label: 'Sleep Duration (hours)',
            data: sleepData.slice(0, 30).reverse().map(s => parseFloat(s.sleep_duration_hours)),
            borderColor: 'rgb(59, 130, 246)',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                max: 12,
                ticks: {
                    callback: function(value) {
                        return value + 'h';
                    }
                }
            }
        }
    }
});

setSleepQuality(3);
</script>

<?php include 'includes/footer.php'; ?>
