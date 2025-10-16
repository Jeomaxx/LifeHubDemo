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
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php if ($auth->isLoggedIn()): ?>
    <div class="app-container">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-atlas"></i> <?php echo SITE_NAME; ?></h2>
                <button class="sidebar-toggle" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            
            <nav class="sidebar-nav">
                <a href="/dashboard.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                
                <a href="/assets.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'assets.php' ? 'active' : ''; ?>">
                    <i class="fas fa-box"></i>
                    <span>Assets</span>
                </a>
                
                <a href="/bills.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'bills.php' ? 'active' : ''; ?>">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <span>Bills</span>
                </a>
                
                <a href="/birthdays.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'birthdays.php' ? 'active' : ''; ?>">
                    <i class="fas fa-birthday-cake"></i>
                    <span>Birthdays</span>
                </a>
                
                <a href="/finance.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'finance.php' ? 'active' : ''; ?>">
                    <i class="fas fa-wallet"></i>
                    <span>Finance</span>
                </a>
                
                <a href="/goals.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'goals.php' ? 'active' : ''; ?>">
                    <i class="fas fa-bullseye"></i>
                    <span>Goals</span>
                </a>
                
                <a href="/habits.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'habits.php' ? 'active' : ''; ?>">
                    <i class="fas fa-check-circle"></i>
                    <span>Habits</span>
                </a>
                
                <a href="/health.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'health.php' ? 'active' : ''; ?>">
                    <i class="fas fa-heartbeat"></i>
                    <span>Health</span>
                </a>
                
                <a href="/hobbies.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'hobbies.php' ? 'active' : ''; ?>">
                    <i class="fas fa-palette"></i>
                    <span>Hobbies</span>
                </a>
                
                <a href="/investments.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'investments.php' ? 'active' : ''; ?>">
                    <i class="fas fa-chart-line"></i>
                    <span>Investments</span>
                </a>
                
                <a href="/journal.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'journal.php' ? 'active' : ''; ?>">
                    <i class="fas fa-book"></i>
                    <span>Journal</span>
                </a>
                
                <a href="/learning.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'learning.php' ? 'active' : ''; ?>">
                    <i class="fas fa-graduation-cap"></i>
                    <span>Learning</span>
                </a>
                
                <a href="/media.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'media.php' ? 'active' : ''; ?>">
                    <i class="fas fa-film"></i>
                    <span>Media</span>
                </a>
                
                <a href="/subscriptions.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'subscriptions.php' ? 'active' : ''; ?>">
                    <i class="fas fa-sync"></i>
                    <span>Subscriptions</span>
                </a>
                
                <a href="/tasks.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'tasks.php' ? 'active' : ''; ?>">
                    <i class="fas fa-tasks"></i>
                    <span>Tasks</span>
                </a>
                
                <a href="/crypto.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'crypto.php' ? 'active' : ''; ?>">
                    <i class="fas fa-bitcoin"></i>
                    <span>Cryptocurrency</span>
                </a>
                
                <div class="nav-divider"></div>
                
                <a href="/analytics.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'analytics.php' ? 'active' : ''; ?>">
                    <i class="fas fa-chart-bar"></i>
                    <span>Analytics</span>
                </a>
                
                <a href="/profile.php" class="nav-item">
                    <i class="fas fa-user-cog"></i>
                    <span>Profile</span>
                </a>
                
                <a href="/security_2fa.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'security_2fa.php' ? 'active' : ''; ?>">
                    <i class="fas fa-shield-alt"></i>
                    <span>2FA Security</span>
                </a>
                
                <a href="/backup.php" class="nav-item">
                    <i class="fas fa-database"></i>
                    <span>Backup</span>
                </a>
                
                <a href="/admin.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin.php' ? 'active' : ''; ?>">
                    <i class="fas fa-cog"></i>
                    <span>Administration</span>
                </a>
                
                <?php if ($auth->isAdmin()): ?>
                <a href="/admin.php" class="nav-item">
                    <i class="fas fa-user-shield"></i>
                    <span>Admin Panel</span>
                </a>
                <?php endif; ?>
            </nav>
            
            <div class="sidebar-footer">
                <div class="user-info">
                    <i class="fas fa-user-circle"></i>
                    <span><?php echo sanitize($currentUser['name']); ?></span>
                </div>
                <button class="theme-toggle" id="themeToggle">
                    <i class="fas fa-moon"></i>
                </button>
                <a href="/logout.php" class="logout-btn" title="Logout">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </aside>
        
        <main class="main-content">
            <div class="top-bar">
                <button class="mobile-toggle" id="mobileToggle">
                    <i class="fas fa-bars"></i>
                </button>
                
                <div class="search-bar">
                    <input type="text" id="globalSearch" placeholder="Search across all modules...">
                    <i class="fas fa-search"></i>
                </div>
                
                <div class="top-bar-actions">
                    <div class="notifications" id="notificationDropdown">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge" id="notificationCount">0</span>
                    </div>
                </div>
            </div>
            
            <div class="content-wrapper">
    <?php else: ?>
        <div class="auth-container">
    <?php endif; ?>
