<?php
require_once '../../../includes/config.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/db.php';
require_once '../../../includes/functions.php';

header('Content-Type: application/json');

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

$db = getDB();
$userId = $auth->getUserId();

$title = trim($_POST['title'] ?? '');
$content = trim($_POST['content'] ?? '');
$mood = trim($_POST['mood'] ?? '');

if (empty($title)) {
    echo json_encode(['success' => false, 'message' => 'Title is required']);
    exit;
}

if (strlen($title) > 500) {
    echo json_encode(['success' => false, 'message' => 'Title too long']);
    exit;
}

try {
    $entryId = $db->insert('journal_entries', [
        'user_id' => $userId,
        'entry_date' => date('Y-m-d'),
        'title' => $title,
        'content' => $content,
        'mood' => $mood,
        'entry_type' => 'text'
    ]);

    echo json_encode([
        'success' => true,
        'entry_id' => $entryId,
        'message' => 'Journal entry created successfully'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to create journal entry'
    ]);
}
