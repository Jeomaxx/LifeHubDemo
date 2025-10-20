<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$pageTitle = 'Tax Reports & Documents';
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <i data-lucide="receipt" class="text-primary"></i>
                Tax Reports & Documents
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Organize receipts, track deductions, and generate tax reports</p>
        </div>
        <div class="flex gap-2">
            <button onclick="openCategoryModal()" class="btn bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                <i data-lucide="folder-plus" class="w-4 h-4 inline"></i> Add Category
            </button>
            <button onclick="openDocumentModal()" class="btn bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
                <i data-lucide="file-plus" class="w-4 h-4 inline"></i> Upload Document
            </button>
            <button onclick="generateReport()" class="btn bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-lg">
                <i data-lucide="file-text" class="w-4 h-4 inline"></i> Generate Report
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Income (YTD)</p>
                    <p id="totalIncome" class="text-2xl font-bold text-green-600 mt-1">$0</p>
                </div>
                <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                    <i data-lucide="trending-up" class="text-green-600 w-6 h-6"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Deductible Expenses</p>
                    <p id="totalDeductions" class="text-2xl font-bold text-blue-600 mt-1">$0</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                    <i data-lucide="minus-circle" class="text-blue-600 w-6 h-6"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Tax Documents</p>
                    <p id="totalDocuments" class="text-2xl font-bold text-purple-600 mt-1">0</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                    <i data-lucide="file" class="text-purple-600 w-6 h-6"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Estimated Tax</p>
                    <p id="estimatedTax" class="text-2xl font-bold text-orange-600 mt-1">$0</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900 rounded-lg flex items-center justify-center">
                    <i data-lucide="calculator" class="text-orange-600 w-6 h-6"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold">Tax Documents</h2>
                <select id="yearFilter" class="px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                    <option value="2025">2025</option>
                    <option value="2024">2024</option>
                    <option value="2023">2023</option>
                </select>
            </div>
            <div id="documentsList"></div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Tax Categories</h2>
            <div id="categoriesList"></div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold mb-4">Generated Reports</h2>
        <div id="reportsList"></div>
    </div>
</div>

<script src="/assets/js/tax-reports.js"></script>
<script>
if (typeof lucide !== 'undefined') lucide.createIcons();
</script>

<?php include 'includes/footer.php'; ?>
