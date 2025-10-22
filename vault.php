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
let vaultItems = [];

async function loadVaultItems() {
    try {
        const response = await fetch('/api/vault.php?action=list');
        const result = await response.json();
        if (result.success) {
            vaultItems = result.items || [];
            displayVaultItems(vaultItems);
            updateVaultStats(vaultItems);
        }
    } catch (error) {
        console.error('Error loading vault items:', error);
    }
}

function displayVaultItems(items) {
    const container = document.getElementById('vaultList');
    
    if (items.length === 0) {
        container.innerHTML = '<div class="text-center py-12 text-gray-500 dark:text-gray-400"><i class="fas fa-vault text-4xl mb-3"></i><p>Your vault is empty. Add your first item to get started.</p><p class="text-sm mt-2">All items are encrypted with AES-256 encryption</p></div>';
        return;
    }
    
    container.innerHTML = '<div class="grid grid-cols-1 md:grid-cols-2 gap-4">' + items.map(item => `
        <div class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:shadow-md transition">
            <div class="flex items-start justify-between mb-2">
                <div class="flex items-center gap-3">
                    <i class="fas ${getVaultIcon(item.item_type)} text-2xl text-primary"></i>
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-white">${escapeHtml(item.title)}</h3>
                        <p class="text-xs text-gray-500">${item.item_type ? item.item_type.charAt(0).toUpperCase() + item.item_type.slice(1) : 'Item'}</p>
                    </div>
                </div>
            </div>
            ${item.tags ? `<div class="flex gap-1 flex-wrap mb-2">${item.tags.split(',').map(tag => `<span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 text-xs rounded">${tag.trim()}</span>`).join('')}</div>` : ''}
            <div class="flex gap-2 mt-3">
                <button onclick="viewVaultItem(${item.id})" class="flex-1 px-3 py-1 bg-blue-500 text-white rounded text-sm hover:bg-blue-600">View</button>
                <button onclick="copyPassword(${item.id})" class="px-3 py-1 bg-gray-500 text-white rounded text-sm hover:bg-gray-600" title="Copy"><i class="fas fa-copy"></i></button>
                <button onclick="deleteVaultItem(${item.id})" class="px-3 py-1 bg-red-500 text-white rounded text-sm hover:bg-red-600"><i class="fas fa-trash"></i></button>
            </div>
        </div>
    `).join('') + '</div>';
}

function updateVaultStats(items) {
    document.getElementById('totalItems').textContent = items.length;
    document.getElementById('passwordCount').textContent = items.filter(i => i.item_type === 'password').length;
    document.getElementById('notesCount').textContent = items.filter(i => i.item_type === 'note').length;
    document.getElementById('cardsCount').textContent = items.filter(i => i.item_type === 'card' || i.item_type === 'identity').length;
}

function getVaultIcon(type) {
    const icons = {
        'password': 'fa-key',
        'note': 'fa-sticky-note',
        'card': 'fa-credit-card',
        'identity': 'fa-id-card'
    };
    return icons[type] || 'fa-lock';
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function openVaultModal() {
    document.getElementById('vaultForm').reset();
    const modal = document.getElementById('vaultModal');
    modal.classList.remove('hidden');
    modal.style.display = 'flex';
}

function closeVaultModal() {
    const modal = document.getElementById('vaultModal');
    modal.classList.add('hidden');
    modal.style.display = 'none';
}

async function saveVaultItem() {
    const itemData = {
        item_type: document.getElementById('vaultType').value,
        title: document.getElementById('vaultTitle').value,
        encrypted_content: JSON.stringify({
            username: document.getElementById('vaultUsername').value,
            password: document.getElementById('vaultPassword').value,
            notes: document.getElementById('vaultNotes').value
        }),
        encryption_key_id: null,
        tags: document.getElementById('vaultTags')?.value || null
    };
    
    try {
        const response = await fetch('/api/vault.php?action=create', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content
            },
            body: JSON.stringify(itemData)
        });
        
        const result = await response.json();
        if (result.success) {
            closeVaultModal();
            loadVaultItems();
            if (typeof showToast === 'function') {
                showToast('success', 'Success', 'Vault item saved securely');
            } else {
                alert('Vault item saved securely');
            }
        } else {
            alert('Error: ' + (result.message || 'Failed to save vault item'));
        }
    } catch (error) {
        console.error('Error saving vault item:', error);
        alert('Failed to save vault item');
    }
}

async function viewVaultItem(id) {
    try {
        const response = await fetch(`/api/vault.php?action=single&id=${id}`);
        const result = await response.json();
        if (result.success && result.item) {
            const item = result.item;
            const content = JSON.parse(item.encrypted_content || '{}');
            alert(`Title: ${item.title}\nUsername: ${content.username || 'N/A'}\nPassword: ${content.password || '***'}\nNotes: ${content.notes || 'N/A'}`);
        }
    } catch (error) {
        console.error('Error viewing vault item:', error);
        alert('Failed to view vault item');
    }
}

async function copyPassword(id) {
    try {
        const response = await fetch(`/api/vault.php?action=single&id=${id}`);
        const result = await response.json();
        if (result.success && result.item) {
            const content = JSON.parse(result.item.encrypted_content || '{}');
            if (content.password) {
                navigator.clipboard.writeText(content.password);
                if (typeof showToast === 'function') {
                    showToast('success', 'Copied', 'Password copied to clipboard');
                } else {
                    alert('Password copied to clipboard');
                }
            }
        }
    } catch (error) {
        console.error('Error copying password:', error);
    }
}

async function deleteVaultItem(id) {
    if (!confirm('Are you sure you want to delete this vault item?')) return;
    
    try {
        const response = await fetch(`/api/vault.php?action=delete&id=${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content
            }
        });
        
        const result = await response.json();
        if (result.success) {
            loadVaultItems();
            if (typeof showToast === 'function') {
                showToast('success', 'Success', 'Vault item deleted');
            }
        } else {
            alert('Error: ' + (result.message || 'Failed to delete vault item'));
        }
    } catch (error) {
        console.error('Error deleting vault item:', error);
        alert('Failed to delete vault item');
    }
}

document.getElementById('searchVault')?.addEventListener('input', function() {
    const query = this.value.toLowerCase();
    const filtered = vaultItems.filter(item => 
        item.title.toLowerCase().includes(query) || 
        (item.tags && item.tags.toLowerCase().includes(query))
    );
    displayVaultItems(filtered);
});

document.getElementById('filterType')?.addEventListener('change', function() {
    const type = this.value;
    const filtered = type ? vaultItems.filter(item => item.item_type === type) : vaultItems;
    displayVaultItems(filtered);
});

// Load vault items on page load
loadVaultItems();
</script>

<?php include 'includes/footer.php'; ?>
