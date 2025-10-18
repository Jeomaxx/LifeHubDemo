<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$pageTitle = 'Calendar';
$extraScripts = ['/assets/js/calendar.js'];
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <i class="fas fa-calendar-alt text-primary"></i>
                Full Calendar
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Manage all your events, tasks, and appointments in one place</p>
        </div>
        <div class="flex gap-2">
            <button onclick="showAddEventModal()" class="btn btn-primary flex items-center gap-2">
                <i class="fas fa-plus"></i>
                Add Event
            </button>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
            <div class="flex items-center gap-2">
                <button id="prevPeriod" class="px-3 py-2 bg-gray-200 dark:bg-gray-700 rounded hover:bg-gray-300 dark:hover:bg-gray-600">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button id="today" class="px-4 py-2 bg-primary text-white rounded hover:bg-blue-600">
                    Today
                </button>
                <button id="nextPeriod" class="px-3 py-2 bg-gray-200 dark:bg-gray-700 rounded hover:bg-gray-300 dark:hover:bg-gray-600">
                    <i class="fas fa-chevron-right"></i>
                </button>
                <h2 id="currentPeriod" class="text-xl font-bold ml-4 text-gray-900 dark:text-white"></h2>
            </div>
            
            <div class="flex items-center gap-2">
                <select id="viewMode" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    <option value="month">Month</option>
                    <option value="week">Week</option>
                    <option value="day">Day</option>
                </select>
                
                <div class="relative">
                    <button id="filterBtn" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-filter"></i>
                        Filter
                    </button>
                    <div id="filterDropdown" class="hidden absolute right-0 mt-2 w-64 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 p-4 z-10">
                        <h3 class="font-semibold mb-3 text-gray-900 dark:text-white">Event Sources</h3>
                        <label class="flex items-center gap-2 mb-2 cursor-pointer">
                            <input type="checkbox" class="filter-checkbox" data-source="custom" checked>
                            <span class="text-sm text-gray-700 dark:text-gray-300">Custom Events</span>
                        </label>
                        <label class="flex items-center gap-2 mb-2 cursor-pointer">
                            <input type="checkbox" class="filter-checkbox" data-source="task" checked>
                            <span class="text-sm text-gray-700 dark:text-gray-300">Tasks</span>
                        </label>
                        <label class="flex items-center gap-2 mb-2 cursor-pointer">
                            <input type="checkbox" class="filter-checkbox" data-source="bill" checked>
                            <span class="text-sm text-gray-700 dark:text-gray-300">Bills</span>
                        </label>
                        <label class="flex items-center gap-2 mb-2 cursor-pointer">
                            <input type="checkbox" class="filter-checkbox" data-source="birthday" checked>
                            <span class="text-sm text-gray-700 dark:text-gray-300">Birthdays</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" class="filter-checkbox" data-source="gym" checked>
                            <span class="text-sm text-gray-700 dark:text-gray-300">Gym Routines</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div id="calendarView" class="min-h-[600px]">
        </div>
    </div>
</div>

<div id="addEventModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-lg w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white" id="modalTitle">Add Event</h2>
        </div>
        <form id="eventForm" class="p-6 space-y-4">
            <input type="hidden" id="eventId">
            
            <div>
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Title *</label>
                <input type="text" id="eventTitle" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Description</label>
                <textarea id="eventDescription" rows="3" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"></textarea>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Start Date & Time *</label>
                    <input type="datetime-local" id="eventStart" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">End Date & Time *</label>
                    <input type="datetime-local" id="eventEnd" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>
            </div>
            
            <div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" id="eventAllDay" class="rounded">
                    <span class="text-sm text-gray-700 dark:text-gray-300">All Day Event</span>
                </label>
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Location</label>
                <input type="text" id="eventLocation" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Color</label>
                <div class="flex gap-2 flex-wrap">
                    <button type="button" class="color-btn w-8 h-8 rounded-full border-2 border-transparent" style="background-color: #3b82f6" data-color="#3b82f6"></button>
                    <button type="button" class="color-btn w-8 h-8 rounded-full border-2 border-transparent" style="background-color: #10b981" data-color="#10b981"></button>
                    <button type="button" class="color-btn w-8 h-8 rounded-full border-2 border-transparent" style="background-color: #f59e0b" data-color="#f59e0b"></button>
                    <button type="button" class="color-btn w-8 h-8 rounded-full border-2 border-transparent" style="background-color: #ef4444" data-color="#ef4444"></button>
                    <button type="button" class="color-btn w-8 h-8 rounded-full border-2 border-transparent" style="background-color: #8b5cf6" data-color="#8b5cf6"></button>
                    <button type="button" class="color-btn w-8 h-8 rounded-full border-2 border-transparent" style="background-color: #ec4899" data-color="#ec4899"></button>
                </div>
                <input type="hidden" id="eventColor" value="#3b82f6">
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Reminder (minutes before)</label>
                <select id="eventReminder" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    <option value="0">No reminder</option>
                    <option value="15">15 minutes before</option>
                    <option value="30" selected>30 minutes before</option>
                    <option value="60">1 hour before</option>
                    <option value="1440">1 day before</option>
                </select>
            </div>
            
            <div class="flex justify-end gap-2 pt-4">
                <button type="button" onclick="closeAddEventModal()" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-blue-600">
                    Save Event
                </button>
            </div>
        </form>
    </div>
</div>

<div id="eventDetailModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-lg w-full max-w-lg">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex items-start justify-between">
            <div>
                <h2 id="detailTitle" class="text-xl font-bold text-gray-900 dark:text-white"></h2>
                <p id="detailTime" class="text-sm text-gray-600 dark:text-gray-400 mt-1"></p>
            </div>
            <button onclick="closeEventDetail()" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6 space-y-4">
            <div id="detailDescription"></div>
            <div id="detailLocation"></div>
            <div id="detailSource" class="text-sm text-gray-600 dark:text-gray-400"></div>
        </div>
        <div id="detailActions" class="p-6 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-2">
            <button onclick="editEvent()" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-blue-600">
                <i class="fas fa-edit mr-1"></i> Edit
            </button>
            <button onclick="deleteEvent()" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600">
                <i class="fas fa-trash mr-1"></i> Delete
            </button>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
