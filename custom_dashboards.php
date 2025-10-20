<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$pageTitle = 'Custom Dashboard Builder';
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <i data-lucide="layout-dashboard" class="text-primary"></i>
                Custom Dashboard Builder
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Design your perfect dashboard with drag-and-drop widgets</p>
        </div>
        <div class="flex gap-2">
            <button onclick="createNewDashboard()" class="btn bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-lg">
                <i data-lucide="plus" class="w-4 h-4 inline"></i> New Dashboard
            </button>
            <button onclick="toggleEditMode()" class="btn bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg" id="editModeBtn">
                <i data-lucide="edit" class="w-4 h-4 inline"></i> Edit Mode
            </button>
        </div>
    </div>

    <div class="mb-6">
        <div class="flex gap-2 overflow-x-auto pb-2">
            <button onclick="switchDashboard(0)" class="dashboard-tab active px-4 py-2 rounded-lg bg-white dark:bg-gray-800 border-2 border-primary font-medium whitespace-nowrap">
                Default Dashboard
            </button>
        </div>
    </div>

    <div id="editTools" class="hidden mb-6 bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
        <h3 class="font-semibold mb-4">Available Widgets</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
            <button onclick="addWidget('stats')" class="p-4 border-2 border-dashed rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                <i data-lucide="bar-chart-2" class="w-8 h-8 mx-auto mb-2 text-blue-600"></i>
                <div class="text-sm font-medium">Stats Card</div>
            </button>
            <button onclick="addWidget('chart')" class="p-4 border-2 border-dashed rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                <i data-lucide="pie-chart" class="w-8 h-8 mx-auto mb-2 text-green-600"></i>
                <div class="text-sm font-medium">Chart</div>
            </button>
            <button onclick="addWidget('tasks')" class="p-4 border-2 border-dashed rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                <i data-lucide="check-square" class="w-8 h-8 mx-auto mb-2 text-purple-600"></i>
                <div class="text-sm font-medium">Tasks</div>
            </button>
            <button onclick="addWidget('calendar')" class="p-4 border-2 border-dashed rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                <i data-lucide="calendar" class="w-8 h-8 mx-auto mb-2 text-orange-600"></i>
                <div class="text-sm font-medium">Calendar</div>
            </button>
            <button onclick="addWidget('notes')" class="p-4 border-2 border-dashed rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                <i data-lucide="file-text" class="w-8 h-8 mx-auto mb-2 text-yellow-600"></i>
                <div class="text-sm font-medium">Notes</div>
            </button>
            <button onclick="addWidget('goals')" class="p-4 border-2 border-dashed rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                <i data-lucide="target" class="w-8 h-8 mx-auto mb-2 text-red-600"></i>
                <div class="text-sm font-medium">Goals</div>
            </button>
        </div>
    </div>

    <div id="dashboardCanvas" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 min-h-[400px]">
        <div class="widget bg-white dark:bg-gray-800 rounded-lg shadow-md p-6" data-widget-id="1">
            <div class="flex justify-between items-start mb-4">
                <h3 class="font-semibold">Tasks Overview</h3>
                <div class="widget-actions hidden">
                    <button onclick="removeWidget(1)" class="text-red-600 hover:text-red-700">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>
            <div class="space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Pending</span>
                    <span class="font-semibold">12</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">In Progress</span>
                    <span class="font-semibold">5</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Completed</span>
                    <span class="font-semibold">23</span>
                </div>
            </div>
        </div>

        <div class="widget bg-white dark:bg-gray-800 rounded-lg shadow-md p-6" data-widget-id="2">
            <div class="flex justify-between items-start mb-4">
                <h3 class="font-semibold">Finance Summary</h3>
                <div class="widget-actions hidden">
                    <button onclick="removeWidget(2)" class="text-red-600 hover:text-red-700">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>
            <div class="space-y-3">
                <div>
                    <div class="text-sm text-gray-600">Income (MTD)</div>
                    <div class="text-2xl font-bold text-green-600">$5,230</div>
                </div>
                <div>
                    <div class="text-sm text-gray-600">Expenses (MTD)</div>
                    <div class="text-2xl font-bold text-red-600">$3,450</div>
                </div>
            </div>
        </div>

        <div class="widget bg-white dark:bg-gray-800 rounded-lg shadow-md p-6" data-widget-id="3">
            <div class="flex justify-between items-start mb-4">
                <h3 class="font-semibold">Upcoming Events</h3>
                <div class="widget-actions hidden">
                    <button onclick="removeWidget(3)" class="text-red-600 hover:text-red-700">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>
            <div class="text-sm text-gray-500">No upcoming events</div>
        </div>

        <div class="widget bg-white dark:bg-gray-800 rounded-lg shadow-md p-6" data-widget-id="4">
            <div class="flex justify-between items-start mb-4">
                <h3 class="font-semibold">Health Stats</h3>
                <div class="widget-actions hidden">
                    <button onclick="removeWidget(4)" class="text-red-600 hover:text-red-700">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>
            <div class="space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Steps Today</span>
                    <span class="font-semibold">8,542</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Water Intake</span>
                    <span class="font-semibold">2.1L</span>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg text-center">
        <p class="text-blue-800 dark:text-blue-300">
            <i data-lucide="info" class="w-4 h-4 inline"></i>
            Turn on Edit Mode to customize your dashboard by adding, removing, or rearranging widgets
        </p>
    </div>
</div>

<script src="/assets/js/custom-dashboards.js"></script>
<script>
if (typeof lucide !== 'undefined') lucide.createIcons();
</script>

<?php include 'includes/footer.php'; ?>
