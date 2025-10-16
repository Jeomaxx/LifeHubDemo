<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

// Get current month/year or from query params
$currentMonth = (int)($_GET['month'] ?? date('n'));
$currentYear = (int)($_GET['year'] ?? date('Y'));

// Get budgets for current period
$budgets = $db->fetchAll("
    SELECT * FROM budgets 
    WHERE user_id = ? AND month = ? AND year = ? 
    ORDER BY category
", [$userId, $currentMonth, $currentYear]);

// Calculate spending from finance table
foreach ($budgets as &$budget) {
    if ($budget['category']) {
        $spent = $db->fetchColumn("
            SELECT COALESCE(SUM(amount), 0) 
            FROM finance 
            WHERE user_id = ? 
            AND category = ? 
            AND type = 'expense'
            AND EXTRACT(MONTH FROM date) = ?
            AND EXTRACT(YEAR FROM date) = ?
        ", [$userId, $budget['category'], $currentMonth, $currentYear]);
        $budget['spent_amount'] = $spent;
    }
}

// Calculate total budget vs total spent
$totalBudget = array_sum(array_column($budgets, 'category_limit'));
$totalSpent = array_sum(array_column($budgets, 'spent_amount'));

$pageTitle = 'Budget Planner';
include 'includes/header.php';
?>

<div class="mb-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-calculator text-primary"></i>
                Budget Planner
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Track and manage your monthly budget</p>
        </div>
        <button onclick="openModal('budgetModal')" class="bg-primary hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center gap-2">
            <i class="fas fa-plus"></i>
            <span>Add Budget</span>
        </button>
    </div>
</div>

<!-- Month Navigator -->
<div class="bg-white dark:bg-gray-800 rounded-lg p-4 mb-6 shadow-sm border border-gray-200 dark:border-gray-700">
    <div class="flex items-center justify-between">
        <button onclick="navigateMonth(-1)" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
            <i class="fas fa-chevron-left"></i>
        </button>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            <?php echo date('F Y', strtotime("$currentYear-$currentMonth-01")); ?>
        </h3>
        <button onclick="navigateMonth(1)" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>
</div>

<!-- Budget Overview -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm border border-gray-200 dark:border-gray-700">
        <p class="text-sm text-gray-600 dark:text-gray-400">Total Budget</p>
        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mt-1"><?php echo formatCurrency($totalBudget); ?></h3>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm border border-gray-200 dark:border-gray-700">
        <p class="text-sm text-gray-600 dark:text-gray-400">Total Spent</p>
        <h3 class="text-2xl font-bold text-red-600 mt-1"><?php echo formatCurrency($totalSpent); ?></h3>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm border border-gray-200 dark:border-gray-700">
        <p class="text-sm text-gray-600 dark:text-gray-400">Remaining</p>
        <h3 class="text-2xl font-bold <?php echo ($totalBudget - $totalSpent) >= 0 ? 'text-green-600' : 'text-red-600'; ?> mt-1">
            <?php echo formatCurrency($totalBudget - $totalSpent); ?>
        </h3>
    </div>
</div>

<!-- Budget Categories -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
    <div class="p-6">
        <?php if (empty($budgets)): ?>
        <div class="text-center py-12">
            <i class="fas fa-calculator text-gray-300 dark:text-gray-600 text-5xl mb-4"></i>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No Budgets Set</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-6">Create your first budget to start tracking expenses</p>
            <button onclick="openModal('budgetModal')" class="bg-primary hover:bg-blue-600 text-white px-6 py-2 rounded-lg">
                Create Budget
            </button>
        </div>
        <?php else: ?>
        <div class="space-y-4">
            <?php foreach ($budgets as $budget): 
                $percentage = $budget['category_limit'] > 0 ? ($budget['spent_amount'] / $budget['category_limit']) * 100 : 0;
                $colorClass = $percentage >= 100 ? 'bg-red-500' : ($percentage >= 80 ? 'bg-yellow-500' : 'bg-green-500');
            ?>
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <div class="flex justify-between items-start mb-2">
                    <h4 class="font-semibold text-gray-900 dark:text-white"><?php echo sanitize($budget['category']); ?></h4>
                    <div class="flex gap-2">
                        <button onclick="editBudget(<?php echo $budget['id']; ?>)" class="text-blue-600 hover:text-blue-700">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="deleteBudget(<?php echo $budget['id']; ?>)" class="text-red-600 hover:text-red-700">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400 mb-2">
                    <span><?php echo formatCurrency($budget['spent_amount']); ?> / <?php echo formatCurrency($budget['category_limit']); ?></span>
                    <span><?php echo round($percentage, 1); ?>%</span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                    <div class="<?php echo $colorClass; ?> h-2 rounded-full transition-all" style="width: <?php echo min($percentage, 100); ?>%"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Budget Modal -->
<div id="budgetModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
    <div class="bg-white dark:bg-gray-800 rounded-lg w-full max-w-md mx-4">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white" id="modalTitle">Add Budget</h3>
            <button onclick="closeModal('budgetModal')" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form id="budgetForm" class="p-6 space-y-4">
            <input type="hidden" id="budgetId">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Category *</label>
                <input type="text" id="category" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Budget Limit *</label>
                <input type="number" id="categoryLimit" step="0.01" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Month</label>
                    <select id="month" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                        <?php for ($i = 1; $i <= 12; $i++): ?>
                        <option value="<?php echo $i; ?>" <?php echo $i == $currentMonth ? 'selected' : ''; ?>>
                            <?php echo date('F', mktime(0, 0, 0, $i, 1)); ?>
                        </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Year</label>
                    <input type="number" id="year" value="<?php echo $currentYear; ?>" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                </div>
            </div>
            <div class="flex gap-3 pt-4">
                <button type="submit" class="flex-1 bg-primary hover:bg-blue-600 text-white px-6 py-2 rounded-lg">Save</button>
                <button type="button" onclick="closeModal('budgetModal')" class="px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function navigateMonth(delta) {
    const month = <?php echo $currentMonth; ?>;
    const year = <?php echo $currentYear; ?>;
    const date = new Date(year, month - 1 + delta, 1);
    window.location.href = `?month=${date.getMonth() + 1}&year=${date.getFullYear()}`;
}

document.getElementById('budgetForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const id = document.getElementById('budgetId').value;
    const data = {
        budget_name: document.getElementById('category').value,
        category: document.getElementById('category').value,
        category_limit: parseFloat(document.getElementById('categoryLimit').value),
        month: parseInt(document.getElementById('month').value),
        year: parseInt(document.getElementById('year').value),
        total_budget: parseFloat(document.getElementById('categoryLimit').value)
    };
    
    const url = '/api/budgets.php' + (id ? '?action=update&id=' + id : '?action=create');
    const response = await fetch(url, {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content},
        body: JSON.stringify(data)
    });
    
    const result = await response.json();
    if (result.success) {
        alert('Budget saved successfully');
        window.location.reload();
    }
});

function editBudget(id) {
    fetch(`/api/budgets.php?action=read&id=${id}`)
        .then(r => r.json())
        .then(result => {
            const budget = result.data;
            document.getElementById('budgetId').value = budget.id;
            document.getElementById('category').value = budget.category;
            document.getElementById('categoryLimit').value = budget.category_limit;
            document.getElementById('month').value = budget.month;
            document.getElementById('year').value = budget.year;
            openModal('budgetModal');
        });
}

function deleteBudget(id) {
    if (!confirm('Delete this budget?')) return;
    fetch(`/api/budgets.php?action=delete&id=${id}`, {
        method: 'POST',
        headers: {'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content}
    }).then(() => window.location.reload());
}

function openModal(id) {
    document.getElementById(id).classList.remove('hidden');
    document.getElementById(id).classList.add('flex');
}

function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
    document.getElementById(id).classList.remove('flex');
}
</script>

<?php include 'includes/footer.php'; ?>
