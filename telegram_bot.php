<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/notifications.php';

// Telegram Webhook Handler with Security
$content = file_get_contents('php://input');
$update = json_decode($content, true);

if (!$update) {
    http_response_code(400);
    exit('Invalid request');
}

// Verify Telegram Bot Token (basic security)
$botToken = TELEGRAM_BOT_TOKEN;
if (empty($botToken)) {
    error_log('Telegram bot token not configured');
    http_response_code(403);
    exit('Bot not configured');
}

// Verify request authenticity using secret token in URL or header
$secretToken = $_GET['token'] ?? $_SERVER['HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN'] ?? '';
$expectedToken = hash('sha256', $botToken);

if ($secretToken !== $expectedToken) {
    error_log('Invalid Telegram webhook token');
    http_response_code(403);
    exit('Unauthorized');
}

$db = Database::getInstance();

// Extract message data
$message = $update['message'] ?? [];
$chatId = $message['chat']['id'] ?? null;
$text = $message['text'] ?? '';
$userId = null;

if (!$chatId || empty($text)) {
    http_response_code(200);
    exit('No message');
}

// Find user by chat ID
$user = $db->fetchOne("SELECT id, name FROM users WHERE telegram_chat_id = ?", [$chatId]);

if (!$user) {
    sendTelegramMessage($chatId, "Please link your Telegram account in the app settings first.");
    http_response_code(200);
    exit;
}

$userId = $user['id'];

// Process commands
$response = '';

try {
    if ($text === '/start') {
        $response = "Welcome to Life Atlas Organizer! 🌟\n\n";
        $response .= "Available commands:\n";
        $response .= "/report - Get daily report\n";
        $response .= "/tasks - View pending tasks\n";
        $response .= "/balance - Check financial balance\n";
        $response .= "/goals - View active goals\n";
        $response .= "/health - Health summary\n";
        $response .= "/addtask [task] - Add a new task\n";
        $response .= "/help - Show this help message";
    }
    elseif ($text === '/help') {
        $response = "📚 Life Atlas Commands:\n\n";
        $response .= "/report - Daily briefing\n";
        $response .= "/tasks - Pending tasks\n";
        $response .= "/balance - Financial balance\n";
        $response .= "/goals - Active goals\n";
        $response .= "/health - Health metrics\n";
        $response .= "/addtask [name] - Add task\n";
        $response .= "/addexpense [amount] [category] - Log expense";
    }
    elseif ($text === '/report') {
        $response = generateDailyReport($userId, $db);
    }
    elseif ($text === '/tasks') {
        $response = getTasksList($userId, $db);
    }
    elseif ($text === '/balance') {
        $response = getFinancialBalance($userId, $db);
    }
    elseif ($text === '/goals') {
        $response = getActiveGoals($userId, $db);
    }
    elseif ($text === '/health') {
        $response = getHealthSummary($userId, $db);
    }
    elseif (strpos($text, '/addtask ') === 0) {
        $taskName = trim(substr($text, 9));
        $response = addTask($userId, $taskName, $db);
    }
    elseif (strpos($text, '/addexpense ') === 0) {
        $parts = explode(' ', substr($text, 12));
        $amount = $parts[0] ?? 0;
        $category = $parts[1] ?? 'General';
        $response = addExpense($userId, $amount, $category, $db);
    }
    else {
        $response = "Command not recognized. Type /help for available commands.";
    }
    
    sendTelegramMessage($chatId, $response);
} catch (Exception $e) {
    error_log('Telegram bot error: ' . $e->getMessage());
    sendTelegramMessage($chatId, "Sorry, an error occurred processing your request.");
}

http_response_code(200);

// Bot Functions

function generateDailyReport($userId, $db) {
    $tasks = $db->fetchAll("SELECT * FROM tasks WHERE user_id = ? AND status != 'completed' ORDER BY priority DESC LIMIT 5", [$userId]);
    $finance = $db->fetchOne("SELECT SUM(CASE WHEN type = 'income' THEN amount ELSE -amount END) as balance FROM finance WHERE user_id = ? AND EXTRACT(MONTH FROM date) = EXTRACT(MONTH FROM CURRENT_DATE)", [$userId]);
    $goals = $db->fetchAll("SELECT * FROM goals WHERE user_id = ? AND status = 'active' LIMIT 3", [$userId]);
    
    $report = "📊 Daily Report - " . date('F d, Y') . "\n\n";
    
    $report .= "💼 Tasks (" . count($tasks) . " pending):\n";
    foreach ($tasks as $task) {
        $report .= "• " . $task['title'] . "\n";
    }
    
    $report .= "\n💰 Financial Balance:\n";
    $report .= "$" . number_format($finance['balance'] ?? 0, 2) . " this month\n";
    
    $report .= "\n🎯 Active Goals:\n";
    foreach ($goals as $goal) {
        $report .= "• " . $goal['title'] . " (" . $goal['progress'] . "%)\n";
    }
    
    return $report;
}

function getTasksList($userId, $db) {
    $tasks = $db->fetchAll("SELECT * FROM tasks WHERE user_id = ? AND status != 'completed' ORDER BY priority DESC LIMIT 10", [$userId]);
    
    if (empty($tasks)) {
        return "✅ No pending tasks! Great job!";
    }
    
    $response = "📝 Pending Tasks:\n\n";
    foreach ($tasks as $task) {
        $priority = $task['priority'] == 'high' ? '🔴' : ($task['priority'] == 'medium' ? '🟡' : '🟢');
        $response .= "$priority " . $task['title'] . "\n";
    }
    
    return $response;
}

function getFinancialBalance($userId, $db) {
    $balance = $db->fetchOne("SELECT SUM(CASE WHEN type = 'income' THEN amount ELSE -amount END) as balance FROM finance WHERE user_id = ? AND EXTRACT(MONTH FROM date) = EXTRACT(MONTH FROM CURRENT_DATE)", [$userId]);
    $income = $db->fetchColumn("SELECT SUM(amount) FROM finance WHERE user_id = ? AND type = 'income' AND EXTRACT(MONTH FROM date) = EXTRACT(MONTH FROM CURRENT_DATE)", [$userId]) ?: 0;
    $expense = $db->fetchColumn("SELECT SUM(amount) FROM finance WHERE user_id = ? AND type = 'expense' AND EXTRACT(MONTH FROM date) = EXTRACT(MONTH FROM CURRENT_DATE)", [$userId]) ?: 0;
    
    $response = "💰 Financial Summary - " . date('F Y') . "\n\n";
    $response .= "Income: $" . number_format($income, 2) . "\n";
    $response .= "Expenses: $" . number_format($expense, 2) . "\n";
    $response .= "Balance: $" . number_format($balance['balance'] ?? 0, 2);
    
    return $response;
}

function getActiveGoals($userId, $db) {
    $goals = $db->fetchAll("SELECT * FROM goals WHERE user_id = ? AND status = 'active' ORDER BY deadline ASC", [$userId]);
    
    if (empty($goals)) {
        return "🎯 No active goals. Create some in the app!";
    }
    
    $response = "🎯 Active Goals:\n\n";
    foreach ($goals as $goal) {
        $progress = $goal['progress'] ?? 0;
        $progressBar = str_repeat('█', floor($progress / 10)) . str_repeat('░', 10 - floor($progress / 10));
        $response .= $goal['title'] . "\n";
        $response .= "[$progressBar] $progress%\n";
        $response .= "Deadline: " . date('M d, Y', strtotime($goal['deadline'])) . "\n\n";
    }
    
    return $response;
}

function getHealthSummary($userId, $db) {
    $health = $db->fetchOne("SELECT * FROM health WHERE user_id = ? ORDER BY date DESC LIMIT 1", [$userId]);
    
    if (!$health) {
        return "🏥 No health data available. Start tracking in the app!";
    }
    
    $response = "🏥 Health Summary:\n\n";
    
    if (isset($health['weight'])) {
        $response .= "Weight: " . $health['weight'] . " kg\n";
    }
    if (isset($health['water_intake'])) {
        $response .= "Water: " . $health['water_intake'] . " glasses\n";
    }
    if (isset($health['sleep_hours'])) {
        $response .= "Sleep: " . $health['sleep_hours'] . " hours\n";
    }
    if (isset($health['steps'])) {
        $response .= "Steps: " . number_format($health['steps']) . "\n";
    }
    
    $response .= "\nLast updated: " . date('M d, Y', strtotime($health['date']));
    
    return $response;
}

function addTask($userId, $taskName, $db) {
    if (empty($taskName)) {
        return "❌ Please provide a task name. Usage: /addtask [task name]";
    }
    
    try {
        $db->execute(
            "INSERT INTO tasks (user_id, title, status, priority) VALUES (?, ?, 'pending', 'medium')",
            [$userId, $taskName]
        );
        return "✅ Task added: " . $taskName;
    } catch (Exception $e) {
        return "❌ Failed to add task";
    }
}

function addExpense($userId, $amount, $category, $db) {
    $amount = floatval($amount);
    
    if ($amount <= 0) {
        return "❌ Please provide a valid amount. Usage: /addexpense [amount] [category]";
    }
    
    try {
        $db->execute(
            "INSERT INTO finance (user_id, type, amount, category, description, date) VALUES (?, 'expense', ?, ?, 'Added via Telegram', CURRENT_DATE)",
            [$userId, $amount, $category]
        );
        return "✅ Expense logged: $" . number_format($amount, 2) . " for " . $category;
    } catch (Exception $e) {
        return "❌ Failed to log expense";
    }
}
