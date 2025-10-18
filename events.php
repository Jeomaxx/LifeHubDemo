<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

$events = $db->fetchAll("SELECT * FROM events WHERE user_id = ? ORDER BY event_date DESC", [$userId]);

$pageTitle = 'Event Planner';
$extraScripts = ['/assets/js/new-modules.js'];
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <i class="fas fa-calendar-check text-primary"></i>
                Event Planner
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Plan and manage your personal events</p>
        </div>
        <button onclick="showAddEventModal()" class="btn btn-primary flex items-center gap-2">
            <i class="fas fa-plus"></i>
            Plan Event
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Events</p>
                    <h3 class="text-2xl font-bold"><?php echo count($events); ?></h3>
                </div>
                <i class="fas fa-calendar-alt text-3xl text-blue-500"></i>
            </div>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">In Planning</p>
                    <h3 class="text-2xl font-bold"><?php echo count(array_filter($events, fn($e) => $e['status'] == 'planning')); ?></h3>
                </div>
                <i class="fas fa-tasks text-3xl text-orange-500"></i>
            </div>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Confirmed</p>
                    <h3 class="text-2xl font-bold"><?php echo count(array_filter($events, fn($e) => $e['status'] == 'confirmed')); ?></h3>
                </div>
                <i class="fas fa-check-circle text-3xl text-green-500"></i>
            </div>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Budget</p>
                    <h3 class="text-2xl font-bold">$<?php echo number_format(array_sum(array_column($events, 'budget')), 2); ?></h3>
                </div>
                <i class="fas fa-dollar-sign text-3xl text-purple-500"></i>
            </div>
        </div>
    </div>

    <?php if (empty($events)): ?>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-12 text-center">
            <i class="fas fa-calendar-check text-6xl text-gray-300 mb-4"></i>
            <p class="text-gray-500">No events planned yet. Create your first event to get started!</p>
        </div>
    <?php else: ?>
        <div class="space-y-6">
            <?php foreach ($events as $event): 
                $checklistItems = $db->fetchAll("SELECT * FROM event_checklists WHERE event_id = ? ORDER BY position", [$event['id']]);
                $completedItems = count(array_filter($checklistItems, fn($i) => $i['is_completed']));
                $guestCount = $db->fetchColumn("SELECT COUNT(*) FROM event_guests WHERE event_id = ?", [$event['id']]);
                $budgetItems = $db->fetchAll("SELECT * FROM event_budget_items WHERE event_id = ?", [$event['id']]);
                $totalSpent = array_sum(array_column($budgetItems, 'actual_cost'));
            ?>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <h2 class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo sanitize($event['name']); ?></h2>
                                <span class="px-3 py-1 text-sm rounded-full <?php 
                                    echo match($event['status']) {
                                        'planning' => 'bg-orange-100 text-orange-800',
                                        'confirmed' => 'bg-green-100 text-green-800',
                                        'completed' => 'bg-blue-100 text-blue-800',
                                        'cancelled' => 'bg-red-100 text-red-800',
                                        default => 'bg-gray-100 text-gray-800'
                                    };
                                ?>"><?php echo ucfirst($event['status']); ?></span>
                            </div>
                            <p class="text-gray-600 dark:text-gray-400"><?php echo ucfirst(str_replace('_', ' ', $event['event_type'])); ?></p>
                        </div>
                        <div class="flex gap-2">
                            <button onclick="editEvent(<?php echo $event['id']; ?>)" class="text-blue-600 hover:text-blue-700">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="deleteEvent(<?php echo $event['id']; ?>)" class="text-red-600 hover:text-red-700">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                        <div>
                            <p class="text-sm text-gray-500">Date & Time</p>
                            <p class="font-semibold"><?php echo date('M d, Y', strtotime($event['event_date'])); ?></p>
                            <?php if ($event['event_time']): ?>
                            <p class="text-sm"><?php echo date('g:i A', strtotime($event['event_time'])); ?></p>
                            <?php endif; ?>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Location</p>
                            <p class="font-semibold"><?php echo sanitize($event['location'] ?? 'TBD'); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Guests</p>
                            <p class="font-semibold"><?php echo $guestCount; ?> invited</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Budget</p>
                            <p class="font-semibold <?php echo $totalSpent > $event['budget'] ? 'text-red-600' : 'text-green-600'; ?>">
                                $<?php echo number_format($totalSpent, 2); ?> / $<?php echo number_format($event['budget'], 2); ?>
                            </p>
                        </div>
                    </div>
                    
                    <?php if (count($checklistItems) > 0): ?>
                    <div class="mb-4">
                        <div class="flex justify-between text-sm mb-2">
                            <span class="text-gray-600 dark:text-gray-400">Checklist Progress</span>
                            <span class="font-semibold"><?php echo $completedItems; ?> / <?php echo count($checklistItems); ?> completed</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            <div class="bg-green-500 h-2 rounded-full" style="width: <?php echo count($checklistItems) > 0 ? ($completedItems / count($checklistItems)) * 100 : 0; ?>%"></div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="flex gap-2">
                        <button onclick="manageChecklist(<?php echo $event['id']; ?>)" class="flex-1 px-4 py-2 bg-gray-100 dark:bg-gray-700 rounded hover:bg-gray-200">
                            <i class="fas fa-tasks mr-2"></i>Checklist
                        </button>
                        <button onclick="manageGuests(<?php echo $event['id']; ?>)" class="flex-1 px-4 py-2 bg-gray-100 dark:bg-gray-700 rounded hover:bg-gray-200">
                            <i class="fas fa-users mr-2"></i>Guests
                        </button>
                        <button onclick="manageBudget(<?php echo $event['id']; ?>)" class="flex-1 px-4 py-2 bg-gray-100 dark:bg-gray-700 rounded hover:bg-gray-200">
                            <i class="fas fa-dollar-sign mr-2"></i>Budget
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<div id="addEventModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-lg w-full max-w-lg">
        <div class="p-6 border-b">
            <h2 class="text-xl font-bold">Plan New Event</h2>
        </div>
        <form id="eventForm" class="p-6 space-y-4">
            <input type="hidden" id="eventId">
            
            <div>
                <label class="block text-sm font-medium mb-1">Event Name *</label>
                <input type="text" id="eventName" required class="w-full px-3 py-2 border rounded-lg">
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-1">Event Type *</label>
                <select id="eventType" required class="w-full px-3 py-2 border rounded-lg">
                    <option value="party">Party</option>
                    <option value="wedding">Wedding</option>
                    <option value="meeting">Meeting</option>
                    <option value="trip">Trip</option>
                    <option value="other">Other</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-1">Description</label>
                <textarea id="eventDescription" rows="2" class="w-full px-3 py-2 border rounded-lg"></textarea>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Event Date *</label>
                    <input type="date" id="eventDate" required class="w-full px-3 py-2 border rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Time</label>
                    <input type="time" id="eventTime" class="w-full px-3 py-2 border rounded-lg">
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-1">Location</label>
                <input type="text" id="eventLocation" class="w-full px-3 py-2 border rounded-lg">
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-1">Budget</label>
                <input type="number" id="eventBudget" step="0.01" class="w-full px-3 py-2 border rounded-lg">
            </div>
            
            <div class="flex justify-end gap-2 pt-4">
                <button type="button" onclick="closeAddEventModal()" class="px-4 py-2 border rounded-lg">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg">Create Event</button>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
