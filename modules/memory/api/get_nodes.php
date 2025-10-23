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
    $nodes = $db->fetchAll(
        "SELECT * FROM knowledge_nodes WHERE user_id = ? ORDER BY created_at DESC LIMIT 50",
        [$userId]
    );

    foreach ($nodes as &$node) {
        $node['tags'] = json_decode($node['tags'] ?? '[]', true);
    }

    echo json_encode([
        'success' => true,
        'nodes' => $nodes
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch knowledge nodes'
    ]);
}
