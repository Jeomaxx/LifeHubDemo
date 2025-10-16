<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/auth.php';
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
    
    <!-- Main Content Area -->
    <div class="sm:ml-72 transition-all duration-300">
        <main class="p-4 sm:p-6 min-h-screen">
    <?php else: ?>
        <div class="auth-container">
    <?php endif; ?>
