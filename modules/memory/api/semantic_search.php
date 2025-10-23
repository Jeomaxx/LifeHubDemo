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

$query = trim($_GET['q'] ?? '');

if (empty($query)) {
    echo json_encode(['success' => false, 'message' => 'Search query is required']);
    exit;
}

try {
    $nodes = $db->fetchAll(
        "SELECT id, node_title, node_content, node_type, tags, created_at
         FROM ai_memory_graph 
         WHERE user_id = ? 
         AND (node_title ILIKE ? OR node_content ILIKE ? OR tags::text ILIKE ?)
         ORDER BY created_at DESC
         LIMIT 50",
        [$userId, "%$query%", "%$query%", "%$query%"]
    );

    foreach ($nodes as &$node) {
        if ($node['tags']) {
            $node['tags'] = json_decode($node['tags'], true) ?? [];
        }
        
        if (!empty($node['node_content'])) {
            $pos = stripos($node['node_content'], $query);
            if ($pos !== false) {
                $start = max(0, $pos - 50);
                $node['snippet'] = '...' . substr($node['node_content'], $start, 150) . '...';
            } else {
                $node['snippet'] = substr($node['node_content'], 0, 150) . '...';
            }
        }
    }

    echo json_encode([
        'success' => true,
        'nodes' => $nodes,
        'query' => $query
    ]);
} catch (Exception $e) {
    error_log("Semantic search error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Search failed'
    ]);
}
