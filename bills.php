<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

// Get categories and vendors for filters
$categories = $db->fetchAll("SELECT DISTINCT category FROM bills WHERE user_id = ? AND category IS NOT NULL ORDER BY category", [$userId]) ?: [];
$vendors = $db->fetchAll("SELECT DISTINCT vendor FROM bills WHERE user_id = ? AND vendor IS NOT NULL ORDER BY vendor", [$userId]) ?: [];
$budgets = $db->fetchAll("SELECT id, category, monthly_limit FROM budgets WHERE user_id = ? ORDER BY category", [$userId]) ?: [];

// Get bills with filters
$status = $_GET['status'] ?? '';
$category = $_GET['category'] ?? '';
$vendor = $_GET['vendor'] ?? '';

$query = "SELECT * FROM bills WHERE user_id = ?";
$params = [$userId];

if ($status) {
    $query .= " AND payment_status = ?";
    $params[] = $status;
}

if ($category) {
    $query .= " AND category = ?";
    $params[] = $category;
}

if ($vendor) {
    $query .= " AND vendor = ?";
    $params[] = $vendor;
}

$query .= " ORDER BY due_date ASC";
$bills = $db->fetchAll($query, $params) ?: [];

// Get stats
$stats = [
    'total' => count($bills),
    'pending' => $db->fetchColumn("SELECT COUNT(*) FROM bills WHERE user_id = ? AND payment_status = 'pending'", [$userId]),
    'overdue' => $db->fetchColumn("SELECT COUNT(*) FROM bills WHERE user_id = ? AND payment_status != 'paid' AND due_date < CURRENT_DATE", [$userId]),
    'total_due' => $db->fetchColumn("SELECT COALESCE(SUM(amount), 0) FROM bills WHERE user_id = ? AND payment_status != 'paid'", [$userId])
];

$pageTitle = 'Bills & Payments';
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-6">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <i class="fas fa-file-invoice-dollar text-primary"></i>
                <?php echo t('Bills & Payments'); ?>
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1"><?php echo t('Track and manage your bills and payments'); ?></p>
        </div>
        <div class="flex gap-2">
            <button onclick="openModal('importBillModal')" class="btn bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                <i class="fas fa-file-import"></i>
                <span><?php echo t('Import CSV'); ?></span>
            </button>
            <button onclick="openModal('billModal')" class="btn bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-lg flex items-center gap-2">
                <i class="fas fa-plus"></i>
                <span><?php echo t('Add Bill'); ?></span>
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400"><?php echo t('Total Bills'); ?></p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1"><?php echo $stats['total']; ?></p>
                </div>
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-file-invoice text-blue-600 dark:text-blue-400 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400"><?php echo t('Pending'); ?></p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1"><?php echo $stats['pending']; ?></p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clock text-yellow-600 dark:text-yellow-400 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400"><?php echo t('Overdue'); ?></p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1"><?php echo $stats['overdue']; ?></p>
                </div>
                <div class="w-12 h-12 bg-red-100 dark:bg-red-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-red-600 dark:text-red-400 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400"><?php echo t('Total Due'); ?></p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1"><?php echo formatCurrency($stats['total_due']); ?></p>
                </div>
                <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-dollar-sign text-green-600 dark:text-green-400 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm mb-6">
        <div class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><?php echo t('Status'); ?></label>
                <select id="statusFilter" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary">
                    <option value=""><?php echo t('All Status'); ?></option>
                    <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>><?php echo t('Pending'); ?></option>
                    <option value="paid" <?php echo $status === 'paid' ? 'selected' : ''; ?>><?php echo t('Paid'); ?></option>
                    <option value="overdue" <?php echo $status === 'overdue' ? 'selected' : ''; ?>><?php echo t('Overdue'); ?></option>
                </select>
            </div>

            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><?php echo t('Category'); ?></label>
                <select id="categoryFilter" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary">
                    <option value=""><?php echo t('All Categories'); ?></option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['category']; ?>" <?php echo $category === $cat['category'] ? 'selected' : ''; ?>><?php echo ucfirst($cat['category']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><?php echo t('Vendor'); ?></label>
                <select id="vendorFilter" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary">
                    <option value=""><?php echo t('All Vendors'); ?></option>
                    <?php foreach ($vendors as $v): ?>
                        <option value="<?php echo $v['vendor']; ?>" <?php echo $vendor === $v['vendor'] ? 'selected' : ''; ?>><?php echo $v['vendor']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex items-end">
                <button onclick="clearFilters()" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600">
                    <i class="fas fa-times mr-2"></i><?php echo t('Clear'); ?>
                </button>
            </div>
        </div>
    </div>

    <!-- Bills Table -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            <input type="checkbox" id="selectAll" class="rounded">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"><?php echo t('Bill Name'); ?></th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"><?php echo t('Vendor'); ?></th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"><?php echo t('Amount'); ?></th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"><?php echo t('Due Date'); ?></th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"><?php echo t('Status'); ?></th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"><?php echo t('Category'); ?></th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"><?php echo t('Actions'); ?></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <?php if (empty($bills)): ?>
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                <i class="fas fa-inbox text-4xl mb-3 opacity-50"></i>
                                <p><?php echo t('No bills found'); ?></p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($bills as $bill): 
                            $isOverdue = $bill['payment_status'] !== 'paid' && strtotime($bill['due_date']) < time();
                            $daysUntilDue = ceil((strtotime($bill['due_date']) - time()) / 86400);
                        ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 <?php echo $isOverdue ? 'bg-red-50 dark:bg-red-900/20' : ''; ?>">
                            <td class="px-6 py-4">
                                <input type="checkbox" class="bill-checkbox rounded" data-bill-id="<?php echo $bill['id']; ?>">
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium text-gray-900 dark:text-white"><?php echo sanitize($bill['name']); ?></span>
                                    <?php if ($bill['recurring']): ?>
                                        <span class="text-xs bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 px-2 py-0.5 rounded-full">
                                            <i class="fas fa-sync-alt"></i> <?php echo ucfirst($bill['frequency'] ?? 'recurring'); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                <?php echo sanitize($bill['vendor'] ?? '-'); ?>
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">
                                <?php echo formatCurrency($bill['amount']); ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                <div>
                                    <?php echo formatDate($bill['due_date']); ?>
                                    <?php if ($isOverdue): ?>
                                        <span class="block text-xs text-red-600 dark:text-red-400 mt-1">
                                            <i class="fas fa-exclamation-circle"></i> <?php echo abs($daysUntilDue); ?> days overdue
                                        </span>
                                    <?php elseif ($daysUntilDue <= 7 && $bill['payment_status'] !== 'paid'): ?>
                                        <span class="block text-xs text-yellow-600 dark:text-yellow-400 mt-1">
                                            <i class="fas fa-clock"></i> Due in <?php echo $daysUntilDue; ?> days
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <?php
                                    $statusClass = $bill['payment_status'] === 'paid' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 
                                                   ($isOverdue ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200');
                                ?>
                                <span class="px-2 py-1 text-xs font-medium rounded-full <?php echo $statusClass; ?>">
                                    <?php echo ucfirst($bill['payment_status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                <?php echo ucfirst($bill['category'] ?? '-'); ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <button onclick="viewBillDetail(<?php echo $bill['id']; ?>)" class="text-blue-600 hover:text-blue-800 dark:text-blue-400" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button onclick="editBill(<?php echo $bill['id']; ?>)" class="text-gray-600 hover:text-gray-800 dark:text-gray-400" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <?php if ($bill['payment_status'] !== 'paid'): ?>
                                        <button onclick="markAsPaid(<?php echo $bill['id']; ?>)" class="text-green-600 hover:text-green-800 dark:text-green-400" title="Mark as Paid">
                                            <i class="fas fa-check-circle"></i>
                                        </button>
                                    <?php endif; ?>
                                    <button onclick="sendReminder(<?php echo $bill['id']; ?>)" class="text-purple-600 hover:text-purple-800 dark:text-purple-400" title="Send Reminder">
                                        <i class="fas fa-bell"></i>
                                    </button>
                                    <button onclick="deleteBill(<?php echo $bill['id']; ?>)" class="text-red-600 hover:text-red-800 dark:text-red-400" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Bulk Actions -->
        <div id="bulkActions" class="hidden bg-gray-100 dark:bg-gray-700 px-6 py-3 border-t border-gray-200 dark:border-gray-600">
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    <span id="selectedCount">0</span> <?php echo t('bills selected'); ?>
                </span>
                <div class="flex gap-2">
                    <button onclick="bulkMarkPaid()" class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                        <i class="fas fa-check mr-1"></i><?php echo t('Mark All Paid'); ?>
                    </button>
                    <button onclick="bulkDelete()" class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 text-sm">
                        <i class="fas fa-trash mr-1"></i><?php echo t('Delete Selected'); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Bill Modal -->
<div id="billModal" class="modal hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center p-6 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white" id="modalTitle"><?php echo t('Add Bill'); ?></h3>
            <button onclick="closeModal('billModal')" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form id="billForm" class="p-6">
            <input type="hidden" name="csrf_token" value="<?php echo $auth->generateCSRFToken(); ?>">
            <input type="hidden" id="billId" name="id">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><?php echo t('Bill Name'); ?> <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="billName" required class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><?php echo t('Amount'); ?> <span class="text-red-500">*</span></label>
                    <input type="number" name="amount" id="billAmount" step="0.01" required class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><?php echo t('Due Date'); ?> <span class="text-red-500">*</span></label>
                    <input type="date" name="due_date" id="billDueDate" required class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><?php echo t('Category'); ?></label>
                    <input type="text" name="category" id="billCategory" list="categoryList" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary">
                    <datalist id="categoryList">
                        <option value="utilities">Utilities</option>
                        <option value="rent">Rent</option>
                        <option value="insurance">Insurance</option>
                        <option value="subscription">Subscription</option>
                        <option value="loan">Loan</option>
                        <option value="other">Other</option>
                    </datalist>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><?php echo t('Vendor'); ?></label>
                    <input type="text" name="vendor" id="billVendor" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><?php echo t('Payment Status'); ?></label>
                    <select name="payment_status" id="billStatus" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary">
                        <option value="pending"><?php echo t('Pending'); ?></option>
                        <option value="paid"><?php echo t('Paid'); ?></option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><?php echo t('Payment Method'); ?></label>
                    <select name="payment_method" id="billPaymentMethod" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary">
                        <option value=""><?php echo t('Select...'); ?></option>
                        <option value="cash"><?php echo t('Cash'); ?></option>
                        <option value="credit_card"><?php echo t('Credit Card'); ?></option>
                        <option value="debit_card"><?php echo t('Debit Card'); ?></option>
                        <option value="bank_transfer"><?php echo t('Bank Transfer'); ?></option>
                        <option value="auto_pay"><?php echo t('Auto Pay'); ?></option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><?php echo t('Link to Budget'); ?></label>
                    <select name="budget_id" id="billBudgetId" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary">
                        <option value=""><?php echo t('None'); ?></option>
                        <?php foreach ($budgets as $budget): ?>
                            <option value="<?php echo $budget['id']; ?>"><?php echo $budget['category']; ?> (<?php echo formatCurrency($budget['monthly_limit']); ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><?php echo t('Reminder (days before)'); ?></label>
                    <input type="number" name="reminder_days_before" id="billReminder" value="3" min="0" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary">
                </div>

                <div class="md:col-span-2">
                    <label class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        <input type="checkbox" name="recurring" id="billRecurring" class="rounded" onchange="toggleRecurringFields()">
                        <?php echo t('Recurring Bill'); ?>
                    </label>
                </div>

                <div id="frequencyField" class="md:col-span-2 hidden">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><?php echo t('Frequency'); ?></label>
                    <select name="frequency" id="billFrequency" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary">
                        <option value="weekly"><?php echo t('Weekly'); ?></option>
                        <option value="biweekly"><?php echo t('Bi-Weekly'); ?></option>
                        <option value="monthly" selected><?php echo t('Monthly'); ?></option>
                        <option value="quarterly"><?php echo t('Quarterly'); ?></option>
                        <option value="yearly"><?php echo t('Yearly'); ?></option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        <input type="checkbox" name="auto_pay" id="billAutoPay" class="rounded">
                        <?php echo t('Auto Pay Enabled'); ?>
                    </label>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><?php echo t('Notes'); ?></label>
                    <textarea name="notes" id="billNotes" rows="3" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary"></textarea>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-6">
                <button type="button" onclick="closeModal('billModal')" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300">
                    <?php echo t('Cancel'); ?>
                </button>
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark">
                    <?php echo t('Save Bill'); ?>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Bill Detail Modal -->
<div id="billDetailModal" class="modal hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-3xl mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center p-6 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white"><?php echo t('Bill Details'); ?></h3>
            <button onclick="closeModal('billDetailModal')" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div id="billDetailContent" class="p-6">
            <!-- Content loaded dynamically -->
        </div>
    </div>
</div>

<!-- CSV Import Modal -->
<div id="importBillModal" class="modal hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-lg mx-4">
        <div class="flex justify-between items-center p-6 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white"><?php echo t('Import Bills from CSV'); ?></h3>
            <button onclick="closeModal('importBillModal')" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div class="p-6">
            <div class="mb-4">
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2"><?php echo t('CSV Format: name, amount, due_date, category, vendor, recurring, frequency'); ?></p>
                <p class="text-xs text-gray-500 dark:text-gray-500"><?php echo t('Example: Electric Bill, 150.00, 2025-01-15, utilities, Power Company, true, monthly'); ?></p>
            </div>
            <form id="importBillForm" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo $auth->generateCSRFToken(); ?>">
                <div class="mb-4">
                    <input type="file" name="csv_file" accept=".csv" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg">
                </div>
                <div id="importProgress" class="hidden mb-4">
                    <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                        <div id="importProgressBar" class="bg-primary h-2.5 rounded-full" style="width: 0%"></div>
                    </div>
                    <p id="importStatus" class="text-sm text-gray-600 dark:text-gray-400 mt-2"></p>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeModal('importBillModal')" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg">
                        <?php echo t('Cancel'); ?>
                    </button>
                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark">
                        <i class="fas fa-upload mr-2"></i><?php echo t('Import'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Filter change handlers
document.getElementById('statusFilter').addEventListener('change', applyFilters);
document.getElementById('categoryFilter').addEventListener('change', applyFilters);
document.getElementById('vendorFilter').addEventListener('change', applyFilters);

function applyFilters() {
    const status = document.getElementById('statusFilter').value;
    const category = document.getElementById('categoryFilter').value;
    const vendor = document.getElementById('vendorFilter').value;
    
    const params = new URLSearchParams();
    if (status) params.append('status', status);
    if (category) params.append('category', category);
    if (vendor) params.append('vendor', vendor);
    
    window.location.href = 'bills.php?' + params.toString();
}

function clearFilters() {
    window.location.href = 'bills.php';
}

// Toggle recurring fields
function toggleRecurringFields() {
    const recurring = document.getElementById('billRecurring').checked;
    document.getElementById('frequencyField').classList.toggle('hidden', !recurring);
}

// Bill form submission
document.getElementById('billForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const billId = document.getElementById('billId').value;
    const data = {};
    
    formData.forEach((value, key) => {
        if (key === 'recurring' || key === 'auto_pay') {
            data[key] = value === 'on' || value === '1';
        } else {
            data[key] = value;
        }
    });
    
    const url = billId ? `/api/bills.php?action=update&id=${billId}&csrf_token=${formData.get('csrf_token')}` : '/api/bills.php?action=create';
    const method = billId ? 'PUT' : 'POST';
    
    try {
        const response = await fetch(url, {
            method: method,
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('Bill saved successfully', 'success');
            closeModal('billModal');
            setTimeout(() => location.reload(), 500);
        } else {
            showToast(result.error || 'Failed to save bill', 'error');
        }
    } catch (error) {
        showToast('An error occurred', 'error');
    }
});

// Edit bill
async function editBill(billId) {
    try {
        const response = await fetch(`/api/bills.php?action=detail&id=${billId}`);
        const result = await response.json();
        
        if (result.success) {
            const bill = result.bill;
            document.getElementById('modalTitle').textContent = '<?php echo t('Edit Bill'); ?>';
            document.getElementById('billId').value = bill.id;
            document.getElementById('billName').value = bill.name;
            document.getElementById('billAmount').value = bill.amount;
            document.getElementById('billDueDate').value = bill.due_date;
            document.getElementById('billCategory').value = bill.category || '';
            document.getElementById('billVendor').value = bill.vendor || '';
            document.getElementById('billStatus').value = bill.payment_status;
            document.getElementById('billPaymentMethod').value = bill.payment_method || '';
            document.getElementById('billBudgetId').value = bill.budget_id || '';
            document.getElementById('billReminder').value = bill.reminder_days_before;
            document.getElementById('billRecurring').checked = bill.recurring;
            document.getElementById('billFrequency').value = bill.frequency || 'monthly';
            document.getElementById('billAutoPay').checked = bill.auto_pay;
            document.getElementById('billNotes').value = bill.notes || '';
            
            toggleRecurringFields();
            openModal('billModal');
        }
    } catch (error) {
        showToast('Failed to load bill', 'error');
    }
}

// View bill detail
async function viewBillDetail(billId) {
    try {
        const response = await fetch(`/api/bills.php?action=detail&id=${billId}`);
        const result = await response.json();
        
        if (result.success) {
            const bill = result.bill;
            let html = `
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400"><?php echo t('Bill Name'); ?></p>
                            <p class="font-medium text-gray-900 dark:text-white">${bill.name}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400"><?php echo t('Amount'); ?></p>
                            <p class="font-medium text-gray-900 dark:text-white">${formatCurrency(bill.amount)}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400"><?php echo t('Due Date'); ?></p>
                            <p class="font-medium text-gray-900 dark:text-white">${formatDate(bill.due_date)}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400"><?php echo t('Status'); ?></p>
                            <p class="font-medium text-gray-900 dark:text-white">${bill.payment_status}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400"><?php echo t('Vendor'); ?></p>
                            <p class="font-medium text-gray-900 dark:text-white">${bill.vendor || '-'}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400"><?php echo t('Category'); ?></p>
                            <p class="font-medium text-gray-900 dark:text-white">${bill.category || '-'}</p>
                        </div>
                    </div>
                    
                    ${bill.notes ? `
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400"><?php echo t('Notes'); ?></p>
                            <p class="text-gray-900 dark:text-white">${bill.notes}</p>
                        </div>
                    ` : ''}
                    
                    ${bill.payments && bill.payments.length > 0 ? `
                        <div>
                            <h4 class="font-medium text-gray-900 dark:text-white mb-2"><?php echo t('Payment History'); ?></h4>
                            <div class="space-y-2">
                                ${bill.payments.map(p => `
                                    <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-700 rounded">
                                        <div>
                                            <p class="font-medium">${formatCurrency(p.amount)}</p>
                                            <p class="text-sm text-gray-500">${formatDate(p.payment_date)}</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-sm">${p.payment_method || '-'}</p>
                                            ${p.transaction_id ? `<p class="text-xs text-gray-500">${p.transaction_id}</p>` : ''}
                                        </div>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    ` : ''}
                </div>
            `;
            
            document.getElementById('billDetailContent').innerHTML = html;
            openModal('billDetailModal');
        }
    } catch (error) {
        showToast('Failed to load bill details', 'error');
    }
}

// Mark as paid
async function markAsPaid(billId) {
    if (!confirm('<?php echo t('Mark this bill as paid?'); ?>')) return;
    
    try {
        const response = await fetch('/api/bills.php?action=mark-paid', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                bill_id: billId,
                csrf_token: '<?php echo $auth->generateCSRFToken(); ?>'
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('Bill marked as paid', 'success');
            setTimeout(() => location.reload(), 500);
        } else {
            showToast(result.error || 'Failed to mark as paid', 'error');
        }
    } catch (error) {
        showToast('An error occurred', 'error');
    }
}

// Send reminder
async function sendReminder(billId) {
    try {
        const response = await fetch('/api/bills.php?action=send-reminder', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                bill_id: billId,
                csrf_token: '<?php echo $auth->generateCSRFToken(); ?>'
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast(`Reminder sent via ${result.channels.join(', ')}`, 'success');
        } else {
            showToast(result.error || 'Failed to send reminder', 'error');
        }
    } catch (error) {
        showToast('An error occurred', 'error');
    }
}

// Delete bill
async function deleteBill(billId) {
    if (!confirm('<?php echo t('Are you sure you want to delete this bill?'); ?>')) return;
    
    try {
        const response = await fetch(`/api/bills.php?id=${billId}&csrf_token=<?php echo $auth->generateCSRFToken(); ?>`, {
            method: 'DELETE'
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('Bill deleted successfully', 'success');
            setTimeout(() => location.reload(), 500);
        } else {
            showToast(result.error || 'Failed to delete bill', 'error');
        }
    } catch (error) {
        showToast('An error occurred', 'error');
    }
}

// Bulk operations
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.bill-checkbox');
    checkboxes.forEach(cb => cb.checked = this.checked);
    updateBulkActions();
});

document.querySelectorAll('.bill-checkbox').forEach(cb => {
    cb.addEventListener('change', updateBulkActions);
});

function updateBulkActions() {
    const selected = document.querySelectorAll('.bill-checkbox:checked').length;
    document.getElementById('selectedCount').textContent = selected;
    document.getElementById('bulkActions').classList.toggle('hidden', selected === 0);
}

async function bulkMarkPaid() {
    const billIds = Array.from(document.querySelectorAll('.bill-checkbox:checked')).map(cb => parseInt(cb.dataset.billId));
    
    if (billIds.length === 0) return;
    
    if (!confirm(`Mark ${billIds.length} bill(s) as paid?`)) return;
    
    try {
        const response = await fetch('/api/bills.php?action=bulk-mark-paid', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                bill_ids: billIds,
                csrf_token: '<?php echo $auth->generateCSRFToken(); ?>'
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('Bills marked as paid', 'success');
            setTimeout(() => location.reload(), 500);
        } else {
            showToast('Some bills could not be updated', 'error');
        }
    } catch (error) {
        showToast('An error occurred', 'error');
    }
}

// CSV Import
document.getElementById('importBillForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    document.getElementById('importProgress').classList.remove('hidden');
    document.getElementById('importStatus').textContent = 'Uploading...';
    
    try {
        const response = await fetch('/api/bills_import.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            document.getElementById('importProgressBar').style.width = '100%';
            document.getElementById('importStatus').textContent = `Imported ${result.imported} bills successfully`;
            showToast(`Imported ${result.imported} bills`, 'success');
            setTimeout(() => {
                closeModal('importBillModal');
                location.reload();
            }, 1500);
        } else {
            document.getElementById('importStatus').textContent = result.error;
            showToast(result.error, 'error');
        }
    } catch (error) {
        document.getElementById('importStatus').textContent = 'Import failed';
        showToast('Import failed', 'error');
    }
});

// Helper functions
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(amount);
}

function formatDate(dateStr) {
    return new Date(dateStr).toLocaleDateString();
}

function openModal(modalId) {
    document.getElementById(modalId).classList.remove('hidden');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
    if (modalId === 'billModal') {
        document.getElementById('billForm').reset();
        document.getElementById('billId').value = '';
        document.getElementById('modalTitle').textContent = '<?php echo t('Add Bill'); ?>';
        document.getElementById('frequencyField').classList.add('hidden');
    }
}

function showToast(message, type = 'info') {
    // Use existing toast system
    if (typeof window.showToast === 'function') {
        window.showToast(message, type);
    } else {
        alert(message);
    }
}
</script>

<?php include 'includes/footer.php'; ?>
