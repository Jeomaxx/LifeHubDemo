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
$module = $_GET['module'] ?? '';

if (!$query) {
    echo json_encode(['success' => false, 'message' => 'Query is required']);
    exit;
}

try {
    $results = [];
    $searchPattern = "%$query%";
    
    if (!$module || $module === 'tasks') {
        $tasks = $db->fetchAll("SELECT 'task' as type, id, title as result_title, description as result_desc, created_at FROM tasks WHERE user_id = ? AND (title ILIKE ? OR description ILIKE ?) LIMIT 10", [$userId, $searchPattern, $searchPattern]);
        $results = array_merge($results, $tasks);
    }
    
    if (!$module || $module === 'notes') {
        $notes = $db->fetchAll("SELECT 'note' as type, id, title as result_title, SUBSTRING(content, 1, 200) as result_desc, created_at FROM notes WHERE user_id = ? AND (title ILIKE ? OR content ILIKE ?) LIMIT 10", [$userId, $searchPattern, $searchPattern]);
        $results = array_merge($results, $notes);
    }
    
    if (!$module || $module === 'finance') {
        $finance = $db->fetchAll("SELECT 'finance' as type, id, category as result_title, description as result_desc, date as created_at FROM finance WHERE user_id = ? AND (category ILIKE ? OR description ILIKE ?) LIMIT 10", [$userId, $searchPattern, $searchPattern]);
        $results = array_merge($results, $finance);
    }
    
    if (!$module || $module === 'goals') {
        $goals = $db->fetchAll("SELECT 'goal' as type, id, title as result_title, description as result_desc, created_at FROM goals WHERE user_id = ? AND (title ILIKE ? OR description ILIKE ?) LIMIT 10", [$userId, $searchPattern, $searchPattern]);
        $results = array_merge($results, $goals);
    }
    
    if (!$module || $module === 'journal') {
        $journal = $db->fetchAll("SELECT 'journal' as type, id, title as result_title, SUBSTRING(content, 1, 200) as result_desc, entry_date as created_at FROM journal WHERE user_id = ? AND (title ILIKE ? OR content ILIKE ?) LIMIT 10", [$userId, $searchPattern, $searchPattern]);
        $results = array_merge($results, $journal);
    }
    
    if (!$module || $module === 'documents') {
        $docs = $db->fetchAll("SELECT 'document' as type, id, file_name as result_title, description as result_desc, upload_date as created_at FROM documents WHERE user_id = ? AND (file_name ILIKE ? OR description ILIKE ?) LIMIT 10", [$userId, $searchPattern, $searchPattern]);
        $results = array_merge($results, $docs);
    }
    
    usort($results, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
    
    $results = array_slice($results, 0, 50);
    
    echo json_encode([
        'success' => true,
        'query' => $query,
        'total_results' => count($results),
        'data' => $results
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
