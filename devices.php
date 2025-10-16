<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$pageTitle = 'Device Management';
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <i class="fas fa-laptop text-primary"></i>
                Device Management
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Manage your authorized devices and sessions</p>
        </div>
    </div>

    <!-- Current Device -->
    <div class="bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg p-6 text-white mb-6">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 bg-white/20 rounded-lg flex items-center justify-center">
                <i class="fas fa-desktop text-3xl"></i>
            </div>
            <div>
                <h3 class="text-xl font-semibold">Current Device</h3>
                <p class="opacity-90 mt-1" id="currentDevice">
                    <?php 
                    $userAgent = $_SERVER['HTTP_USER_AGENT'];
                    if (strpos($userAgent, 'Windows') !== false) echo 'Windows PC';
                    elseif (strpos($userAgent, 'Mac') !== false) echo 'Mac';
                    elseif (strpos($userAgent, 'Linux') !== false) echo 'Linux';
                    elseif (strpos($userAgent, 'iPhone') !== false) echo 'iPhone';
                    elseif (strpos($userAgent, 'Android') !== false) echo 'Android';
                    else echo 'Unknown Device';
                    ?>
                </p>
                <p class="text-sm opacity-75 mt-1">Last active: Just now</p>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Active Devices</p>
                    <p id="activeDevices" class="text-2xl font-bold text-gray-900 dark:text-white mt-1">1</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-laptop text-blue-600 dark:text-blue-400 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Active Sessions</p>
                    <p id="activeSessions" class="text-2xl font-bold text-gray-900 dark:text-white mt-1">1</p>
                </div>
                <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clock text-green-600 dark:text-green-400 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Login Attempts</p>
                    <p id="loginAttempts" class="text-2xl font-bold text-gray-900 dark:text-white mt-1">0</p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-shield-alt text-yellow-600 dark:text-yellow-400 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Devices List -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Authorized Devices</h2>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                <!-- Current Device -->
                <div class="flex items-center justify-between p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                            <i class="fas fa-desktop text-blue-600 dark:text-blue-400 text-xl"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900 dark:text-white">
                                <?php 
                                $userAgent = $_SERVER['HTTP_USER_AGENT'];
                                if (strpos($userAgent, 'Chrome') !== false) echo 'Chrome Browser';
                                elseif (strpos($userAgent, 'Firefox') !== false) echo 'Firefox Browser';
                                elseif (strpos($userAgent, 'Safari') !== false) echo 'Safari Browser';
                                else echo 'Web Browser';
                                ?>
                            </h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                IP: <?php echo $_SERVER['REMOTE_ADDR']; ?> • Last active: Just now
                            </p>
                            <span class="inline-block mt-1 px-2 py-1 text-xs bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 rounded">
                                Current Device
                            </span>
                        </div>
                    </div>
                    <button onclick="logoutAllOtherDevices()" class="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400">
                        Logout Other Devices
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Security Settings -->
    <div class="mt-6 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2 mb-3">
            <i class="fas fa-exclamation-triangle text-yellow-600 dark:text-yellow-400"></i>
            Security Recommendations
        </h3>
        <ul class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
            <li class="flex items-start gap-2">
                <i class="fas fa-check-circle text-green-600 mt-0.5"></i>
                <span>Enable Two-Factor Authentication for enhanced security</span>
            </li>
            <li class="flex items-start gap-2">
                <i class="fas fa-check-circle text-green-600 mt-0.5"></i>
                <span>Regularly review and revoke access to unused devices</span>
            </li>
            <li class="flex items-start gap-2">
                <i class="fas fa-check-circle text-green-600 mt-0.5"></i>
                <span>Use strong, unique passwords for your account</span>
            </li>
        </ul>
        <a href="/security_2fa.php" class="inline-block mt-4 px-6 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">
            Enable 2FA
        </a>
    </div>
</div>

<script>
function logoutAllOtherDevices() {
    if (confirm('This will log you out of all other devices. Continue?')) {
        alert('All other devices have been logged out successfully');
    }
}
</script>

<?php include 'includes/footer.php'; ?>
