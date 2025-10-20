<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$pageTitle = 'Team Collaboration';
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <i data-lucide="users" class="text-primary"></i>
                Team Collaboration
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Share boards, tasks, and collaborate with your team</p>
        </div>
        <button onclick="openBoardModal()" class="btn bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-lg flex items-center gap-2">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Create Board</span>
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">My Boards</p>
                    <p id="myBoards" class="text-2xl font-bold text-gray-900 dark:text-white mt-1">0</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                    <i data-lucide="layout" class="text-blue-600 w-6 h-6"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Shared Boards</p>
                    <p id="sharedBoards" class="text-2xl font-bold text-gray-900 dark:text-white mt-1">0</p>
                </div>
                <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                    <i data-lucide="share-2" class="text-green-600 w-6 h-6"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Active Tasks</p>
                    <p id="activeTasks" class="text-2xl font-bold text-gray-900 dark:text-white mt-1">0</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                    <i data-lucide="check-square" class="text-purple-600 w-6 h-6"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Team Members</p>
                    <p id="teamMembers" class="text-2xl font-bold text-gray-900 dark:text-white mt-1">0</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900 rounded-lg flex items-center justify-center">
                    <i data-lucide="user-check" class="text-orange-600 w-6 h-6"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4">Team Boards</h2>
                <div id="boardsList"></div>
            </div>

            <div id="boardView" class="hidden bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <button onclick="backToBoards()" class="text-primary hover:underline mb-2">
                            <i data-lucide="arrow-left" class="w-4 h-4 inline"></i> Back to Boards
                        </button>
                        <h2 id="boardTitle" class="text-2xl font-semibold"></h2>
                    </div>
                    <div class="flex gap-2">
                        <button onclick="manageBoardMembers()" class="btn bg-blue-600 text-white px-4 py-2 rounded-lg">
                            <i data-lucide="user-plus" class="w-4 h-4 inline"></i> Manage Members
                        </button>
                        <button onclick="addTeamTask()" class="btn bg-primary text-white px-4 py-2 rounded-lg">
                            <i data-lucide="plus" class="w-4 h-4 inline"></i> Add Task
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4" id="kanbanBoard">
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                        <h3 class="font-semibold mb-3 flex items-center justify-between">
                            <span>To Do</span>
                            <span class="bg-gray-200 dark:bg-gray-600 px-2 py-1 rounded-full text-xs" id="todoCount">0</span>
                        </h3>
                        <div id="todoTasks" class="space-y-2"></div>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                        <h3 class="font-semibold mb-3 flex items-center justify-between">
                            <span>In Progress</span>
                            <span class="bg-blue-200 dark:bg-blue-600 px-2 py-1 rounded-full text-xs" id="inProgressCount">0</span>
                        </h3>
                        <div id="inProgressTasks" class="space-y-2"></div>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                        <h3 class="font-semibold mb-3 flex items-center justify-between">
                            <span>Review</span>
                            <span class="bg-yellow-200 dark:bg-yellow-600 px-2 py-1 rounded-full text-xs" id="reviewCount">0</span>
                        </h3>
                        <div id="reviewTasks" class="space-y-2"></div>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                        <h3 class="font-semibold mb-3 flex items-center justify-between">
                            <span>Done</span>
                            <span class="bg-green-200 dark:bg-green-600 px-2 py-1 rounded-full text-xs" id="doneCount">0</span>
                        </h3>
                        <div id="doneTasks" class="space-y-2"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Board Permissions</h2>
            <div class="space-y-4">
                <div class="border-b pb-3">
                    <div class="flex items-center gap-2 mb-1">
                        <i data-lucide="eye" class="w-4 h-4 text-gray-500"></i>
                        <span class="font-medium">Viewer</span>
                    </div>
                    <p class="text-sm text-gray-600">Can view boards and tasks only</p>
                </div>

                <div class="border-b pb-3">
                    <div class="flex items-center gap-2 mb-1">
                        <i data-lucide="edit" class="w-4 h-4 text-blue-500"></i>
                        <span class="font-medium">Editor</span>
                    </div>
                    <p class="text-sm text-gray-600">Can create and edit tasks</p>
                </div>

                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <i data-lucide="shield" class="w-4 h-4 text-green-500"></i>
                        <span class="font-medium">Admin</span>
                    </div>
                    <p class="text-sm text-gray-600">Full control including member management</p>
                </div>
            </div>

            <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                <p class="text-sm text-blue-800 dark:text-blue-300">
                    <i data-lucide="info" class="w-4 h-4 inline"></i>
                    Invite team members to collaborate on shared boards!
                </p>
            </div>
        </div>
    </div>
</div>

<script src="/assets/js/team-collaboration.js"></script>
<script>
if (typeof lucide !== 'undefined') lucide.createIcons();
</script>

<?php include 'includes/footer.php'; ?>
