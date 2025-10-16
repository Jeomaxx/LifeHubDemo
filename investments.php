<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

// Get investments data
$investments = $db->fetchAll("SELECT * FROM investments WHERE user_id = ? ORDER BY created_at DESC", [$userId]);
$stats = $db->fetchOne("
    SELECT 
        COALESCE(SUM(amount_invested), 0) as total_invested,
        COALESCE(SUM(current_value), 0) as total_value,
        COUNT(*) as total_count
    FROM investments 
    WHERE user_id = ?
", [$userId]);

$totalReturn = $stats['total_value'] - $stats['total_invested'];
$returnPercentage = $stats['total_invested'] > 0 ? ($totalReturn / $stats['total_invested']) * 100 : 0;

// Group by type for allocation
$allocationData = $db->fetchAll("
    SELECT 
        type,
        SUM(current_value) as value,
        COUNT(*) as count
    FROM investments 
    WHERE user_id = ? AND type IS NOT NULL
    GROUP BY type
    ORDER BY value DESC
", [$userId]);

// Calculate investment growth (last 6 months - based on amount invested)
$performanceData = [];
for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $monthEnd = date('Y-m-t', strtotime("-$i months"));
    $monthData = $db->fetchOne("
        SELECT COALESCE(SUM(amount_invested), 0) as value
        FROM investments 
        WHERE user_id = ? AND COALESCE(purchase_date, created_at) <= ?
    ", [$userId, $monthEnd]);
    $performanceData[] = [
        'month' => date('M', strtotime("-$i months")),
        'value' => $monthData['value'] ?? 0
    ];
}

$pageTitle = 'Investment Dashboard';
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-6">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <i class="fas fa-chart-line text-primary"></i>
                Investment Dashboard
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Track and analyze your investment portfolio</p>
        </div>
        <div class="flex gap-2">
            <button onclick="openModal('investmentModal')" class="btn bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-lg flex items-center gap-2">
                <i class="fas fa-plus"></i>
                <span>Add Investment</span>
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Invested</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                        <?php echo formatCurrency($stats['total_invested']); ?>
                    </p>
                </div>
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-piggy-bank text-blue-600 dark:text-blue-400 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Current Value</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                        <?php echo formatCurrency($stats['total_value']); ?>
                    </p>
                </div>
                <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-chart-line text-green-600 dark:text-green-400 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Return</p>
                    <p class="text-2xl font-bold <?php echo $totalReturn >= 0 ? 'text-green-600' : 'text-red-600'; ?> mt-1">
                        <?php echo ($totalReturn >= 0 ? '+' : '') . formatCurrency($totalReturn); ?>
                    </p>
                    <p class="text-xs <?php echo $returnPercentage >= 0 ? 'text-green-600' : 'text-red-600'; ?> mt-1">
                        <?php echo ($returnPercentage >= 0 ? '+' : '') . number_format($returnPercentage, 2); ?>%
                    </p>
                </div>
                <div class="w-12 h-12 <?php echo $totalReturn >= 0 ? 'bg-green-100 dark:bg-green-900' : 'bg-red-100 dark:bg-red-900'; ?> rounded-lg flex items-center justify-center">
                    <i class="fas fa-<?php echo $totalReturn >= 0 ? 'arrow-up' : 'arrow-down'; ?> <?php echo $totalReturn >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'; ?> text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Investments</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                        <?php echo $stats['total_count']; ?>
                    </p>
                </div>
                <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-briefcase text-purple-600 dark:text-purple-400 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Investment Growth Chart -->
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Investment Growth</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Total amount invested over time</p>
            <canvas id="performanceChart"></canvas>
        </div>

        <!-- Asset Allocation Chart -->
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Asset Allocation</h3>
            <canvas id="allocationChart"></canvas>
        </div>
    </div>

    <!-- Investments Table -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">All Investments</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Invested</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Current Value</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Return</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">ROI</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <?php if (empty($investments)): ?>
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                <i class="fas fa-chart-line text-4xl mb-3 opacity-50"></i>
                                <p>No investments yet. Add your first investment to get started!</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($investments as $investment): 
                            $return = $investment['current_value'] - $investment['amount_invested'];
                            $roi = $investment['amount_invested'] > 0 ? ($return / $investment['amount_invested']) * 100 : 0;
                        ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-white"><?php echo htmlspecialchars($investment['name']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs rounded-full bg-primary/10 text-primary">
                                    <?php echo htmlspecialchars($investment['type'] ?? 'N/A'); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                <?php echo formatCurrency($investment['amount_invested']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                <?php echo formatCurrency($investment['current_value']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm <?php echo $return >= 0 ? 'text-green-600' : 'text-red-600'; ?>">
                                <?php echo ($return >= 0 ? '+' : '') . formatCurrency($return); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm <?php echo $roi >= 0 ? 'text-green-600' : 'text-red-600'; ?>">
                                <?php echo ($roi >= 0 ? '+' : '') . number_format($roi, 2); ?>%
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                <?php echo $investment['purchase_date'] ? date('M d, Y', strtotime($investment['purchase_date'])) : 'N/A'; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <button onclick="editInvestment(<?php echo htmlspecialchars(json_encode($investment)); ?>)" class="text-primary hover:text-primary-dark mr-2">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="deleteInvestment(<?php echo $investment['id']; ?>)" class="text-red-600 hover:text-red-800">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add/Edit Investment Modal -->
<div id="investmentModal" class="modal hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-md w-full mx-4">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white" id="modalTitle">Add Investment</h3>
            <button class="modal-close text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="investmentForm" onsubmit="saveInvestment(event)">
            <input type="hidden" id="investmentId" name="id">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name *</label>
                    <input type="text" name="name" id="investmentName" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary dark:bg-gray-700 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type *</label>
                    <select name="type" id="investmentType" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary dark:bg-gray-700 dark:text-white">
                        <option value="">Select type...</option>
                        <option value="Stocks">Stocks</option>
                        <option value="Bonds">Bonds</option>
                        <option value="Real Estate">Real Estate</option>
                        <option value="Crypto">Cryptocurrency</option>
                        <option value="Mutual Funds">Mutual Funds</option>
                        <option value="ETF">ETF</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Amount Invested *</label>
                    <input type="number" name="amount_invested" id="investmentAmount" step="0.01" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary dark:bg-gray-700 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Current Value *</label>
                    <input type="number" name="current_value" id="investmentValue" step="0.01" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary dark:bg-gray-700 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Purchase Date</label>
                    <input type="date" name="purchase_date" id="investmentDate" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary dark:bg-gray-700 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
                    <textarea name="notes" id="investmentNotes" rows="3" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary dark:bg-gray-700 dark:text-white"></textarea>
                </div>
            </div>
            <div class="mt-6 flex gap-3">
                <button type="submit" class="flex-1 bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-lg">
                    Save Investment
                </button>
                <button type="button" class="modal-close flex-1 bg-gray-300 dark:bg-gray-600 hover:bg-gray-400 dark:hover:bg-gray-500 text-gray-800 dark:text-white px-4 py-2 rounded-lg">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Chart.js for visualizations
const performanceData = <?php echo json_encode($performanceData); ?>;
const allocationData = <?php echo json_encode($allocationData); ?>;

// Investment Growth Chart
const perfCtx = document.getElementById('performanceChart').getContext('2d');
new Chart(perfCtx, {
    type: 'line',
    data: {
        labels: performanceData.map(d => d.month),
        datasets: [{
            label: 'Total Invested',
            data: performanceData.map(d => d.value),
            borderColor: 'rgb(59, 130, 246)',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '$' + value.toLocaleString();
                    }
                }
            }
        }
    }
});

// Allocation Chart
if (allocationData.length > 0) {
    const allocCtx = document.getElementById('allocationChart').getContext('2d');
    new Chart(allocCtx, {
        type: 'doughnut',
        data: {
            labels: allocationData.map(d => d.type),
            datasets: [{
                data: allocationData.map(d => d.value),
                backgroundColor: [
                    'rgb(59, 130, 246)',
                    'rgb(16, 185, 129)',
                    'rgb(245, 158, 11)',
                    'rgb(239, 68, 68)',
                    'rgb(139, 92, 246)',
                    'rgb(236, 72, 153)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

function editInvestment(investment) {
    document.getElementById('modalTitle').textContent = 'Edit Investment';
    document.getElementById('investmentId').value = investment.id;
    document.getElementById('investmentName').value = investment.name;
    document.getElementById('investmentType').value = investment.type || '';
    document.getElementById('investmentAmount').value = investment.amount_invested;
    document.getElementById('investmentValue').value = investment.current_value;
    document.getElementById('investmentDate').value = investment.purchase_date || '';
    document.getElementById('investmentNotes').value = investment.notes || '';
    openModal('investmentModal');
}

async function saveInvestment(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    const data = Object.fromEntries(formData.entries());
    const id = data.id;
    delete data.id;
    
    const url = id ? `/api/crud.php?table=investments&id=${id}` : '/api/crud.php?table=investments';
    const method = id ? 'PUT' : 'POST';
    
    data.user_id = '<?php echo $userId; ?>';
    
    try {
        const response = await fetch(url, {
            method: method,
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        if (result.success) {
            showToast('success', 'Success', id ? 'Investment updated' : 'Investment added');
            setTimeout(() => location.reload(), 500);
        } else {
            showToast('error', 'Error', result.message);
        }
    } catch (error) {
        showToast('error', 'Error', 'Failed to save investment');
    }
}

async function deleteInvestment(id) {
    if (!confirm('Are you sure you want to delete this investment?')) return;
    
    try {
        const response = await fetch(`/api/crud.php?table=investments&id=${id}`, {
            method: 'DELETE'
        });
        
        const result = await response.json();
        if (result.success) {
            showToast('success', 'Success', 'Investment deleted');
            setTimeout(() => location.reload(), 500);
        } else {
            showToast('error', 'Error', result.message);
        }
    } catch (error) {
        showToast('error', 'Error', 'Failed to delete investment');
    }
}
</script>

<?php include 'includes/footer.php'; ?>
