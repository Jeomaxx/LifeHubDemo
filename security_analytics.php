<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$pageTitle = 'Security Analytics';
include 'includes/header.php';

// Get security stats
$loginAttempts = $db->fetchAll("SELECT * FROM security_logs WHERE user_id = ? AND event_type = 'login_attempt' ORDER BY created_at DESC LIMIT 20", [$userId]) ?: [];
$failedLogins = $db->fetchAll("SELECT * FROM security_logs WHERE user_id = ? AND event_type = 'failed_login' ORDER BY created_at DESC LIMIT 10", [$userId]) ?: [];
$recentActivity = $db->fetchAll("SELECT * FROM security_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 30", [$userId]) ?: [];

// Count recent failed attempts
$recentFailedCount = $db->fetchColumn("SELECT COUNT(*) FROM security_logs WHERE user_id = ? AND event_type = 'failed_login' AND created_at >= NOW() - INTERVAL '24 hours'", [$userId]) ?: 0;

// Get unique IPs
$uniqueIPs = $db->fetchAll("SELECT DISTINCT ip_address, COUNT(*) as count, MAX(created_at) as last_seen FROM security_logs WHERE user_id = ? GROUP BY ip_address ORDER BY last_seen DESC LIMIT 10", [$userId]) ?: [];
?>

<div class="container mx-auto px-4 py-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <i class="fas fa-shield-alt text-primary"></i>
                Security Analytics
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Monitor login attempts, devices, and security events</p>
        </div>
    </div>

    <!-- Security Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Logins</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1"><?php echo count($loginAttempts); ?></p>
                </div>
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-sign-in-alt text-blue-600 dark:text-blue-400 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Failed Attempts (24h)</p>
                    <p class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1"><?php echo $recentFailedCount; ?></p>
                </div>
                <div class="w-12 h-12 bg-red-100 dark:bg-red-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-red-600 dark:text-red-400 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Unique IPs</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1"><?php echo count($uniqueIPs); ?></p>
                </div>
                <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-globe text-purple-600 dark:text-purple-400 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Security Score</p>
                    <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1" id="securityScore">-</p>
                </div>
                <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600 dark:text-green-400 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Anomaly Alerts -->
    <?php if ($recentFailedCount > 3): ?>
    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 mb-6">
        <div class="flex items-center gap-3">
            <i class="fas fa-exclamation-circle text-red-600 dark:text-red-400 text-2xl"></i>
            <div>
                <h3 class="font-semibold text-red-900 dark:text-red-100">Security Alert</h3>
                <p class="text-sm text-red-700 dark:text-red-300">Multiple failed login attempts detected in the last 24 hours</p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Recent Activity Timeline -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
            <i class="fas fa-clock"></i> Recent Activity
        </h3>
        <div class="space-y-3">
            <?php foreach ($recentActivity as $activity): ?>
            <div class="flex items-start gap-4 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <div class="w-10 h-10 rounded-full bg-<?php echo $activity['event_type'] == 'failed_login' ? 'red' : 'blue'; ?>-100 dark:bg-<?php echo $activity['event_type'] == 'failed_login' ? 'red' : 'blue'; ?>-900 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-<?php echo $activity['event_type'] == 'failed_login' ? 'times' : 'check'; ?> text-<?php echo $activity['event_type'] == 'failed_login' ? 'red' : 'blue'; ?>-600"></i>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                        <?php echo ucwords(str_replace('_', ' ', $activity['event_type'])); ?>
                    </p>
                    <p class="text-xs text-gray-600 dark:text-gray-400">
                        IP: <?php echo htmlspecialchars($activity['ip_address']); ?> • 
                        <?php echo htmlspecialchars($activity['user_agent']); ?>
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                        <?php echo date('M d, Y H:i:s', strtotime($activity['created_at'])); ?>
                    </p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- IP Address Analysis -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
            <i class="fas fa-map-marker-alt"></i> IP Address Analysis
        </h3>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700 dark:text-gray-300">IP Address</th>
                        <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700 dark:text-gray-300">Requests</th>
                        <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700 dark:text-gray-300">Last Seen</th>
                        <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700 dark:text-gray-300">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($uniqueIPs as $ip): ?>
                    <tr class="border-b border-gray-100 dark:border-gray-700">
                        <td class="py-3 px-4 text-sm text-gray-900 dark:text-white"><?php echo htmlspecialchars($ip['ip_address']); ?></td>
                        <td class="py-3 px-4 text-sm text-gray-600 dark:text-gray-400"><?php echo $ip['count']; ?></td>
                        <td class="py-3 px-4 text-sm text-gray-600 dark:text-gray-400"><?php echo date('M d, H:i', strtotime($ip['last_seen'])); ?></td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">
                                Trusted
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Calculate security score
function calculateSecurityScore() {
    const has2FA = <?php echo json_encode($db->fetchOne("SELECT totp_enabled FROM users WHERE id = ?", [$userId])['totp_enabled'] ?? false); ?>;
    const failedAttempts = <?php echo $recentFailedCount; ?>;
    const uniqueIPs = <?php echo count($uniqueIPs); ?>;
    
    let score = 100;
    
    if (!has2FA) score -= 30;
    if (failedAttempts > 0) score -= (failedAttempts * 5);
    if (uniqueIPs > 5) score -= 10;
    
    score = Math.max(0, Math.min(100, score));
    
    document.getElementById('securityScore').textContent = score + '/100';
    document.getElementById('securityScore').className = score >= 80 ? 'text-2xl font-bold text-green-600 dark:text-green-400 mt-1' : 
                                                           score >= 60 ? 'text-2xl font-bold text-yellow-600 dark:text-yellow-400 mt-1' : 
                                                           'text-2xl font-bold text-red-600 dark:text-red-400 mt-1';
}

calculateSecurityScore();
</script>

<?php include 'includes/footer.php'; ?>
