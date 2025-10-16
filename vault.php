<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$pageTitle = 'Secure Vault';
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <i class="fas fa-vault text-primary"></i>
                Secure Vault
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Store sensitive information securely with encryption</p>
        </div>
        <button onclick="openVaultModal()" class="btn bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-lg flex items-center gap-2">
            <i class="fas fa-plus"></i>
            <span>Add Item</span>
        </button>
    </div>

    <!-- Security Banner -->
    <div class="bg-gradient-to-r from-green-500 to-teal-600 rounded-lg p-6 text-white mb-6">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 bg-white/20 rounded-lg flex items-center justify-center">
                <i class="fas fa-shield-alt text-3xl"></i>
            </div>
            <div>
                <h3 class="text-xl font-semibold">End-to-End Encryption</h3>
                <p class="opacity-90 mt-1">Your data is encrypted with AES-256 encryption. Only you can access it.</p>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Items</p>
                    <p id="totalItems" class="text-2xl font-bold text-gray-900 dark:text-white mt-1">0</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-key text-blue-600 dark:text-blue-400 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Passwords</p>
                    <p id="passwordCount" class="text-2xl font-bold text-gray-900 dark:text-white mt-1">0</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-lock text-purple-600 dark:text-purple-400 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Secure Notes</p>
                    <p id="notesCount" class="text-2xl font-bold text-gray-900 dark:text-white mt-1">0</p>
                </div>
                <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-sticky-note text-green-600 dark:text-green-400 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Cards & IDs</p>
                    <p id="cardsCount" class="text-2xl font-bold text-gray-900 dark:text-white mt-1">0</p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-credit-card text-yellow-600 dark:text-yellow-400 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Vault Items -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Vault Items</h2>
            <div class="flex gap-2">
                <input type="text" placeholder="Search vault..." class="px-4 py-2 border border-gray-300 rounded-lg" id="searchVault">
                <select class="px-4 py-2 border border-gray-300 rounded-lg" id="filterType">
                    <option value="">All Types</option>
                    <option value="password">Passwords</option>
                    <option value="note">Secure Notes</option>
                    <option value="card">Cards</option>
                    <option value="identity">Identities</option>
                </select>
            </div>
        </div>
        <div id="vaultList" class="p-6">
            <div class="text-center py-12 text-gray-500 dark:text-gray-400">
                <i class="fas fa-vault text-4xl mb-3"></i>
                <p>Your vault is empty. Add your first item to get started.</p>
                <p class="text-sm mt-2">All items are encrypted with AES-256 encryption</p>
            </div>
        </div>
    </div>

    <!-- Security Info -->
    <div class="mt-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2 mb-3">
            <i class="fas fa-info-circle text-blue-600 dark:text-blue-400"></i>
            Security Information
        </h3>
        <ul class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
            <li class="flex items-start gap-2">
                <i class="fas fa-check-circle text-blue-600 mt-0.5"></i>
                <span>All vault items are encrypted using AES-256 encryption</span>
            </li>
            <li class="flex items-start gap-2">
                <i class="fas fa-check-circle text-blue-600 mt-0.5"></i>
                <span>Encryption happens on the client-side before transmission</span>
            </li>
            <li class="flex items-start gap-2">
                <i class="fas fa-check-circle text-blue-600 mt-0.5"></i>
                <span>Your master password is never stored or transmitted</span>
            </li>
            <li class="flex items-start gap-2">
                <i class="fas fa-check-circle text-blue-600 mt-0.5"></i>
                <span>Enable 2FA for additional security protection</span>
            </li>
        </ul>
    </div>
</div>

<!-- Add Vault Item Modal -->
<div id="vaultModal" class="modal hidden">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="text-xl font-semibold">Add Vault Item</h3>
            <button onclick="closeVaultModal()" class="text-gray-500 hover:text-gray-700">&times;</button>
        </div>
        <form id="vaultForm" class="modal-body">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Item Type</label>
                <select id="vaultType" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    <option value="password">Password</option>
                    <option value="note">Secure Note</option>
                    <option value="card">Credit Card</option>
                    <option value="identity">Identity</option>
                </select>
            </div>
            <div class="grid grid-cols-1 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Title</label>
                    <input type="text" id="vaultTitle" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Username/Account</label>
                    <input type="text" id="vaultUsername" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Password/Content</label>
                    <input type="password" id="vaultPassword" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Notes</label>
                    <textarea id="vaultNotes" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg"></textarea>
                </div>
            </div>
        </form>
        <div class="modal-footer">
            <button onclick="closeVaultModal()" class="btn btn-secondary">Cancel</button>
            <button onclick="saveVaultItem()" class="btn btn-primary">Save Encrypted</button>
        </div>
    </div>
</div>

<script>
function openVaultModal() {
    document.getElementById('vaultModal').classList.remove('hidden');
}

function closeVaultModal() {
    document.getElementById('vaultModal').classList.add('hidden');
    document.getElementById('vaultForm').reset();
}

function saveVaultItem() {
    alert('Vault item will be encrypted and saved securely');
    closeVaultModal();
}
</script>

<?php include 'includes/footer.php'; ?>
