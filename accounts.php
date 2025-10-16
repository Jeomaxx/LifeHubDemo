<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

// Get all accounts
$accounts = $db->fetchAll("SELECT * FROM financial_accounts WHERE user_id = ? ORDER BY account_type, created_at DESC", [$userId]);

// Calculate totals by type
$totals = $db->fetchAll("
    SELECT 
        account_type,
        SUM(current_balance) as total_balance,
        COUNT(*) as account_count
    FROM financial_accounts 
    WHERE user_id = ? AND is_active = TRUE
    GROUP BY account_type
", [$userId]);

$totalsByType = [];
foreach ($totals as $total) {
    $totalsByType[$total['account_type']] = $total;
}

// Calculate overall totals
$overallTotal = $db->fetchOne("
    SELECT 
        COALESCE(SUM(CASE WHEN account_type IN ('checking', 'savings', 'cash', 'investment') THEN current_balance ELSE 0 END), 0) as assets,
        COALESCE(SUM(CASE WHEN account_type = 'credit_card' THEN current_balance ELSE 0 END), 0) as liabilities
    FROM financial_accounts 
    WHERE user_id = ? AND is_active = TRUE
", [$userId]);

$pageTitle = 'Financial Accounts';
include 'includes/header.php';
?>

<!-- Page Header -->
<div class="mb-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-university text-primary"></i>
                Financial Accounts
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Manage your bank accounts, credit cards, and financial assets</p>
        </div>
        <button onclick="openModal('accountModal')" class="bg-primary hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors">
            <i class="fas fa-plus"></i>
            <span>Add Account</span>
        </button>
    </div>
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Total Assets</p>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                    <?php echo formatCurrency($overallTotal['assets'] ?? 0); ?>
                </h3>
            </div>
            <div class="bg-green-100 dark:bg-green-900/30 p-3 rounded-lg">
                <i class="fas fa-arrow-up text-green-600 dark:text-green-400 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Total Liabilities</p>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                    <?php echo formatCurrency($overallTotal['liabilities'] ?? 0); ?>
                </h3>
            </div>
            <div class="bg-red-100 dark:bg-red-900/30 p-3 rounded-lg">
                <i class="fas fa-arrow-down text-red-600 dark:text-red-400 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Net Worth</p>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                    <?php echo formatCurrency(($overallTotal['assets'] ?? 0) - ($overallTotal['liabilities'] ?? 0)); ?>
                </h3>
            </div>
            <div class="bg-blue-100 dark:bg-blue-900/30 p-3 rounded-lg">
                <i class="fas fa-balance-scale text-blue-600 dark:text-blue-400 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Accounts by Type -->
<?php
$accountTypes = [
    'checking' => ['icon' => 'fa-money-check', 'color' => 'blue', 'label' => 'Checking Accounts'],
    'savings' => ['icon' => 'fa-piggy-bank', 'color' => 'green', 'label' => 'Savings Accounts'],
    'credit_card' => ['icon' => 'fa-credit-card', 'color' => 'red', 'label' => 'Credit Cards'],
    'investment' => ['icon' => 'fa-chart-line', 'color' => 'purple', 'label' => 'Investment Accounts'],
    'cash' => ['icon' => 'fa-wallet', 'color' => 'yellow', 'label' => 'Cash'],
    'other' => ['icon' => 'fa-ellipsis-h', 'color' => 'gray', 'label' => 'Other Accounts']
];

foreach ($accountTypes as $type => $config):
    $typeAccounts = array_filter($accounts, fn($acc) => $acc['account_type'] === $type && $acc['is_active']);
    if (empty($typeAccounts)) continue;
?>
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 mb-4">
    <div class="p-4 border-b border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <i class="fas <?php echo $config['icon']; ?> text-<?php echo $config['color']; ?>-600"></i>
                <?php echo $config['label']; ?>
            </h3>
            <span class="text-sm text-gray-600 dark:text-gray-400">
                <?php echo count($typeAccounts); ?> account(s)
            </span>
        </div>
    </div>

    <div class="p-4">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <?php foreach ($typeAccounts as $account): ?>
            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 hover:shadow-md transition-shadow">
                <div class="flex justify-between items-start mb-3">
                    <div class="flex-1">
                        <h4 class="font-semibold text-gray-900 dark:text-white"><?php echo sanitize($account['account_name']); ?></h4>
                        <?php if ($account['bank_name']): ?>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            <?php echo sanitize($account['bank_name']); ?>
                            <?php if ($account['account_number_last4']): ?>
                            <span class="ml-2">•••• <?php echo sanitize($account['account_number_last4']); ?></span>
                            <?php endif; ?>
                        </p>
                        <?php endif; ?>
                    </div>
                    <div class="flex gap-2">
                        <button onclick="editAccount(<?php echo $account['id']; ?>)" class="text-blue-600 hover:text-blue-700 dark:text-blue-400">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="deleteAccount(<?php echo $account['id']; ?>)" class="text-red-600 hover:text-red-700 dark:text-red-400">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>

                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Balance</span>
                    <span class="text-lg font-bold <?php echo ($account['current_balance'] >= 0) ? 'text-green-600' : 'text-red-600'; ?>">
                        <?php echo formatCurrency($account['current_balance']); ?>
                    </span>
                </div>

                <?php if ($account['credit_limit']): ?>
                <div class="mt-2 pt-2 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-600 dark:text-gray-400">Credit Limit</span>
                        <span class="text-gray-900 dark:text-white"><?php echo formatCurrency($account['credit_limit']); ?></span>
                    </div>
                    <div class="flex justify-between items-center text-sm mt-1">
                        <span class="text-gray-600 dark:text-gray-400">Available</span>
                        <span class="text-gray-900 dark:text-white"><?php echo formatCurrency($account['credit_limit'] - abs($account['current_balance'])); ?></span>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($account['notes']): ?>
                <p class="mt-3 text-sm text-gray-600 dark:text-gray-400 border-t border-gray-200 dark:border-gray-700 pt-2">
                    <?php echo sanitize($account['notes']); ?>
                </p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endforeach; ?>

<?php if (empty($accounts)): ?>
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
    <i class="fas fa-university text-gray-300 dark:text-gray-600 text-5xl mb-4"></i>
    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No Accounts Yet</h3>
    <p class="text-gray-600 dark:text-gray-400 mb-6">Start by adding your first financial account to track your balances</p>
    <button onclick="openModal('accountModal')" class="bg-primary hover:bg-blue-600 text-white px-6 py-2 rounded-lg inline-flex items-center gap-2">
        <i class="fas fa-plus"></i>
        <span>Add Your First Account</span>
    </button>
</div>
<?php endif; ?>

<!-- Add/Edit Account Modal -->
<div id="accountModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
    <div class="bg-white dark:bg-gray-800 rounded-lg w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white" id="modalTitle">Add Account</h3>
            <button onclick="closeModal('accountModal')" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <form id="accountForm" class="p-6 space-y-4">
            <input type="hidden" id="accountId" name="id">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Account Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="account_name" id="accountName" required
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent dark:bg-gray-700 dark:text-white">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Account Type <span class="text-red-500">*</span>
                    </label>
                    <select name="account_type" id="accountType" required
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent dark:bg-gray-700 dark:text-white">
                        <option value="checking">Checking</option>
                        <option value="savings">Savings</option>
                        <option value="credit_card">Credit Card</option>
                        <option value="investment">Investment</option>
                        <option value="cash">Cash</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Bank Name</label>
                    <input type="text" name="bank_name" id="bankName"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent dark:bg-gray-700 dark:text-white">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Last 4 Digits</label>
                    <input type="text" name="account_number_last4" id="accountLast4" maxlength="4" pattern="[0-9]{4}"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent dark:bg-gray-700 dark:text-white">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Current Balance <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="current_balance" id="currentBalance" step="0.01" required
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent dark:bg-gray-700 dark:text-white">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Currency</label>
                    <select name="currency" id="currency"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent dark:bg-gray-700 dark:text-white">
                        <option value="USD">USD ($)</option>
                        <option value="EUR">EUR (€)</option>
                        <option value="GBP">GBP (£)</option>
                        <option value="JPY">JPY (¥)</option>
                        <option value="CAD">CAD ($)</option>
                        <option value="AUD">AUD ($)</option>
                    </select>
                </div>

                <div id="creditLimitField" class="hidden">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Credit Limit</label>
                    <input type="number" name="credit_limit" id="creditLimit" step="0.01"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent dark:bg-gray-700 dark:text-white">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Interest Rate (%)</label>
                    <input type="number" name="interest_rate" id="interestRate" step="0.01"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent dark:bg-gray-700 dark:text-white">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Notes</label>
                <textarea name="notes" id="accountNotes" rows="3"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent dark:bg-gray-700 dark:text-white"></textarea>
            </div>

            <div class="flex items-center">
                <input type="checkbox" name="is_active" id="isActive" checked class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                <label for="isActive" class="ml-2 text-sm text-gray-700 dark:text-gray-300">Account is active</label>
            </div>

            <div class="flex gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <button type="submit" class="flex-1 bg-primary hover:bg-blue-600 text-white px-6 py-2 rounded-lg transition-colors">
                    Save Account
                </button>
                <button type="button" onclick="closeModal('accountModal')" class="px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-gray-700 dark:text-gray-300">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Show/hide credit limit field based on account type
document.getElementById('accountType').addEventListener('change', function() {
    const creditLimitField = document.getElementById('creditLimitField');
    if (this.value === 'credit_card') {
        creditLimitField.classList.remove('hidden');
    } else {
        creditLimitField.classList.add('hidden');
    }
});

// Handle form submission
document.getElementById('accountForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());
    data.is_active = document.getElementById('isActive').checked;
    
    const accountId = document.getElementById('accountId').value;
    const url = '/api/accounts.php' + (accountId ? '?action=update&id=' + accountId : '?action=create');
    
    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast(result.message || 'Account saved successfully', 'success');
            closeModal('accountModal');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showToast(result.message || 'Failed to save account', 'error');
        }
    } catch (error) {
        showToast('An error occurred', 'error');
    }
});

function editAccount(id) {
    fetch(`/api/accounts.php?action=read&id=${id}`)
        .then(response => response.json())
        .then(result => {
            if (result.success && result.data) {
                const account = result.data;
                document.getElementById('modalTitle').textContent = 'Edit Account';
                document.getElementById('accountId').value = account.id;
                document.getElementById('accountName').value = account.account_name;
                document.getElementById('accountType').value = account.account_type;
                document.getElementById('bankName').value = account.bank_name || '';
                document.getElementById('accountLast4').value = account.account_number_last4 || '';
                document.getElementById('currentBalance').value = account.current_balance;
                document.getElementById('currency').value = account.currency;
                document.getElementById('creditLimit').value = account.credit_limit || '';
                document.getElementById('interestRate').value = account.interest_rate || '';
                document.getElementById('accountNotes').value = account.notes || '';
                document.getElementById('isActive').checked = account.is_active;
                
                // Show credit limit field if credit card
                if (account.account_type === 'credit_card') {
                    document.getElementById('creditLimitField').classList.remove('hidden');
                }
                
                openModal('accountModal');
            }
        });
}

function deleteAccount(id) {
    if (!confirm('Are you sure you want to delete this account?')) return;
    
    fetch(`/api/accounts.php?action=delete&id=${id}`, {
        method: 'POST',
        headers: {
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showToast('Account deleted successfully', 'success');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showToast('Failed to delete account', 'error');
        }
    });
}

function openModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    
    // Reset form
    if (modalId === 'accountModal') {
        document.getElementById('accountForm').reset();
        document.getElementById('accountId').value = '';
        document.getElementById('modalTitle').textContent = 'Add Account';
        document.getElementById('creditLimitField').classList.add('hidden');
    }
}

function showToast(message, type = 'info') {
    // Simple toast notification
    const toast = document.createElement('div');
    toast.className = `fixed bottom-4 right-4 px-6 py-3 rounded-lg text-white z-50 ${
        type === 'success' ? 'bg-green-500' : 
        type === 'error' ? 'bg-red-500' : 'bg-blue-500'
    }`;
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}
</script>

<?php include 'includes/footer.php'; ?>
