<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$userId = $auth->getUserId();
$db = Database::getInstance();

// Get current month
$month = (int)($_GET['month'] ?? date('n'));
$year = (int)($_GET['year'] ?? date('Y'));

// Get all events for the month
$startDate = "$year-$month-01";
$endDate = date('Y-m-t', strtotime($startDate));

// Tasks
$tasks = $db->fetchAll("SELECT 'task' as type, id, title as name, due_date as date, priority, status FROM tasks WHERE user_id = ? AND due_date BETWEEN ? AND ?", [$userId, $startDate, $endDate]);

// Goals
$goals = $db->fetchAll("SELECT 'goal' as type, id, title as name, target_date as date, progress FROM goals WHERE user_id = ? AND target_date BETWEEN ? AND ?", [$userId, $startDate, $endDate]);

// Bills
$bills = $db->fetchAll("SELECT 'bill' as type, id, name, due_date as date, amount, payment_status FROM bills WHERE user_id = ? AND due_date BETWEEN ? AND ?", [$userId, $startDate, $endDate]);

// Birthdays
$birthdays = $db->fetchAll("
    SELECT 'birthday' as type, id, name, 
    DATE(CONCAT(?, '-', EXTRACT(MONTH FROM birth_date), '-', EXTRACT(DAY FROM birth_date))) as date
    FROM birthdays WHERE user_id = ?
    AND EXTRACT(MONTH FROM birth_date) = ?
", [$year, $userId, $month]);

// Merge all events
$events = array_merge($tasks, $goals, $bills, $birthdays);

// Group events by date
$eventsByDate = [];
foreach ($events as $event) {
    $date = $event['date'];
    if (!isset($eventsByDate[$date])) {
        $eventsByDate[$date] = [];
    }
    $eventsByDate[$date][] = $event;
}

$pageTitle = 'Calendar';
include 'includes/header.php';
?>

<div class="mb-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <i class="fas fa-calendar text-primary"></i>
            Calendar
        </h1>
    </div>
</div>

<!-- Month Navigator -->
<div class="bg-white dark:bg-gray-800 rounded-lg p-4 mb-6 shadow-sm border border-gray-200 dark:border-gray-700">
    <div class="flex items-center justify-between">
        <button onclick="navigateMonth(-1)" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
            <i class="fas fa-chevron-left"></i>
        </button>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            <?php echo date('F Y', strtotime("$year-$month-01")); ?>
        </h3>
        <button onclick="navigateMonth(1)" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>
</div>

<!-- Calendar Grid -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="grid grid-cols-7 gap-px bg-gray-200 dark:bg-gray-700">
        <?php foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day): ?>
        <div class="bg-gray-100 dark:bg-gray-800 p-3 text-center font-semibold text-gray-700 dark:text-gray-300">
            <?php echo $day; ?>
        </div>
        <?php endforeach; ?>
        
        <?php
        $firstDay = (int)date('w', strtotime($startDate));
        $daysInMonth = (int)date('t', strtotime($startDate));
        
        // Fill empty cells before month starts
        for ($i = 0; $i < $firstDay; $i++):
        ?>
        <div class="bg-gray-50 dark:bg-gray-900 p-3 min-h-[100px]"></div>
        <?php endfor;
        
        // Calendar days
        for ($day = 1; $day <= $daysInMonth; $day++):
            $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
            $isToday = $date == date('Y-m-d');
            $dayEvents = $eventsByDate[$date] ?? [];
        ?>
        <div class="bg-white dark:bg-gray-800 p-3 min-h-[100px] <?php echo $isToday ? 'ring-2 ring-primary' : ''; ?>">
            <div class="text-sm font-semibold <?php echo $isToday ? 'text-primary' : 'text-gray-700 dark:text-gray-300'; ?> mb-2">
                <?php echo $day; ?>
            </div>
            <div class="space-y-1">
                <?php foreach ($dayEvents as $event): 
                    $colors = [
                        'task' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                        'goal' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                        'bill' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                        'birthday' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200'
                    ];
                ?>
                <div class="<?php echo $colors[$event['type']]; ?> text-xs px-2 py-1 rounded truncate" title="<?php echo sanitize($event['name']); ?>">
                    <?php echo sanitize(substr($event['name'], 0, 15)); ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endfor; ?>
    </div>
</div>

<!-- Legend -->
<div class="mt-6 flex flex-wrap gap-4">
    <div class="flex items-center gap-2">
        <div class="w-4 h-4 bg-blue-500 rounded"></div>
        <span class="text-sm text-gray-700 dark:text-gray-300">Tasks</span>
    </div>
    <div class="flex items-center gap-2">
        <div class="w-4 h-4 bg-green-500 rounded"></div>
        <span class="text-sm text-gray-700 dark:text-gray-300">Goals</span>
    </div>
    <div class="flex items-center gap-2">
        <div class="w-4 h-4 bg-red-500 rounded"></div>
        <span class="text-sm text-gray-700 dark:text-gray-300">Bills</span>
    </div>
    <div class="flex items-center gap-2">
        <div class="w-4 h-4 bg-purple-500 rounded"></div>
        <span class="text-sm text-gray-700 dark:text-gray-300">Birthdays</span>
    </div>
</div>

<script>
function navigateMonth(delta) {
    const date = new Date(<?php echo $year; ?>, <?php echo $month - 1; ?> + delta, 1);
    window.location.href = `?month=${date.getMonth() + 1}&year=${date.getFullYear()}`;
}
</script>

<?php include 'includes/footer.php'; ?>
