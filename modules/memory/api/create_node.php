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
$nodeType = trim($_POST['node_type'] ?? 'note');
$tags = $_POST['tags'] ?? [];

if (empty($title)) {
    echo json_encode(['success' => false, 'message' => 'Title is required']);
    exit;
}

try {
    $embedding = null;
    if (!empty($content)) {
        $wordCount = str_word_count($content);
        $charCount = strlen($content);
        $embedding = json_encode([
            'word_count' => $wordCount,
            'char_count' => $charCount,
            'keywords' => array_slice(str_word_count($content, 1), 0, 10)
        ]);
    }

    $nodeId = $db->insert('ai_memory_graph', [
        'user_id' => $userId,
        'node_title' => $title,
        'node_content' => $content,
        'node_type' => $nodeType,
        'tags' => is_array($tags) ? json_encode($tags) : $tags,
        'embedding' => $embedding
    ]);

    echo json_encode([
        'success' => true,
        'node_id' => $nodeId,
        'message' => 'Knowledge node created successfully'
    ]);
} catch (Exception $e) {
    error_log("Memory node creation error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to create knowledge node'
    ]);
}
