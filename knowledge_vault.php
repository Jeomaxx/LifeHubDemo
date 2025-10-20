<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$pageTitle = 'Knowledge Vault';
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <i data-lucide="brain" class="text-primary"></i>
                Knowledge Vault
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">AI-curated personal knowledge library with semantic search</p>
        </div>
        <button onclick="openAddKnowledgeModal()" class="btn bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-lg flex items-center gap-2">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Add Knowledge</span>
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Items</p>
                    <p id="totalItems" class="text-2xl font-bold text-gray-900 dark:text-white mt-1">0</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                    <i data-lucide="database" class="text-blue-600 w-6 h-6"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Categories</p>
                    <p id="totalCategories" class="text-2xl font-bold text-gray-900 dark:text-white mt-1">0</p>
                </div>
                <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                    <i data-lucide="folder" class="text-green-600 w-6 h-6"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Favorites</p>
                    <p id="totalFavorites" class="text-2xl font-bold text-gray-900 dark:text-white mt-1">0</p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900 rounded-lg flex items-center justify-center">
                    <i data-lucide="star" class="text-yellow-600 w-6 h-6"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Connections</p>
                    <p id="totalConnections" class="text-2xl font-bold text-gray-900 dark:text-white mt-1">0</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                    <i data-lucide="git-branch" class="text-purple-600 w-6 h-6"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4">
            <div class="flex gap-4 items-center">
                <div class="flex-1">
                    <input type="text" id="semanticSearch" placeholder="Search your knowledge... (try: 'productivity tips' or 'finance strategies')" 
                        class="w-full px-4 py-3 border rounded-lg dark:bg-gray-700 dark:border-gray-600 text-lg">
                </div>
                <button onclick="performSemanticSearch()" class="btn bg-primary text-white px-6 py-3 rounded-lg">
                    <i data-lucide="search" class="w-5 h-5 inline"></i> Search
                </button>
            </div>
            <div class="mt-2 flex gap-2">
                <span class="text-xs text-gray-500">Quick filters:</span>
                <button onclick="filterByType('article')" class="text-xs px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded">Articles</button>
                <button onclick="filterByType('note')" class="text-xs px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded">Notes</button>
                <button onclick="filterByType('link')" class="text-xs px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded">Links</button>
                <button onclick="showFavorites()" class="text-xs px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded">Favorites</button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Knowledge Items</h2>
            <div id="knowledgeList"></div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Categories</h2>
            <div id="categoriesList"></div>
            
            <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                <h3 class="font-semibold mb-2 text-blue-900 dark:text-blue-200">AI Features</h3>
                <ul class="text-sm space-y-2 text-blue-800 dark:text-blue-300">
                    <li class="flex items-start gap-2">
                        <i data-lucide="check" class="w-4 h-4 mt-0.5"></i>
                        <span>Auto-summarization</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i data-lucide="check" class="w-4 h-4 mt-0.5"></i>
                        <span>Keyword extraction</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i data-lucide="check" class="w-4 h-4 mt-0.5"></i>
                        <span>Semantic connections</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i data-lucide="check" class="w-4 h-4 mt-0.5"></i>
                        <span>Smart recommendations</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script src="/assets/js/knowledge-vault.js"></script>
<script>
if (typeof lucide !== 'undefined') lucide.createIcons();
</script>

<?php include 'includes/footer.php'; ?>
