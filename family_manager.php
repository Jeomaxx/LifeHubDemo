<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$pageTitle = 'Family Manager';
$extraScripts = ['/assets/js/family-manager.js'];
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <i class="fas fa-users text-primary"></i>
                Household & Family Manager
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Manage family members, shared tasks, expenses, and grocery lists</p>
        </div>
    </div>

    <!-- Tabs -->
    <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
        <nav class="flex space-x-4">
            <button onclick="switchTab('members')" id="tab-members" class="tab-btn active px-4 py-2 border-b-2 font-medium text-sm">
                <i class="fas fa-user-friends"></i> Family Members
            </button>
            <button onclick="switchTab('tasks')" id="tab-tasks" class="tab-btn px-4 py-2 border-b-2 font-medium text-sm">
                <i class="fas fa-tasks"></i> Household Tasks
            </button>
            <button onclick="switchTab('expenses')" id="tab-expenses" class="tab-btn px-4 py-2 border-b-2 font-medium text-sm">
                <i class="fas fa-money-bill-wave"></i> Shared Expenses
            </button>
            <button onclick="switchTab('grocery')" id="tab-grocery" class="tab-btn px-4 py-2 border-b-2 font-medium text-sm">
                <i class="fas fa-shopping-cart"></i> Grocery Lists
            </button>
        </nav>
    </div>

    <!-- Family Members Tab -->
    <div id="content-members" class="tab-content">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Family Members</h2>
                <button onclick="showMemberModal()" class="btn btn-primary flex items-center gap-2">
                    <i class="fas fa-plus"></i> Add Member
                </button>
            </div>
            <div id="membersList" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4"></div>
        </div>
    </div>

    <!-- Household Tasks Tab -->
    <div id="content-tasks" class="tab-content hidden">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Household Tasks</h2>
                <button onclick="showTaskModal()" class="btn btn-primary flex items-center gap-2">
                    <i class="fas fa-plus"></i> Add Task
                </button>
            </div>
            <div id="tasksList" class="space-y-3"></div>
        </div>
    </div>

    <!-- Shared Expenses Tab -->
    <div id="content-expenses" class="tab-content hidden">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Shared Expenses</h2>
                <button onclick="showExpenseModal()" class="btn btn-primary flex items-center gap-2">
                    <i class="fas fa-plus"></i> Add Expense
                </button>
            </div>
            <div id="expensesList" class="space-y-3"></div>
        </div>
    </div>

    <!-- Grocery Lists Tab -->
    <div id="content-grocery" class="tab-content hidden">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Grocery Lists</h2>
                <button onclick="showGroceryModal()" class="btn btn-primary flex items-center gap-2">
                    <i class="fas fa-plus"></i> Add List
                </button>
            </div>
            <div id="groceryLists" class="space-y-3"></div>
        </div>
    </div>
</div>

<!-- Member Modal -->
<div id="memberModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-lg w-full max-w-lg">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Add Family Member</h2>
            <button onclick="closeMemberModal()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="memberForm" class="p-6 space-y-4">
            <input type="hidden" id="memberId">
            <div>
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Name *</label>
                <input type="text" id="memberName" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Relationship</label>
                <input type="text" id="memberRelationship" placeholder="e.g., Spouse, Child, Parent" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Email</label>
                    <input type="email" id="memberEmail" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Phone</label>
                    <input type="tel" id="memberPhone" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Birthday</label>
                <input type="date" id="memberBirthday" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Notes</label>
                <textarea id="memberNotes" rows="3" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"></textarea>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeMemberModal()" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Member</button>
            </div>
        </form>
    </div>
</div>

<!-- Task Modal -->
<div id="taskModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-lg w-full max-w-lg">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Add Household Task</h2>
            <button onclick="closeTaskModal()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="taskForm" class="p-6 space-y-4">
            <input type="hidden" id="taskId">
            <div>
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Task Title *</label>
                <input type="text" id="taskTitle" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Description</label>
                <textarea id="taskDescription" rows="3" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Assign To</label>
                    <select id="taskAssignedTo" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        <option value="">Unassigned</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Due Date</label>
                    <input type="date" id="taskDueDate" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Priority</label>
                    <select id="taskPriority" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Category</label>
                    <input type="text" id="taskCategory" placeholder="e.g., Cleaning, Maintenance" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeTaskModal()" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Task</button>
            </div>
        </form>
    </div>
</div>

<!-- Expense Modal -->
<div id="expenseModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-lg w-full max-w-lg">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Add Shared Expense</h2>
            <button onclick="closeExpenseModal()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="expenseForm" class="p-6 space-y-4">
            <input type="hidden" id="expenseId">
            <div>
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Description *</label>
                <input type="text" id="expenseDescription" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Total Amount *</label>
                    <input type="number" id="expenseAmount" step="0.01" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Date *</label>
                    <input type="date" id="expenseDate" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Paid By</label>
                    <select id="expensePaidBy" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        <option value="">Select Member</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Category</label>
                    <input type="text" id="expenseCategory" placeholder="e.g., Groceries, Utilities" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Split Type</label>
                <select id="expenseSplitType" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    <option value="equal">Split Equally</option>
                    <option value="custom">Custom Split</option>
                    <option value="percentage">By Percentage</option>
                </select>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeExpenseModal()" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Expense</button>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
