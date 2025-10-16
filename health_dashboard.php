<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$pageTitle = 'Health Dashboard';
include 'includes/header.php';

// Get health stats
$healthStats = $db->fetchOne("SELECT * FROM health WHERE user_id = ? ORDER BY created_at DESC LIMIT 1", [$userId]) ?? [];
$habitCount = $db->fetchColumn("SELECT COUNT(*) FROM habits WHERE user_id = ?", [$userId]) ?? 0;
?>

<div class="container mx-auto px-4 py-6">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <i class="fas fa-heartbeat text-red-500"></i>
                Health Dashboard
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Monitor your health and wellness metrics</p>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Active Habits</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1"><?php echo $habitCount; ?></p>
                </div>
                <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600 dark:text-green-400 text-xl"></i>
                </div>
            </div>
        </div>

        <a href="/gym.php" class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Gym Routines</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">-</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-dumbbell text-blue-600 dark:text-blue-400 text-xl"></i>
                </div>
            </div>
        </a>

        <a href="/diet.php" class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Diet Plans</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">-</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-utensils text-orange-600 dark:text-orange-400 text-xl"></i>
                </div>
            </div>
        </a>

        <a href="/water.php" class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Water Intake</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">-</p>
                </div>
                <div class="w-12 h-12 bg-cyan-100 dark:bg-cyan-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-tint text-cyan-600 dark:text-cyan-400 text-xl"></i>
                </div>
            </div>
        </a>
    </div>

    <!-- Health Modules Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Habits Module -->
        <a href="/habits.php" class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm hover:shadow-lg transition-all border-l-4 border-green-500">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check-double text-green-600 dark:text-green-400 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Habits</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Track daily habits</p>
                </div>
            </div>
        </a>

        <!-- Health Records -->
        <a href="/health.php" class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm hover:shadow-lg transition-all border-l-4 border-red-500">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 bg-red-100 dark:bg-red-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-heartbeat text-red-600 dark:text-red-400 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Health Records</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Medical history & vitals</p>
                </div>
            </div>
        </a>

        <!-- Gym Routines -->
        <a href="/gym.php" class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm hover:shadow-lg transition-all border-l-4 border-blue-500">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-dumbbell text-blue-600 dark:text-blue-400 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Gym Routines</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Workout tracking</p>
                </div>
            </div>
        </a>

        <!-- Diet Plans -->
        <a href="/diet.php" class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm hover:shadow-lg transition-all border-l-4 border-orange-500">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-utensils text-orange-600 dark:text-orange-400 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Diet Plans</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Meal planning & nutrition</p>
                </div>
            </div>
        </a>

        <!-- Water Tracker -->
        <a href="/water.php" class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm hover:shadow-lg transition-all border-l-4 border-cyan-500">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 bg-cyan-100 dark:bg-cyan-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-tint text-cyan-600 dark:text-cyan-400 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Water Tracker</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Hydration monitoring</p>
                </div>
            </div>
        </a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
