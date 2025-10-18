<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$pageTitle = 'Shared Family Dashboard';
include 'includes/header.php';

// Get family members
$familyMembers = $db->fetchAll("SELECT * FROM family_members WHERE user_id = ? ORDER BY name", [$userId]) ?: [];

// Aggregate family data
$familyFinance = [];
$familyHealth = [];
$familyGoals = [];
$familyTasks = [];

foreach ($familyMembers as $member) {
    // Get member's finance data if they have a linked account
    $memberUserId = $member['linked_user_id'] ?? null;
    if ($memberUserId) {
        $balance = $db->fetchOne("SELECT SUM(CASE WHEN type = 'income' THEN amount ELSE -amount END) as balance FROM finance WHERE user_id = ?", [$memberUserId]);
        $familyFinance[$member['name']] = $balance['balance'] ?? 0;
        
        $health = $db->fetchOne("SELECT * FROM health WHERE user_id = ? ORDER BY date DESC LIMIT 1", [$memberUserId]);
        $familyHealth[$member['name']] = $health ?: null;
        
        $goals = $db->fetchAll("SELECT * FROM goals WHERE user_id = ? AND status = 'active'", [$memberUserId]);
        $familyGoals[$member['name']] = $goals ?: [];
    }
}

// Get shared household tasks
$householdTasks = $db->fetchAll("SELECT * FROM household_tasks WHERE user_id = ? AND status != 'completed' ORDER BY due_date", [$userId]) ?: [];
?>

<div class="container mx-auto px-4 py-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <i class="fas fa-home text-primary"></i>
                Shared Family Dashboard
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Overview of all family members' activities and metrics</p>
        </div>
        <a href="/family_manager.php" class="btn bg-primary hover:bg-primary-dark text-white px-6 py-2 rounded-lg flex items-center gap-2">
            <i class="fas fa-cog"></i>
            <span>Manage Family</span>
        </a>
    </div>

    <!-- Family Overview Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Family Members</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1"><?php echo count($familyMembers); ?></p>
                </div>
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-users text-blue-600 dark:text-blue-400 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Balance</p>
                    <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">
                        $<?php echo number_format(array_sum($familyFinance), 2); ?>
                    </p>
                </div>
                <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-wallet text-green-600 dark:text-green-400 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Household Tasks</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1"><?php echo count($householdTasks); ?></p>
                </div>
                <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-tasks text-purple-600 dark:text-purple-400 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Active Goals</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                        <?php echo array_sum(array_map('count', $familyGoals)); ?>
                    </p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-bullseye text-yellow-600 dark:text-yellow-400 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Family Members Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
        <?php foreach ($familyMembers as $member): ?>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white text-2xl font-bold">
                    <?php echo strtoupper(substr($member['name'], 0, 1)); ?>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white"><?php echo htmlspecialchars($member['name']); ?></h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400"><?php echo htmlspecialchars($member['relationship'] ?? 'Family'); ?></p>
                </div>
            </div>
            
            <div class="space-y-3">
                <?php if (isset($familyFinance[$member['name']])): ?>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        <i class="fas fa-wallet"></i> Balance
                    </span>
                    <span class="font-medium text-gray-900 dark:text-white">
                        $<?php echo number_format($familyFinance[$member['name']], 2); ?>
                    </span>
                </div>
                <?php endif; ?>
                
                <?php if (isset($familyGoals[$member['name']]) && !empty($familyGoals[$member['name']])): ?>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        <i class="fas fa-bullseye"></i> Goals
                    </span>
                    <span class="font-medium text-gray-900 dark:text-white">
                        <?php echo count($familyGoals[$member['name']]); ?> active
                    </span>
                </div>
                <?php endif; ?>
                
                <?php if (isset($familyHealth[$member['name']]) && $familyHealth[$member['name']]): ?>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        <i class="fas fa-heartbeat"></i> Health
                    </span>
                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">
                        Updated
                    </span>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Household Tasks -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
            <i class="fas fa-clipboard-list"></i> Household Tasks
        </h3>
        <div class="space-y-3">
            <?php if (empty($householdTasks)): ?>
            <p class="text-gray-500 dark:text-gray-400 text-center py-4">No pending household tasks</p>
            <?php else: ?>
            <?php foreach ($householdTasks as $task): ?>
            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <div class="flex items-center gap-3">
                    <input type="checkbox" class="w-5 h-5 text-primary rounded" onchange="completeHouseholdTask(<?php echo $task['id']; ?>)">
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white"><?php echo htmlspecialchars($task['title']); ?></p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Assigned to: <?php 
                            if ($task['assigned_to_member_id']) {
                                $assignee = array_filter($familyMembers, fn($m) => $m['id'] == $task['assigned_to_member_id']);
                                echo $assignee ? current($assignee)['name'] : 'Unassigned';
                            } else {
                                echo 'Unassigned';
                            }
                            ?>
                        </p>
                    </div>
                </div>
                <?php if ($task['due_date']): ?>
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    Due: <?php echo date('M d', strtotime($task['due_date'])); ?>
                </span>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Family Events & Calendar -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
            <i class="fas fa-calendar"></i> Upcoming Family Events
        </h3>
        <div id="familyEvents" class="space-y-3">
            <p class="text-gray-500 dark:text-gray-400 text-center py-4">No upcoming events</p>
        </div>
    </div>
</div>

<script>
async function completeHouseholdTask(taskId) {
    try {
        const response = await fetch('/api/family.php', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                type: 'task',
                id: taskId,
                status: 'completed'
            })
        });
        
        const result = await response.json();
        if (result.success) {
            location.reload();
        }
    } catch (error) {
        console.error('Error:', error);
    }
}
</script>

<?php include 'includes/footer.php'; ?>
