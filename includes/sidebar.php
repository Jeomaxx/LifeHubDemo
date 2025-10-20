<?php
// Load required functions
require_once __DIR__ . '/functions.php';

// Get notification counts and stats for badges
$db = Database::getInstance();
$userId = $auth->getUserId();

$pendingTasks = $db->fetchColumn("SELECT COUNT(*) FROM tasks WHERE user_id = ? AND status != 'completed'", [$userId]) ?? 0;
$pendingBills = $db->fetchColumn("SELECT COUNT(*) FROM bills WHERE user_id = ? AND payment_status = 'pending'", [$userId]) ?? 0;
$overdueBills = $db->fetchColumn("SELECT COUNT(*) FROM bills WHERE user_id = ? AND payment_status != 'paid' AND due_date < CURRENT_DATE", [$userId]) ?? 0;
$unreadNotifications = $db->fetchColumn("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = FALSE", [$userId]) ?? 0;
$activeGoals = $db->fetchColumn("SELECT COUNT(*) FROM goals WHERE user_id = ? AND status = 'active'", [$userId]) ?? 0;

$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!-- Modern Sidebar with Tailwind CSS -->
<aside id="sidebar" class="fixed top-0 left-0 z-40 w-72 h-screen transition-transform -translate-x-full sm:translate-x-0 bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-700">
    <div class="h-full px-3 py-4 overflow-y-auto">
        <!-- Sidebar Header -->
        <div class="flex items-center justify-between mb-5 px-2">
            <div class="flex items-center gap-2">
                <i class="fas fa-atlas text-primary text-2xl"></i>
                <span class="text-xl font-bold text-gray-800 dark:text-white">Life Atlas</span>
            </div>
            <button id="closeSidebar" class="sm:hidden text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Search Bar -->
        <div class="mb-4">
            <div class="relative">
                <input 
                    type="text" 
                    id="sidebarSearch" 
                    placeholder="Search modules..." 
                    class="w-full px-4 py-2 pl-10 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                >
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
            </div>
        </div>

        <!-- Navigation Menu -->
        <nav class="space-y-2">
            <!-- Dashboard -->
            <a href="/dashboard.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 <?php echo $currentPage == 'dashboard.php' ? 'bg-primary/10 text-primary' : 'text-gray-700 dark:text-gray-300'; ?>">
                <i class="fas fa-home w-5"></i>
                <span class="font-medium">Dashboard</span>
            </a>

            <!-- Finances Category -->
            <div class="sidebar-category">
                <button class="category-toggle w-full flex items-center justify-between px-3 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-300">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-wallet w-5"></i>
                        <span class="font-medium">Finances</span>
                    </div>
                    <i class="fas fa-chevron-down text-xs transition-transform duration-200"></i>
                </button>
                <div class="category-items pl-11 mt-1 space-y-1 hidden">
                    <a href="/finance.php" class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm <?php echo $currentPage == 'finance.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 dark:text-gray-400'; ?>">
                        <span>Transactions</span>
                        <button class="quick-add-btn" data-module="finance" title="Add Transaction">
                            <i class="fas fa-plus text-xs"></i>
                        </button>
                    </a>
                    <a href="/accounts.php" class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm <?php echo $currentPage == 'accounts.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 dark:text-gray-400'; ?>">
                        <span>Accounts</span>
                        <button class="quick-add-btn" data-module="account">
                            <i class="fas fa-plus text-xs"></i>
                        </button>
                    </a>
                    <a href="/budgets.php" class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm <?php echo $currentPage == 'budgets.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 dark:text-gray-400'; ?>">
                        <span>Budgets</span>
                        <button class="quick-add-btn" data-module="budget">
                            <i class="fas fa-plus text-xs"></i>
                        </button>
                    </a>
                    <a href="/investments.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm <?php echo $currentPage == 'investments.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 dark:text-gray-400'; ?>">
                        <span>Investments</span>
                    </a>
                    <a href="/crypto.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm <?php echo $currentPage == 'crypto.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 dark:text-gray-400'; ?>">
                        <span>Cryptocurrency</span>
                    </a>
                    <a href="/bills.php" class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm <?php echo $currentPage == 'bills.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 dark:text-gray-400'; ?>">
                        <div class="flex items-center gap-2">
                            <span>Bills & Payments</span>
                            <?php if ($overdueBills > 0): ?>
                                <span class="bg-red-500 text-white text-xs px-1.5 py-0.5 rounded-full" title="Overdue bills"><?php echo $overdueBills; ?></span>
                            <?php endif; ?>
                        </div>
                        <button class="quick-add-btn" data-module="bill" title="Add Bill">
                            <i class="fas fa-plus text-xs"></i>
                        </button>
                    </a>
                    <a href="/debts.php" class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm <?php echo $currentPage == 'debts.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 dark:text-gray-400'; ?>">
                        <span>Debt Payoff Planner</span>
                        <button class="quick-add-btn" data-module="debt">
                            <i class="fas fa-plus text-xs"></i>
                        </button>
                    </a>
                </div>
            </div>

            <!-- Tasks & Projects Category -->
            <div class="sidebar-category">
                <button class="category-toggle w-full flex items-center justify-between px-3 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-300">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-tasks w-5"></i>
                        <span class="font-medium">Tasks & Projects</span>
                        <?php if ($pendingTasks > 0): ?>
                        <span class="bg-primary text-white text-xs px-2 py-0.5 rounded-full"><?php echo $pendingTasks; ?></span>
                        <?php endif; ?>
                    </div>
                    <i class="fas fa-chevron-down text-xs transition-transform duration-200"></i>
                </button>
                <div class="category-items pl-11 mt-1 space-y-1 hidden">
                    <a href="/tasks.php" class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm <?php echo $currentPage == 'tasks.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 dark:text-gray-400'; ?>">
                        <span>All Tasks</span>
                        <button class="quick-add-btn" data-module="task">
                            <i class="fas fa-plus text-xs"></i>
                        </button>
                    </a>
                    <a href="/kanban.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm <?php echo $currentPage == 'kanban.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 dark:text-gray-400'; ?>">
                        <span>Kanban Board</span>
                    </a>
                    <a href="/career_center.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm <?php echo $currentPage == 'career_center.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 dark:text-gray-400'; ?>">
                        <span><i class="fas fa-briefcase"></i> Career Center</span>
                    </a>
                    <a href="/pomodoro.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm <?php echo $currentPage == 'pomodoro.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 dark:text-gray-400'; ?>">
                        <span>Pomodoro Timer</span>
                    </a>
                </div>
            </div>

            <!-- Goals & Habits Category -->
            <div class="sidebar-category">
                <button class="category-toggle w-full flex items-center justify-between px-3 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-300">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-bullseye w-5"></i>
                        <span class="font-medium">Goals & Habits</span>
                        <?php if ($activeGoals > 0): ?>
                        <span class="bg-green-500 text-white text-xs px-2 py-0.5 rounded-full"><?php echo $activeGoals; ?></span>
                        <?php endif; ?>
                    </div>
                    <i class="fas fa-chevron-down text-xs transition-transform duration-200"></i>
                </button>
                <div class="category-items pl-11 mt-1 space-y-1 hidden">
                    <a href="/goals.php" class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm <?php echo $currentPage == 'goals.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 dark:text-gray-400'; ?>">
                        <span>Goals</span>
                        <button class="quick-add-btn" data-module="goal">
                            <i class="fas fa-plus text-xs"></i>
                        </button>
                    </a>
                    <a href="/habits.php" class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm <?php echo $currentPage == 'habits.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 dark:text-gray-400'; ?>">
                        <span>Habits</span>
                        <button class="quick-add-btn" data-module="habit">
                            <i class="fas fa-plus text-xs"></i>
                        </button>
                    </a>
                </div>
            </div>

            <!-- Health & Wellness Category -->
            <div class="sidebar-category">
                <button class="category-toggle w-full flex items-center justify-between px-3 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-300">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-heartbeat w-5"></i>
                        <span class="font-medium">Health & Wellness</span>
                    </div>
                    <i class="fas fa-chevron-down text-xs transition-transform duration-200"></i>
                </button>
                <div class="category-items pl-11 mt-1 space-y-1 hidden">
                    <a href="/health_dashboard.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm <?php echo $currentPage == 'health_dashboard.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 dark:text-gray-400'; ?>">
                        <span>Health Dashboard</span>
                    </a>
                    <a href="/gym.php" class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm <?php echo $currentPage == 'gym.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 dark:text-gray-400'; ?>">
                        <span>Gym Routines</span>
                        <button class="quick-add-btn" data-module="gym">
                            <i class="fas fa-plus text-xs"></i>
                        </button>
                    </a>
                    <a href="/mood_tracker.php" class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm <?php echo $currentPage == 'mood_tracker.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 dark:text-gray-400'; ?>">
                        <span>Mood Tracker</span>
                        <button class="quick-add-btn" data-module="mood">
                            <i class="fas fa-plus text-xs"></i>
                        </button>
                    </a>
                    <a href="/diet.php" class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm <?php echo $currentPage == 'diet.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 dark:text-gray-400'; ?>">
                        <span>Diet Plans</span>
                        <button class="quick-add-btn" data-module="diet">
                            <i class="fas fa-plus text-xs"></i>
                        </button>
                    </a>
                    <a href="/water.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm <?php echo $currentPage == 'water.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 dark:text-gray-400'; ?>">
                        <span>Water Tracker</span>
                    </a>
                    <a href="/mindfulness_sleep.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm <?php echo $currentPage == 'mindfulness_sleep.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 dark:text-gray-400'; ?>">
                        <span><i class="fas fa-spa"></i> Mindfulness Hub</span>
                    </a>
                    <a href="/sleep_tracking.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm <?php echo $currentPage == 'sleep_tracking.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 dark:text-gray-400'; ?>">
                        <span><i class="fas fa-moon"></i> Sleep Tracker</span>
                    </a>
                    <a href="/health.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm <?php echo $currentPage == 'health.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 dark:text-gray-400'; ?>">
                        <span>Health Records</span>
                    </a>
                    <a href="/medications.php" class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm <?php echo $currentPage == 'medications.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 dark:text-gray-400'; ?>">
                        <span>Medications</span>
                        <button class="quick-add-btn" data-module="medication">
                            <i class="fas fa-plus text-xs"></i>
                        </button>
                    </a>
                    <a href="/symptoms.php" class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm <?php echo $currentPage == 'symptoms.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 dark:text-gray-400'; ?>">
                        <span>Symptom Tracker</span>
                        <button class="quick-add-btn" data-module="symptom">
                            <i class="fas fa-plus text-xs"></i>
                        </button>
                    </a>
                </div>
            </div>

            <!-- Life Management Category -->
            <div class="sidebar-category">
                <button class="category-toggle w-full flex items-center justify-between px-3 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-300">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-heart w-5"></i>
                        <span class="font-medium">Life Management</span>
                    </div>
                    <i class="fas fa-chevron-down text-xs transition-transform duration-200"></i>
                </button>
                <div class="category-items pl-11 mt-1 space-y-1 hidden">
                    <a href="/journal.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm <?php echo $currentPage == 'journal.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 dark:text-gray-400'; ?>">
                        <span>Journal & Mood</span>
                    </a>
                    <a href="/learning.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm <?php echo $currentPage == 'learning.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 dark:text-gray-400'; ?>">
                        <span>Learning</span>
                    </a>
                    <a href="/learning_center.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm <?php echo $currentPage == 'learning_center.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 dark:text-gray-400'; ?>">
                        <span><i class="fas fa-graduation-cap"></i> Learning Hub</span>
                    </a>
                    <a href="/hobbies.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm <?php echo $currentPage == 'hobbies.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 dark:text-gray-400'; ?>">
                        <span>Hobbies</span>
                    </a>
                    <a href="/media.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm <?php echo $currentPage == 'media.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 dark:text-gray-400'; ?>">
                        <span>Media & Entertainment</span>
                    </a>
                    <a href="/gifts.php" class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm <?php echo $currentPage == 'gifts.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 dark:text-gray-400'; ?>">
                        <span>Gift Management</span>
                        <button class="quick-add-btn" data-module="gift">
                            <i class="fas fa-plus text-xs"></i>
                        </button>
                    </a>
                    <a href="/relationships.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm <?php echo $currentPage == 'relationships.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 dark:text-gray-400'; ?>">
                        <span><i class="fas fa-users"></i> Relationships</span>
                    </a>
                    <a href="/family_manager.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm <?php echo $currentPage == 'family_manager.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 dark:text-gray-400'; ?>">
                        <span><i class="fas fa-home-user"></i> Family Manager</span>
                    </a>
                    <a href="/contacts.php" class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm <?php echo $currentPage == 'contacts.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 dark:text-gray-400'; ?>">
                        <span>Personal CRM</span>
                        <button class="quick-add-btn" data-module="contact">
                            <i class="fas fa-plus text-xs"></i>
                        </button>
                    </a>
                </div>
            </div>

            <!-- Travel & Lifestyle Category -->
            <div class="sidebar-category">
                <button class="category-toggle w-full flex items-center justify-between px-3 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-300">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-plane w-5"></i>
                        <span class="font-medium">Travel & Lifestyle</span>
                    </div>
                    <i class="fas fa-chevron-down text-xs transition-transform duration-200"></i>
                </button>
                <div class="category-items pl-11 mt-1 space-y-1 hidden">
                    <a href="/travel_planner.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm <?php echo $currentPage == 'travel_planner.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 dark:text-gray-400'; ?>">
                        <span><i class="fas fa-suitcase"></i> Travel Planner</span>
                    </a>
                    <a href="/travel_journal.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm <?php echo $currentPage == 'travel_journal.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 dark:text-gray-400'; ?>">
                        <span><i class="fas fa-book-open"></i> Travel Journal</span>
                    </a>
                </div>
            </div>

            <!-- Reminders & Events Category -->
            <div class="sidebar-category">
                <button class="category-toggle w-full flex items-center justify-between px-3 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-300">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-calendar w-5"></i>
                        <span class="font-medium">Reminders & Events</span>
                    </div>
                    <i class="fas fa-chevron-down text-xs transition-transform duration-200"></i>
                </button>
                <div class="category-items pl-11 mt-1 space-y-1 hidden">
                    <a href="/calendar.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm <?php echo $currentPage == 'calendar.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 dark:text-gray-400'; ?>">
                        <span>Calendar View</span>
                    </a>
                    <a href="/birthdays.php" class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm <?php echo $currentPage == 'birthdays.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 dark:text-gray-400'; ?>">
                        <span>Birthdays</span>
                        <button class="quick-add-btn" data-module="birthday">
                            <i class="fas fa-plus text-xs"></i>
                        </button>
                    </a>
                    <a href="/subscriptions.php" class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm <?php echo $currentPage == 'subscriptions.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 dark:text-gray-400'; ?>">
                        <span>Subscriptions</span>
                        <button class="quick-add-btn" data-module="subscription">
                            <i class="fas fa-plus text-xs"></i>
                        </button>
                    </a>
                    <a href="/finance_advanced.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm <?php echo $currentPage == 'finance_advanced.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 dark:text-gray-400'; ?>">
                        <span><i class="fas fa-chart-line"></i> Finance Advanced</span>
                    </a>
                    <a href="/ai_lifemap.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm <?php echo $currentPage == 'ai_lifemap.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 dark:text-gray-400'; ?>">
                        <span><i class="fas fa-brain"></i> AI Life Map</span>
                    </a>
                    <a href="/events.php" class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm <?php echo $currentPage == 'events.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 dark:text-gray-400'; ?>">
                        <span>Event Planner</span>
                        <button class="quick-add-btn" data-module="event">
                            <i class="fas fa-plus text-xs"></i>
                        </button>
                    </a>
                </div>
            </div>

            <!-- Home & Assets Category -->
            <div class="sidebar-category">
                <button class="category-toggle w-full flex items-center justify-between px-3 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-300">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-home w-5"></i>
                        <span class="font-medium">Home & Assets</span>
                    </div>
                    <i class="fas fa-chevron-down text-xs transition-transform duration-200"></i>
                </button>
                <div class="category-items pl-11 mt-1 space-y-1 hidden">
                    <a href="/home_assets.php" class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm <?php echo $currentPage == 'home_assets.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 dark:text-gray-400'; ?>">
                        <span>Home Assets</span>
                        <button class="quick-add-btn" data-module="home_asset">
                            <i class="fas fa-plus text-xs"></i>
                        </button>
                    </a>
                    <a href="/maintenance.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm <?php echo $currentPage == 'maintenance.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 dark:text-gray-400'; ?>">
                        <span>Maintenance Logs</span>
                    </a>
                    <a href="/documents.php" class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm <?php echo $currentPage == 'documents.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 dark:text-gray-400'; ?>">
                        <span>Documents</span>
                        <button class="quick-add-btn" data-module="document">
                            <i class="fas fa-plus text-xs"></i>
                        </button>
                    </a>
                    <a href="/assets.php" class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm <?php echo $currentPage == 'assets.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 dark:text-gray-400'; ?>">
                        <span>Personal Assets</span>
                        <button class="quick-add-btn" data-module="asset">
                            <i class="fas fa-plus text-xs"></i>
                        </button>
                    </a>
                    <a href="/vehicles.php" class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm <?php echo $currentPage == 'vehicles.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 dark:text-gray-400'; ?>">
                        <span>Vehicle Maintenance</span>
                        <button class="quick-add-btn" data-module="vehicle">
                            <i class="fas fa-plus text-xs"></i>
                        </button>
                    </a>
                    <a href="/recipes.php" class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm <?php echo $currentPage == 'recipes.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 dark:text-gray-400'; ?>">
                        <span>Recipe Book</span>
                        <button class="quick-add-btn" data-module="recipe">
                            <i class="fas fa-plus text-xs"></i>
                        </button>
                    </a>
                </div>
            </div>
            
            <!-- AI & Productivity Category -->
            <div class="sidebar-category">
                <button class="category-toggle w-full flex items-center justify-between px-3 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-300">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-robot w-5"></i>
                        <span class="font-medium">AI & Productivity</span>
                    </div>
                    <i class="fas fa-chevron-down text-xs transition-transform duration-200"></i>
                </button>
                <div class="category-items pl-11 mt-1 space-y-1 hidden">
                    <a href="/ai_assistant.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm <?php echo $currentPage == 'ai_assistant.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 dark:text-gray-400'; ?>">
                        <span>AI Assistant</span>
                    </a>
                    <a href="/ai_briefing.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm <?php echo $currentPage == 'ai_briefing.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 dark:text-gray-400'; ?>">
                        <span>Daily Briefing</span>
                    </a>
                    <a href="/life_advisor.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm <?php echo $currentPage == 'life_advisor.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 dark:text-gray-400'; ?>">
                        <span><i class="fas fa-magic"></i> Life Advisor</span>
                    </a>
                    <a href="/financial_forecast.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm <?php echo $currentPage == 'financial_forecast.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 dark:text-gray-400'; ?>">
                        <span><i class="fas fa-chart-line"></i> Financial Forecast</span>
                    </a>
                    <a href="/smart_goals.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm <?php echo $currentPage == 'smart_goals.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 dark:text-gray-400'; ?>">
                        <span><i class="fas fa-bullseye"></i> SMART Goals</span>
                    </a>
                    <a href="/life_events.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm <?php echo $currentPage == 'life_events.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 dark:text-gray-400'; ?>">
                        <span><i class="fas fa-crystal-ball"></i> Life Events</span>
                    </a>
                    <a href="/relationships.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm <?php echo $currentPage == 'relationships.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 dark:text-gray-400'; ?>">
                        <span><i class="fas fa-users"></i> Relationships</span>
                    </a>
                </div>
            </div>

            <div class="my-2 border-t border-gray-200 dark:border-gray-700"></div>

            <!-- V3.0 Professional & AI Tools -->
            <div class="sidebar-category">
                <button class="category-toggle w-full flex items-center justify-between px-3 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-300">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-rocket w-5"></i>
                        <span class="font-medium">V3.0 Professional & AI</span>
                        <span class="bg-gradient-to-r from-primary to-purple-600 text-white text-xs px-2 py-0.5 rounded-full">NEW</span>
                    </div>
                    <i class="fas fa-chevron-down text-xs transition-transform duration-200"></i>
                </button>
                <div class="category-items pl-11 mt-1 space-y-1 hidden">
                    <a href="/freelance_tracker.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm <?php echo $currentPage == 'freelance_tracker.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 dark:text-gray-400'; ?>">
                        <span><i class="fas fa-briefcase"></i> Freelance Tracker</span>
                    </a>
                    <a href="/tax_reports.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm <?php echo $currentPage == 'tax_reports.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 dark:text-gray-400'; ?>">
                        <span><i class="fas fa-receipt"></i> Tax Reports & PDF</span>
                    </a>
                    <a href="/team_collaboration.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm <?php echo $currentPage == 'team_collaboration.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 dark:text-gray-400'; ?>">
                        <span><i class="fas fa-users"></i> Team Collaboration</span>
                    </a>
                    <a href="/knowledge_vault.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm <?php echo $currentPage == 'knowledge_vault.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 dark:text-gray-400'; ?>">
                        <span><i class="fas fa-brain"></i> Knowledge Vault</span>
                    </a>
                    <a href="/unified_search.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm <?php echo $currentPage == 'unified_search.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 dark:text-gray-400'; ?>">
                        <span><i class="fas fa-search"></i> Unified Search</span>
                    </a>
                    <a href="/life_orchestrator.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm <?php echo $currentPage == 'life_orchestrator.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 dark:text-gray-400'; ?>">
                        <span><i class="fas fa-zap"></i> Life Orchestrator</span>
                    </a>
                    <a href="/custom_dashboards.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm <?php echo $currentPage == 'custom_dashboards.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 dark:text-gray-400'; ?>">
                        <span><i class="fas fa-layout-dashboard"></i> Custom Dashboards</span>
                    </a>
                    <a href="/portfolio_generator.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm <?php echo $currentPage == 'portfolio_generator.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 dark:text-gray-400'; ?>">
                        <span><i class="fas fa-award"></i> Portfolio Generator</span>
                    </a>
                    <a href="/nutrition_ai.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm <?php echo $currentPage == 'nutrition_ai.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 dark:text-gray-400'; ?>">
                        <span><i class="fas fa-apple"></i> Nutrition AI</span>
                    </a>
                </div>
            </div>

            <div class="my-2 border-t border-gray-200 dark:border-gray-700"></div>

            <!-- Analytics & Reports -->
            <a href="/analytics.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 <?php echo $currentPage == 'analytics.php' ? 'bg-primary/10 text-primary' : 'text-gray-700 dark:text-gray-300'; ?>">
                <i class="fas fa-chart-bar w-5"></i>
                <span class="font-medium">Analytics</span>
            </a>
            
            <!-- Life Analytics -->
            <a href="/life_analytics.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 <?php echo $currentPage == 'life_analytics.php' ? 'bg-primary/10 text-primary' : 'text-gray-700 dark:text-gray-300'; ?>">
                <i class="fas fa-chart-pie w-5"></i>
                <span class="font-medium">Life Analytics</span>
            </a>

            <!-- Notifications -->
            <a href="/notifications.php" class="flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 <?php echo $currentPage == 'notifications.php' ? 'bg-primary/10 text-primary' : 'text-gray-700 dark:text-gray-300'; ?>">
                <div class="flex items-center gap-3">
                    <i class="fas fa-bell w-5"></i>
                    <span class="font-medium">Notifications</span>
                </div>
                <?php if ($unreadNotifications > 0): ?>
                <span id="notificationCount" class="bg-red-500 text-white text-xs px-2 py-0.5 rounded-full"><?php echo $unreadNotifications; ?></span>
                <?php else: ?>
                <span id="notificationCount" class="bg-red-500 text-white text-xs px-2 py-0.5 rounded-full" style="display: none;">0</span>
                <?php endif; ?>
            </a>

            <!-- Security & Privacy Category -->
            <div class="sidebar-category">
                <button class="category-toggle w-full flex items-center justify-between px-3 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-300">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-shield-alt w-5"></i>
                        <span class="font-medium">Security & Privacy</span>
                    </div>
                    <i class="fas fa-chevron-down text-xs transition-transform duration-200"></i>
                </button>
                <div class="category-items pl-11 mt-1 space-y-1 hidden">
                    <a href="/vault.php" class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm <?php echo $currentPage == 'vault.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 dark:text-gray-400'; ?>">
                        <span>Secure Vault</span>
                        <button class="quick-add-btn" data-module="vault">
                            <i class="fas fa-plus text-xs"></i>
                        </button>
                    </a>
                    <a href="/devices.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm <?php echo $currentPage == 'devices.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 dark:text-gray-400'; ?>">
                        <span>Device Management</span>
                    </a>
                    <a href="/security_2fa.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm <?php echo $currentPage == 'security_2fa.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 dark:text-gray-400'; ?>">
                        <span>2FA Security</span>
                    </a>
                </div>
            </div>

            <!-- Settings Category -->
            <div class="sidebar-category">
                <button class="category-toggle w-full flex items-center justify-between px-3 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-300">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-cog w-5"></i>
                        <span class="font-medium">Settings</span>
                    </div>
                    <i class="fas fa-chevron-down text-xs transition-transform duration-200"></i>
                </button>
                <div class="category-items pl-11 mt-1 space-y-1 hidden">
                    <a href="/profile.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm <?php echo $currentPage == 'profile.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 dark:text-gray-400'; ?>">
                        <span>Profile</span>
                    </a>
                    <a href="/import_export.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm <?php echo $currentPage == 'import_export.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 dark:text-gray-400'; ?>">
                        <span>Import / Export</span>
                    </a>
                    <a href="/backup.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm <?php echo $currentPage == 'backup.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 dark:text-gray-400'; ?>">
                        <span>Backup & Restore</span>
                    </a>
                    <a href="/settings.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-sm <?php echo $currentPage == 'settings.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 dark:text-gray-400'; ?>">
                        <span>Preferences</span>
                    </a>
                </div>
            </div>

            <?php if ($auth->isAdmin()): ?>
            <!-- Admin Panel -->
            <div class="my-2 border-t border-gray-200 dark:border-gray-700"></div>
            <a href="/admin.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 <?php echo $currentPage == 'admin.php' ? 'bg-primary/10 text-primary' : 'text-gray-700 dark:text-gray-300'; ?>">
                <i class="fas fa-user-shield w-5"></i>
                <span class="font-medium">Admin Panel</span>
            </a>
            <?php endif; ?>
        </nav>
    </div>

    <!-- Sidebar Footer -->
    <div class="absolute bottom-0 left-0 right-0 p-4 bg-gray-50 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center">
                    <i class="fas fa-user text-primary"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate"><?php echo sanitize($currentUser['name'] ?? 'User'); ?></p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button id="themeToggle" class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200" title="Toggle theme">
                    <i class="fas fa-moon"></i>
                </button>
                <a href="/logout.php" class="p-2 text-gray-500 hover:text-red-600" title="Logout">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </div>
    </div>
</aside>

<!-- Mobile Toggle Button -->
<button id="mobileToggle" class="sm:hidden fixed top-4 left-4 z-50 p-2 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700">
    <i class="fas fa-bars text-gray-700 dark:text-gray-300"></i>
</button>

<!-- Sidebar Overlay for Mobile -->
<div id="sidebarOverlay" class="fixed inset-0 bg-black/50 z-30 hidden sm:hidden"></div>

<style>
.quick-add-btn {
    padding: 4px 6px;
    background: transparent;
    border: none;
    color: var(--primary);
    opacity: 0;
    transition: opacity 0.2s;
    cursor: pointer;
}

.category-items a:hover .quick-add-btn {
    opacity: 1;
}

.quick-add-btn:hover {
    background: var(--primary);
    color: white;
    border-radius: 4px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Category toggle functionality
    document.querySelectorAll('.category-toggle').forEach(button => {
        button.addEventListener('click', function() {
            const category = this.closest('.sidebar-category');
            const items = category.querySelector('.category-items');
            const chevron = this.querySelector('.fa-chevron-down');
            
            items.classList.toggle('hidden');
            chevron.classList.toggle('rotate-180');
        });
    });

    // Mobile sidebar toggle
    const sidebar = document.getElementById('sidebar');
    const mobileToggle = document.getElementById('mobileToggle');
    const closeSidebar = document.getElementById('closeSidebar');
    const overlay = document.getElementById('sidebarOverlay');

    function toggleSidebar() {
        sidebar.classList.toggle('-translate-x-full');
        overlay.classList.toggle('hidden');
    }

    mobileToggle?.addEventListener('click', toggleSidebar);
    closeSidebar?.addEventListener('click', toggleSidebar);
    overlay?.addEventListener('click', toggleSidebar);

    // Search functionality
    const searchInput = document.getElementById('sidebarSearch');
    searchInput?.addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const navLinks = document.querySelectorAll('.sidebar-category, nav > a, .category-items a');
        
        navLinks.forEach(link => {
            const text = link.textContent.toLowerCase();
            if (text.includes(searchTerm)) {
                link.style.display = '';
                // Show parent category if child matches
                const parent = link.closest('.sidebar-category');
                if (parent) {
                    parent.style.display = '';
                    parent.querySelector('.category-items').classList.remove('hidden');
                }
            } else {
                link.style.display = 'none';
            }
        });
    });

    // Quick add button handlers
    document.querySelectorAll('.quick-add-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const module = this.dataset.module;
            // Trigger the add modal for the specific module
            const modalId = module + 'Modal';
            const modal = document.getElementById(modalId);
            if (modal && typeof openModal === 'function') {
                openModal(modalId);
            }
        });
    });

    // Theme toggle
    const themeToggle = document.getElementById('themeToggle');
    const html = document.documentElement;
    
    // Load saved theme
    const savedTheme = localStorage.getItem('theme') || 'light';
    html.classList.toggle('dark', savedTheme === 'dark');
    updateThemeIcon(savedTheme);

    themeToggle?.addEventListener('click', function() {
        const isDark = html.classList.toggle('dark');
        const newTheme = isDark ? 'dark' : 'light';
        localStorage.setItem('theme', newTheme);
        updateThemeIcon(newTheme);
    });

    function updateThemeIcon(theme) {
        const icon = themeToggle.querySelector('i');
        if (theme === 'dark') {
            icon.classList.remove('fa-moon');
            icon.classList.add('fa-sun');
        } else {
            icon.classList.remove('fa-sun');
            icon.classList.add('fa-moon');
        }
    }
});
</script>
