<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';

header('Content-Type: application/json');

$auth = new Auth();

if (!$auth->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = $auth->getUserId();
$db = Database::getInstance();

$query = $_GET['q'] ?? '';

if (empty($query) || strlen($query) < 2) {
    echo json_encode(['success' => false, 'results' => []]);
    exit;
}

$searchPattern = '%' . $query . '%';
$results = [];

// Search in Tasks
$tasks = $db->fetchAll("
    SELECT 'task' as type, id, title, description, status 
    FROM tasks 
    WHERE user_id = ? AND (title ILIKE ? OR description ILIKE ?)
    LIMIT 5
", [$userId, $searchPattern, $searchPattern]);

foreach ($tasks as $task) {
    $results[] = [
        'type' => 'Task',
        'icon' => 'fa-tasks',
        'title' => $task['title'],
        'description' => $task['description'] ?? '',
        'url' => '/tasks.php?id=' . $task['id'],
        'meta' => 'Status: ' . ucfirst($task['status'])
    ];
}

// Search in Bills
$bills = $db->fetchAll("
    SELECT 'bill' as type, id, title, amount, due_date 
    FROM bills 
    WHERE user_id = ? AND (title ILIKE ? OR vendor ILIKE ?)
    LIMIT 5
", [$userId, $searchPattern, $searchPattern]);

foreach ($bills as $bill) {
    $results[] = [
        'type' => 'Bill',
        'icon' => 'fa-file-invoice-dollar',
        'title' => $bill['title'],
        'description' => 'Due: ' . date('M d, Y', strtotime($bill['due_date'])),
        'url' => '/bills.php?id=' . $bill['id'],
        'meta' => '$' . number_format($bill['amount'], 2)
    ];
}

// Search in Notes
$notes = $db->fetchAll("
    SELECT 'note' as type, id, title, content 
    FROM journal 
    WHERE user_id = ? AND (title ILIKE ? OR content ILIKE ?)
    LIMIT 5
", [$userId, $searchPattern, $searchPattern]);

foreach ($notes as $note) {
    $results[] = [
        'type' => 'Note',
        'icon' => 'fa-sticky-note',
        'title' => $note['title'],
        'description' => substr($note['content'] ?? '', 0, 100),
        'url' => '/journal.php?id=' . $note['id'],
        'meta' => ''
    ];
}

// Search in Goals
$goals = $db->fetchAll("
    SELECT 'goal' as type, id, title, description, status 
    FROM goals 
    WHERE user_id = ? AND (title ILIKE ? OR description ILIKE ?)
    LIMIT 5
", [$userId, $searchPattern, $searchPattern]);

foreach ($goals as $goal) {
    $results[] = [
        'type' => 'Goal',
        'icon' => 'fa-bullseye',
        'title' => $goal['title'],
        'description' => $goal['description'] ?? '',
        'url' => '/goals.php?id=' . $goal['id'],
        'meta' => ucfirst($goal['status'])
    ];
}

// Search in Habits
$habits = $db->fetchAll("
    SELECT 'habit' as type, id, name, description 
    FROM habits 
    WHERE user_id = ? AND (name ILIKE ? OR description ILIKE ?)
    LIMIT 5
", [$userId, $searchPattern, $searchPattern]);

foreach ($habits as $habit) {
    $results[] = [
        'type' => 'Habit',
        'icon' => 'fa-check-circle',
        'title' => $habit['name'],
        'description' => $habit['description'] ?? '',
        'url' => '/habits.php?id=' . $habit['id'],
        'meta' => ''
    ];
}

echo json_encode([
    'success' => true,
    'count' => count($results),
    'results' => $results
]);
