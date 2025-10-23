<?php
require_once '../../../includes/config.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/db.php';

header('Content-Type: application/json');

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$db = getDB();
$userId = $auth->getUserId();

try {
    $currentDay = date('m-d');
    
    $memories = $db->fetchAll(
        "SELECT id, title, content, sentiment_score, 
                EXTRACT(YEAR FROM created_at) as year,
                created_at
         FROM journal_entries 
         WHERE user_id = ? 
         AND TO_CHAR(created_at, 'MM-DD') = ?
         AND EXTRACT(YEAR FROM created_at) < EXTRACT(YEAR FROM CURRENT_DATE)
         ORDER BY created_at DESC
         LIMIT 10",
        [$userId, $currentDay]
    );

    foreach ($memories as &$memory) {
        $yearsAgo = date('Y') - intval($memory['year']);
        $memory['years_ago'] = $yearsAgo;
        $memory['label'] = $yearsAgo === 1 ? "1 year ago" : "$yearsAgo years ago";
        
        if (!empty($memory['content'])) {
            $memory['preview'] = substr($memory['content'], 0, 200) . '...';
        }
    }

    echo json_encode([
        'success' => true,
        'memories' => $memories,
        'day' => date('F j')
    ]);
} catch (Exception $e) {
    error_log("On this day error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to retrieve memories'
    ]);
}
