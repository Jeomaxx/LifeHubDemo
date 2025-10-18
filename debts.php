<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$debts = $db->fetchAll("SELECT * FROM debts WHERE user_id = ? ORDER BY priority_order, current_balance DESC", [$userId]);

$totalDebt = 0;
$totalPaid = 0;
foreach ($debts as $debt) {
    $totalDebt += $debt['current_balance'];
    $totalPaid += ($debt['principal_amount'] - $debt['current_balance']);
}

$pageTitle = 'Debt Payoff Planner';
$extraScripts = ['/assets/js/new-modules.js'];
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <i class="fas fa-hand-holding-usd text-primary"></i>
                Debt Payoff Planner
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Track debts and optimize your payoff strategy</p>
        </div>
        <div class="flex gap-2">
            <button onclick="showAddDebtModal()" class="btn btn-primary flex items-center gap-2">
                <i class="fas fa-plus"></i>
                Add Debt
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Debt</p>
                    <h3 class="text-2xl font-bold text-red-600 dark:text-red-400"><?php echo formatCurrency($totalDebt); ?></h3>
                </div>
                <i class="fas fa-credit-card text-3xl text-red-500"></i>
            </div>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Paid</p>
                    <h3 class="text-2xl font-bold text-green-600 dark:text-green-400"><?php echo formatCurrency($totalPaid); ?></h3>
                </div>
                <i class="fas fa-check-circle text-3xl text-green-500"></i>
            </div>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Active Debts</p>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo count($debts); ?></h3>
                </div>
                <i class="fas fa-list text-3xl text-primary"></i>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Payoff Strategy</h2>
            <select id="strategySelect" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700">
                <option value="snowball">Snowball (Smallest Balance First)</option>
                <option value="avalanche">Avalanche (Highest Interest First)</option>
                <option value="custom">Custom Priority</option>
            </select>
        </div>
        <div id="strategyVisualization" class="mt-4"></div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
        <div class="p-6">
            <h2 class="text-xl font-bold mb-4 text-gray-900 dark:text-white">Your Debts</h2>
            <?php if (empty($debts)): ?>
                <div class="text-center py-12">
                    <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500">No debts tracked yet. Add your first debt to start planning!</p>
                </div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($debts as $debt): 
                        $progressPercent = (($debt['principal_amount'] - $debt['current_balance']) / $debt['principal_amount']) * 100;
                    ?>
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-md transition-shadow">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <h3 class="font-bold text-lg text-gray-900 dark:text-white"><?php echo sanitize($debt['name']); ?></h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400"><?php echo ucfirst(str_replace('_', ' ', $debt['debt_type'])); ?></p>
                            </div>
                            <div class="flex gap-2">
                                <button onclick="viewDebtDetails(<?php echo $debt['id']; ?>)" class="text-blue-600 hover:text-blue-700">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button onclick="editDebt(<?php echo $debt['id']; ?>)" class="text-green-600 hover:text-green-700">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="deleteDebt(<?php echo $debt['id']; ?>)" class="text-red-600 hover:text-red-700">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-3">
                            <div>
                                <p class="text-xs text-gray-500">Current Balance</p>
                                <p class="font-semibold text-red-600"><?php echo formatCurrency($debt['current_balance']); ?></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Original Amount</p>
                                <p class="font-semibold"><?php echo formatCurrency($debt['principal_amount']); ?></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Interest Rate</p>
                                <p class="font-semibold"><?php echo number_format($debt['interest_rate'], 2); ?>%</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Min. Payment</p>
                                <p class="font-semibold"><?php echo formatCurrency($debt['minimum_payment']); ?></p>
                            </div>
                        </div>
                        
                        <div class="mb-2">
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-600 dark:text-gray-400">Progress</span>
                                <span class="font-semibold"><?php echo number_format($progressPercent, 1); ?>% paid off</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                                <div class="bg-green-500 h-3 rounded-full transition-all" style="width: <?php echo $progressPercent; ?>%"></div>
                            </div>
                        </div>
                        
                        <button onclick="recordPayment(<?php echo $debt['id']; ?>)" class="w-full mt-3 px-4 py-2 bg-primary text-white rounded-lg hover:bg-blue-600">
                            <i class="fas fa-dollar-sign mr-2"></i>Record Payment
                        </button>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div id="addDebtModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-lg w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Add Debt</h2>
        </div>
        <form id="debtForm" class="p-6 space-y-4">
            <input type="hidden" id="debtId">
            
            <div>
                <label class="block text-sm font-medium mb-1">Debt Name *</label>
                <input type="text" id="debtName" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700">
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-1">Debt Type *</label>
                <select id="debtType" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700">
                    <option value="credit_card">Credit Card</option>
                    <option value="student_loan">Student Loan</option>
                    <option value="mortgage">Mortgage</option>
                    <option value="personal_loan">Personal Loan</option>
                    <option value="car_loan">Car Loan</option>
                </select>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Principal Amount *</label>
                    <input type="number" id="principalAmount" step="0.01" required class="w-full px-3 py-2 border rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Current Balance *</label>
                    <input type="number" id="currentBalance" step="0.01" required class="w-full px-3 py-2 border rounded-lg">
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Interest Rate (%) *</label>
                    <input type="number" id="interestRate" step="0.01" required class="w-full px-3 py-2 border rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Minimum Payment *</label>
                    <input type="number" id="minimumPayment" step="0.01" required class="w-full px-3 py-2 border rounded-lg">
                </div>
            </div>
            
            <div class="flex justify-end gap-2 pt-4">
                <button type="button" onclick="closeAddDebtModal()" class="px-4 py-2 border rounded-lg">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg">Save Debt</button>
            </div>
        </form>
    </div>
</div>

<div id="paymentModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-lg w-full max-w-md">
        <div class="p-6 border-b">
            <h2 class="text-xl font-bold">Record Payment</h2>
        </div>
        <form id="paymentForm" class="p-6 space-y-4">
            <input type="hidden" id="paymentDebtId">
            
            <div>
                <label class="block text-sm font-medium mb-1">Payment Amount *</label>
                <input type="number" id="paymentAmount" step="0.01" required class="w-full px-3 py-2 border rounded-lg">
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-1">Payment Date *</label>
                <input type="date" id="paymentDate" value="<?php echo date('Y-m-d'); ?>" required class="w-full px-3 py-2 border rounded-lg">
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-1">Notes</label>
                <textarea id="paymentNotes" rows="2" class="w-full px-3 py-2 border rounded-lg"></textarea>
            </div>
            
            <div class="flex justify-end gap-2">
                <button type="button" onclick="closePaymentModal()" class="px-4 py-2 border rounded-lg">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg">Record Payment</button>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
