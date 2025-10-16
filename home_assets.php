<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$pageTitle = 'Home & Assets';
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <i class="fas fa-home text-primary"></i>
                Home & Assets
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Manage your home assets and track maintenance</p>
        </div>
        <button onclick="openAssetModal()" class="btn bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-lg flex items-center gap-2">
            <i class="fas fa-plus"></i>
            <span>Add Asset</span>
        </button>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Assets</p>
                    <p id="totalAssets" class="text-2xl font-bold text-gray-900 dark:text-white mt-1">0</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-box text-blue-600 dark:text-blue-400 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Value</p>
                    <p id="totalValue" class="text-2xl font-bold text-gray-900 dark:text-white mt-1">$0</p>
                </div>
                <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-dollar-sign text-green-600 dark:text-green-400 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Pending Maintenance</p>
                    <p id="pendingMaintenance" class="text-2xl font-bold text-gray-900 dark:text-white mt-1">0</p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-tools text-yellow-600 dark:text-yellow-400 text-xl"></i>
                </div>
            </div>
        </div>

        <a href="/maintenance.php" class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Maintenance Logs</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">View →</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-wrench text-purple-600 dark:text-purple-400 text-xl"></i>
                </div>
            </div>
        </a>
    </div>

    <!-- Assets List -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Your Assets</h2>
        </div>
        <div id="assetsList" class="p-6">
            <div class="text-center py-12 text-gray-500 dark:text-gray-400">
                <i class="fas fa-home text-4xl mb-3"></i>
                <p>No assets added yet. Click "Add Asset" to get started.</p>
            </div>
        </div>
    </div>
</div>

<!-- Add Asset Modal -->
<div id="assetModal" class="modal hidden">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="text-xl font-semibold">Add New Asset</h3>
            <button onclick="closeAssetModal()" class="text-gray-500 hover:text-gray-700">&times;</button>
        </div>
        <form id="assetForm" class="modal-body">
            <input type="hidden" id="assetId" name="id">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Asset Name</label>
                    <input type="text" id="assetName" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Category</label>
                    <select id="assetCategory" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        <option value="Electronics">Electronics</option>
                        <option value="Appliances">Appliances</option>
                        <option value="Furniture">Furniture</option>
                        <option value="Vehicle">Vehicle</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Purchase Date</label>
                    <input type="date" id="purchaseDate" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Purchase Value</label>
                    <input type="number" id="purchaseValue" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Warranty Expiry</label>
                    <input type="date" id="warrantyExpiry" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                    <select id="assetStatus" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        <option value="Active">Active</option>
                        <option value="Under Maintenance">Under Maintenance</option>
                        <option value="Retired">Retired</option>
                    </select>
                </div>
            </div>
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Notes</label>
                <textarea id="assetNotes" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg"></textarea>
            </div>
        </form>
        <div class="modal-footer">
            <button onclick="closeAssetModal()" class="btn btn-secondary">Cancel</button>
            <button onclick="saveAsset()" class="btn btn-primary">Save Asset</button>
        </div>
    </div>
</div>

<script>
function openAssetModal() {
    const modal = document.getElementById('assetModal');
    modal.classList.remove('hidden');
    modal.style.display = 'flex';
}

function closeAssetModal() {
    const modal = document.getElementById('assetModal');
    modal.classList.add('hidden');
    modal.style.display = 'none';
    document.getElementById('assetForm').reset();
}

async function saveAsset() {
    const data = {
        asset_name: document.getElementById('assetName').value,
        category: document.getElementById('assetCategory').value,
        purchase_date: document.getElementById('purchaseDate').value,
        purchase_price: parseFloat(document.getElementById('purchaseValue').value) || null,
        warranty_expiry: document.getElementById('warrantyExpiry').value || null,
        status: document.getElementById('assetStatus').value,
        notes: document.getElementById('assetNotes').value
    };
    
    try {
        const response = await fetch('/api/home_assets.php?action=create&type=assets', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        if (result.success) {
            alert('Asset saved successfully!');
            window.location.reload();
        } else {
            alert('Error: ' + (result.message || 'Failed to save asset'));
        }
    } catch (error) {
        console.error('Error saving asset:', error);
        alert('Failed to save asset. Please try again.');
    }
}
</script>

<?php include 'includes/footer.php'; ?>
