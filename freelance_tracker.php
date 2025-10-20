<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$pageTitle = 'Freelance & Side-Hustle Tracker';
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <i data-lucide="briefcase" class="text-primary"></i>
                Freelance & Side-Hustle Tracker
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Manage clients, projects, invoices and track your freelance income</p>
        </div>
        <div class="flex gap-2">
            <button onclick="openClientModal()" class="btn bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                <i data-lucide="user-plus" class="w-4 h-4"></i>
                <span>Add Client</span>
            </button>
            <button onclick="openProjectModal()" class="btn bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-lg flex items-center gap-2">
                <i data-lucide="folder-plus" class="w-4 h-4"></i>
                <span>Add Project</span>
            </button>
            <button onclick="openInvoiceModal()" class="btn bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                <i data-lucide="file-text" class="w-4 h-4"></i>
                <span>Create Invoice</span>
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Active Projects</p>
                    <p id="activeProjects" class="text-2xl font-bold text-gray-900 dark:text-white mt-1">0</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                    <i data-lucide="folder" class="text-blue-600 dark:text-blue-400 w-6 h-6"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Earned (MTD)</p>
                    <p id="totalEarned" class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">$0</p>
                </div>
                <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                    <i data-lucide="dollar-sign" class="text-green-600 dark:text-green-400 w-6 h-6"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Pending Invoices</p>
                    <p id="pendingInvoices" class="text-2xl font-bold text-orange-600 dark:text-orange-400 mt-1">0</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900 rounded-lg flex items-center justify-center">
                    <i data-lucide="clock" class="text-orange-600 dark:text-orange-400 w-6 h-6"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Active Clients</p>
                    <p id="activeClients" class="text-2xl font-bold text-purple-600 dark:text-purple-400 mt-1">0</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                    <i data-lucide="users" class="text-purple-600 dark:text-purple-400 w-6 h-6"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md mb-6">
        <div class="flex border-b border-gray-200 dark:border-gray-700">
            <button class="tab-btn active px-6 py-4 font-medium text-primary border-b-2 border-primary" data-tab="clients">Clients</button>
            <button class="tab-btn px-6 py-4 font-medium text-gray-600 dark:text-gray-400" data-tab="projects">Projects</button>
            <button class="tab-btn px-6 py-4 font-medium text-gray-600 dark:text-gray-400" data-tab="invoices">Invoices</button>
            <button class="tab-btn px-6 py-4 font-medium text-gray-600 dark:text-gray-400" data-tab="time">Time Tracking</button>
        </div>

        <div id="clientsTab" class="tab-content p-6">
            <div class="mb-4 flex justify-between items-center">
                <h2 class="text-xl font-semibold">Clients</h2>
                <input type="text" id="clientSearch" placeholder="Search clients..." class="px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
            </div>
            <div id="clientsList"></div>
        </div>

        <div id="projectsTab" class="tab-content p-6 hidden">
            <div class="mb-4 flex justify-between items-center">
                <h2 class="text-xl font-semibold">Projects</h2>
                <select id="projectStatusFilter" class="px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                    <option value="">All Status</option>
                    <option value="in_progress">In Progress</option>
                    <option value="completed">Completed</option>
                    <option value="on_hold">On Hold</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            <div id="projectsList"></div>
        </div>

        <div id="invoicesTab" class="tab-content p-6 hidden">
            <div class="mb-4 flex justify-between items-center">
                <h2 class="text-xl font-semibold">Invoices</h2>
                <select id="invoiceStatusFilter" class="px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                    <option value="">All Status</option>
                    <option value="draft">Draft</option>
                    <option value="sent">Sent</option>
                    <option value="paid">Paid</option>
                    <option value="overdue">Overdue</option>
                </select>
            </div>
            <div id="invoicesList"></div>
        </div>

        <div id="timeTab" class="tab-content p-6 hidden">
            <div class="mb-4 flex justify-between items-center">
                <h2 class="text-xl font-semibold">Time Entries</h2>
                <button onclick="openTimeEntryModal()" class="btn bg-primary text-white px-4 py-2 rounded-lg">
                    <i data-lucide="plus" class="w-4 h-4 inline"></i> Log Time
                </button>
            </div>
            <div id="timeEntriesList"></div>
        </div>
    </div>
</div>

<div id="clientModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <h2 class="text-2xl font-bold mb-6">Add Client</h2>
            <form id="clientForm" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-2">Client Name *</label>
                        <input type="text" name="client_name" required class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Company Name</label>
                        <input type="text" name="company_name" class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-2">Email</label>
                        <input type="email" name="email" class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Phone</label>
                        <input type="tel" name="phone" class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Address</label>
                    <textarea name="address" rows="2" class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Notes</label>
                    <textarea name="notes" rows="3" class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600"></textarea>
                </div>
                <div class="flex gap-2 justify-end">
                    <button type="button" onclick="closeModal('clientModal')" class="px-4 py-2 border rounded-lg">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg">Save Client</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="/assets/js/freelance-tracker.js"></script>
<script>
if (typeof lucide !== 'undefined') lucide.createIcons();
</script>

<?php include 'includes/footer.php'; ?>
