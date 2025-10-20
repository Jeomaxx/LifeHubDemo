<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$pageTitle = 'Unified Search';
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3 mb-4">
            <i data-lucide="search" class="text-primary"></i>
            Unified Semantic Search
        </h1>
        <p class="text-gray-600 dark:text-gray-400">AI-powered search across all your life data: tasks, notes, finances, health, and more</p>
    </div>

    <div class="bg-gradient-to-r from-primary to-blue-600 rounded-lg p-8 mb-6 text-white">
        <div class="max-w-3xl mx-auto">
            <h2 class="text-2xl font-bold mb-4">What would you like to find?</h2>
            <div class="flex gap-4">
                <input type="text" id="globalSearch" placeholder="Try: 'Show my spending on groceries last month' or 'Find notes about productivity'" 
                    class="flex-1 px-6 py-4 rounded-lg text-gray-900 text-lg" autofocus>
                <button onclick="performGlobalSearch()" class="btn bg-white text-primary px-8 py-4 rounded-lg font-semibold hover:bg-gray-100">
                    Search
                </button>
            </div>
            <div class="mt-4 flex flex-wrap gap-2">
                <span class="text-sm opacity-90">Try:</span>
                <button onclick="quickSearch('health data last week')" class="text-sm bg-white/20 px-3 py-1 rounded hover:bg-white/30">"health data last week"</button>
                <button onclick="quickSearch('bills due this month')" class="text-sm bg-white/20 px-3 py-1 rounded hover:bg-white/30">"bills due this month"</button>
                <button onclick="quickSearch('completed goals')" class="text-sm bg-white/20 px-3 py-1 rounded hover:bg-white/30">"completed goals"</button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <button onclick="filterByModule('tasks')" class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-md transition">
            <i data-lucide="check-circle" class="w-6 h-6 text-blue-600 mb-2"></i>
            <div class="font-semibold">Tasks</div>
            <div class="text-sm text-gray-600">Search tasks & projects</div>
        </button>
        <button onclick="filterByModule('finance')" class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-md transition">
            <i data-lucide="dollar-sign" class="w-6 h-6 text-green-600 mb-2"></i>
            <div class="font-semibold">Finance</div>
            <div class="text-sm text-gray-600">Transactions & budgets</div>
        </button>
        <button onclick="filterByModule('notes')" class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-md transition">
            <i data-lucide="file-text" class="w-6 h-6 text-purple-600 mb-2"></i>
            <div class="font-semibold">Notes</div>
            <div class="text-sm text-gray-600">All your notes</div>
        </button>
        <button onclick="filterByModule('health')" class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-md transition">
            <i data-lucide="heart" class="w-6 h-6 text-red-600 mb-2"></i>
            <div class="font-semibold">Health</div>
            <div class="text-sm text-gray-600">Health & wellness data</div>
        </button>
    </div>

    <div id="searchResults" class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
        <div class="text-center text-gray-500 py-12">
            <i data-lucide="search" class="w-16 h-16 mx-auto mb-4 text-gray-400"></i>
            <h3 class="text-lg font-semibold mb-2">Start searching</h3>
            <p>Use the search bar above to find anything across all your data</p>
        </div>
    </div>
</div>

<script src="/assets/js/unified-search.js"></script>
<script>
if (typeof lucide !== 'undefined') lucide.createIcons();
</script>

<?php include 'includes/footer.php'; ?>
