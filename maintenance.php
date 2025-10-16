<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$pageTitle = 'Maintenance Logs';
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <i class="fas fa-tools text-primary"></i>
                Maintenance Logs
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Track maintenance and repair history</p>
        </div>
        <button onclick="openMaintenanceModal()" class="btn bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-lg flex items-center gap-2">
            <i class="fas fa-plus"></i>
            <span>Log Maintenance</span>
        </button>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Maintenance</p>
                    <p id="totalMaintenance" class="text-2xl font-bold text-gray-900 dark:text-white mt-1">0</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-wrench text-blue-600 dark:text-blue-400 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Pending Tasks</p>
                    <p id="pendingTasks" class="text-2xl font-bold text-gray-900 dark:text-white mt-1">0</p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clock text-yellow-600 dark:text-yellow-400 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Cost</p>
                    <p id="totalCost" class="text-2xl font-bold text-gray-900 dark:text-white mt-1">$0</p>
                </div>
                <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-dollar-sign text-green-600 dark:text-green-400 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Maintenance List -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Maintenance History</h2>
        </div>
        <div id="maintenanceList" class="p-6">
            <div class="text-center py-12 text-gray-500 dark:text-gray-400">
                <i class="fas fa-tools text-4xl mb-3"></i>
                <p>No maintenance logs yet. Click "Log Maintenance" to add one.</p>
            </div>
        </div>
    </div>
</div>

<!-- Add Maintenance Modal -->
<div id="maintenanceModal" class="modal hidden">
    <div class="modal-content bg-white dark:bg-gray-800 rounded-lg w-full max-w-md mx-4">
        <div class="modal-header p-6 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-xl font-semibold">Log Maintenance</h3>
            <button onclick="closeMaintenanceModal()" class="text-gray-500 hover:text-gray-700">&times;</button>
        </div>
        <form id="maintenanceForm" class="modal-body p-6">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Asset</label>
                <input type="text" id="maintenanceAsset" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Type</label>
                <select id="maintenanceType" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    <option value="repair">Repair</option>
                    <option value="maintenance">Maintenance</option>
                    <option value="inspection">Inspection</option>
                    <option value="replacement">Replacement</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date</label>
                <input type="date" id="maintenanceDate" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Cost</label>
                <input type="number" id="maintenanceCost" step="0.01" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description</label>
                <textarea id="maintenanceDescription" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg"></textarea>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="flex-1 bg-primary hover:bg-blue-600 text-white px-6 py-2 rounded-lg">Save</button>
                <button type="button" onclick="closeMaintenanceModal()" class="btn btn-secondary">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openMaintenanceModal() {
    document.getElementById('maintenanceModal').classList.remove('hidden');
}

function closeMaintenanceModal() {
    document.getElementById('maintenanceModal').classList.add('hidden');
    document.getElementById('maintenanceForm').reset();
}

document.getElementById('maintenanceForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const data = {
        asset_name: document.getElementById('maintenanceAsset').value,
        maintenance_type: document.getElementById('maintenanceType').value,
        maintenance_date: document.getElementById('maintenanceDate').value,
        cost: parseFloat(document.getElementById('maintenanceCost').value) || 0,
        description: document.getElementById('maintenanceDescription').value
    };
    
    const response = await fetch('/api/home_assets.php?action=create_maintenance', {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content},
        body: JSON.stringify(data)
    });
    
    const result = await response.json();
    if (result.success) {
        alert('Maintenance logged successfully');
        window.location.reload();
    } else {
        alert('Error: ' + (result.message || 'Failed to log maintenance'));
    }
});
</script>

<?php include 'includes/footer.php'; ?>
