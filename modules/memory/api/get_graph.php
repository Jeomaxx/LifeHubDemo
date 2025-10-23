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
        "SELECT id, node_title, node_content, node_type, tags, created_at 
         FROM ai_memory_graph 
         WHERE user_id = ? 
         ORDER BY created_at DESC 
         LIMIT 100",
        [$userId]
    );

    $connections = $db->fetchAll(
        "SELECT c.* 
         FROM ai_memory_connections c
         INNER JOIN ai_memory_graph n1 ON c.source_node_id = n1.id
         INNER JOIN ai_memory_graph n2 ON c.target_node_id = n2.id
         WHERE n1.user_id = ? AND n2.user_id = ?",
        [$userId, $userId]
    );

    foreach ($nodes as &$node) {
        if ($node['tags']) {
            $node['tags'] = json_decode($node['tags'], true) ?? [];
        }
    }

    echo json_encode([
        'success' => true,
        'nodes' => $nodes,
        'connections' => $connections
    ]);
} catch (Exception $e) {
    error_log("Graph retrieval error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to retrieve graph'
    ]);
}
