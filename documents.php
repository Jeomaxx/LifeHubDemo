<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$pageTitle = 'Documents Hub';
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <i class="fas fa-folder-open text-primary"></i>
                Documents Hub
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Organize and manage your documents</p>
        </div>
        <button onclick="openUploadModal()" class="btn bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-lg flex items-center gap-2">
            <i class="fas fa-upload"></i>
            <span>Upload Document</span>
        </button>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Documents</p>
                    <p id="totalDocs" class="text-2xl font-bold text-gray-900 dark:text-white mt-1">0</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-file text-blue-600 dark:text-blue-400 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Storage Used</p>
                    <p id="storageUsed" class="text-2xl font-bold text-gray-900 dark:text-white mt-1">0 MB</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-hdd text-purple-600 dark:text-purple-400 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Categories</p>
                    <p id="categoryCount" class="text-2xl font-bold text-gray-900 dark:text-white mt-1">0</p>
                </div>
                <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-tags text-green-600 dark:text-green-400 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Recent Uploads</p>
                    <p id="recentUploads" class="text-2xl font-bold text-gray-900 dark:text-white mt-1">0</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clock text-orange-600 dark:text-orange-400 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Documents Grid -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Your Documents</h2>
            <div class="flex gap-2">
                <input type="text" placeholder="Search documents..." class="px-4 py-2 border border-gray-300 rounded-lg" id="searchDocs">
                <select class="px-4 py-2 border border-gray-300 rounded-lg" id="filterCategory">
                    <option value="">All Categories</option>
                    <option value="Personal">Personal</option>
                    <option value="Financial">Financial</option>
                    <option value="Legal">Legal</option>
                    <option value="Medical">Medical</option>
                </select>
            </div>
        </div>
        <div id="documentsList" class="p-6">
            <div class="text-center py-12 text-gray-500 dark:text-gray-400">
                <i class="fas fa-folder-open text-4xl mb-3"></i>
                <p>No documents yet. Upload your first document to get started.</p>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
