<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$pageTitle = 'Gift Management';
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <i class="fas fa-gift text-primary"></i>
                <?php echo t('Gift Management'); ?>
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1"><?php echo t('Track gifts for special occasions'); ?></p>
        </div>
        <button onclick="openGiftModal()" class="btn bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-lg flex items-center gap-2">
            <i class="fas fa-plus"></i>
            <span><?php echo t('Add Gift'); ?></span>
        </button>
    </div>

    <!-- Filter -->
    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 mb-6">
        <div class="flex flex-wrap gap-4">
            <input type="text" id="searchGifts" placeholder="Search gifts or recipients..." 
                class="flex-1 px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
            <select id="filterStatus" class="px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="purchased">Purchased</option>
            </select>
        </div>
    </div>

    <!-- Gifts Grid -->
    <div id="giftsContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4"></div>
</div>

<!-- Gift Modal -->
<div id="giftModal" class="modal hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white dark:bg-gray-800 rounded-lg max-w-2xl w-full p-6">
        <h2 class="text-2xl font-bold mb-4 dark:text-white">Add Gift</h2>
        <form id="giftForm" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1 dark:text-gray-300">Gift Name*</label>
                    <input type="text" name="gift_name" required class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1 dark:text-gray-300">Recipient*</label>
                    <input type="text" name="recipient_name" required class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1 dark:text-gray-300">Occasion</label>
                    <input type="text" name="occasion" class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1 dark:text-gray-300">Price</label>
                    <input type="number" step="0.01" name="price" class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1 dark:text-gray-300">Provider Link</label>
                <input type="url" name="provider_link" class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1 dark:text-gray-300">Notes</label>
                <textarea name="notes" rows="3" class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600"></textarea>
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" name="purchased" id="purchased" class="rounded">
                <label for="purchased" class="text-sm dark:text-gray-300">Already Purchased</label>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeGiftModal()" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg dark:text-gray-400 dark:hover:bg-gray-700">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark">Save Gift</button>
            </div>
        </form>
    </div>
</div>

<script>
let gifts = [];

async function loadGifts() {
    const response = await fetch('/api/gifts.php?action=list');
    const data = await response.json();
    if (data.success) {
        gifts = data.gifts;
        renderGifts();
    }
}

function renderGifts() {
    const container = document.getElementById('giftsContainer');
    const search = document.getElementById('searchGifts').value.toLowerCase();
    const status = document.getElementById('filterStatus').value;
    
    const filtered = gifts.filter(gift => {
        const matchSearch = !search || gift.gift_name.toLowerCase().includes(search) || gift.recipient_name.toLowerCase().includes(search);
        const matchStatus = !status || (status === 'purchased' && gift.purchased) || (status === 'pending' && !gift.purchased);
        return matchSearch && matchStatus;
    });
    
    container.innerHTML = filtered.map(gift => `
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm">
            <div class="flex justify-between items-start mb-2">
                <h3 class="font-semibold dark:text-white">${gift.gift_name}</h3>
                ${gift.purchased ? '<span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded">✓ Purchased</span>' : '<span class="text-xs bg-yellow-100 text-yellow-800 px-2 py-1 rounded">Pending</span>'}
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400">For: ${gift.recipient_name}</p>
            ${gift.occasion ? `<p class="text-sm text-gray-500 dark:text-gray-500">Occasion: ${gift.occasion}</p>` : ''}
            ${gift.price ? `<p class="text-sm font-semibold text-primary mt-2">$${parseFloat(gift.price).toFixed(2)}</p>` : ''}
            ${gift.provider_link ? `<a href="${gift.provider_link}" target="_blank" class="text-sm text-blue-500 hover:underline block mt-1">View Product</a>` : ''}
            <div class="flex gap-2 mt-4">
                <button onclick="togglePurchased(${gift.id})" class="text-sm px-3 py-1 bg-gray-100 dark:bg-gray-700 rounded hover:bg-gray-200 dark:hover:bg-gray-600">
                    ${gift.purchased ? 'Mark Pending' : 'Mark Purchased'}
                </button>
                <button onclick="deleteGift(${gift.id})" class="text-sm px-3 py-1 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded">Delete</button>
            </div>
        </div>
    `).join('');
}

function openGiftModal() {
    document.getElementById('giftModal').classList.remove('hidden');
    document.getElementById('giftForm').reset();
}

function closeGiftModal() {
    document.getElementById('giftModal').classList.add('hidden');
}

document.getElementById('giftForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const data = {
        gift_name: formData.get('gift_name'),
        recipient_name: formData.get('recipient_name'),
        occasion: formData.get('occasion'),
        price: formData.get('price'),
        provider_link: formData.get('provider_link'),
        notes: formData.get('notes'),
        purchased: formData.get('purchased') === 'on'
    };
    
    const response = await fetch('/api/gifts.php?action=create', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    });
    
    const result = await response.json();
    if (result.success) {
        closeGiftModal();
        loadGifts();
        showToast('Gift added successfully', 'success');
    }
});

async function togglePurchased(id) {
    const response = await fetch('/api/gifts.php?action=toggle_purchased', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({id})
    });
    const result = await response.json();
    if (result.success) {
        loadGifts();
    }
}

async function deleteGift(id) {
    if (!confirm('Delete this gift?')) return;
    const response = await fetch(`/api/gifts.php?id=${id}`, {method: 'DELETE'});
    const result = await response.json();
    if (result.success) {
        loadGifts();
        showToast('Gift deleted', 'success');
    }
}

document.getElementById('searchGifts').addEventListener('input', renderGifts);
document.getElementById('filterStatus').addEventListener('change', renderGifts);

loadGifts();
</script>

<?php include 'includes/footer.php'; ?>
