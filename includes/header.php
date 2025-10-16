<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/i18n.php';
I18n::init($_SESSION['language'] ?? 'en');
$auth = new Auth();
$currentUser = $auth->getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo $auth->generateCSRFToken(); ?>">
    <title><?php echo (isset($pageTitle) ? $pageTitle . ' - ' : ''); ?><?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#3b82f6',
                        secondary: '#6366f1',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 dark:bg-gray-900">
    <?php if ($auth->isLoggedIn()): ?>
    <?php require_once __DIR__ . '/sidebar.php'; ?>
    
    <!-- Global Search -->
    <div id="globalSearchModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-start justify-center pt-20">
        <div class="bg-white dark:bg-gray-800 rounded-lg w-full max-w-2xl mx-4 shadow-2xl">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-3">
                    <i class="fas fa-search text-gray-400"></i>
                    <input type="text" id="globalSearchInput" placeholder="Search across all modules..." 
                        class="flex-1 bg-transparent border-none focus:ring-0 text-gray-900 dark:text-white" 
                        autocomplete="off">
                    <button onclick="closeGlobalSearch()" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div id="globalSearchResults" class="max-h-96 overflow-y-auto p-4"></div>
        </div>
    </div>
    
    <!-- Main Content Area -->
    <div class="sm:ml-72 transition-all duration-300">
        <!-- Top Bar with Global Search -->
        <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-3 mb-6">
            <div class="flex items-center justify-between">
                <button onclick="openGlobalSearch()" class="flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600">
                    <i class="fas fa-search text-gray-500"></i>
                    <span class="text-gray-600 dark:text-gray-400 text-sm">Search... (Ctrl+K)</span>
                </button>
                <div class="flex items-center gap-4">
                    <a href="/notifications.php" class="relative">
                        <i class="fas fa-bell text-gray-600 dark:text-gray-400 text-xl"></i>
                        <span id="notifBadge" class="hidden absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center"></span>
                    </a>
                </div>
            </div>
        </div>
        <main class="p-4 sm:p-6 min-h-screen">
    <?php else: ?>
        <div class="auth-container">
    <?php endif; ?>
