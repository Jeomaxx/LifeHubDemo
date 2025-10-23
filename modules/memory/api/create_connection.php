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

$sourceNodeId = intval($_POST['source_node_id'] ?? 0);
$targetNodeId = intval($_POST['target_node_id'] ?? 0);
$connectionType = trim($_POST['connection_type'] ?? 'related');
$strength = floatval($_POST['strength'] ?? 0.5);

if ($sourceNodeId <= 0 || $targetNodeId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Valid node IDs are required']);
    exit;
}

$sourceNode = $db->fetchOne("SELECT * FROM ai_memory_graph WHERE id = ? AND user_id = ?", [$sourceNodeId, $userId]);
$targetNode = $db->fetchOne("SELECT * FROM ai_memory_graph WHERE id = ? AND user_id = ?", [$targetNodeId, $userId]);

if (!$sourceNode || !$targetNode) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'One or both nodes not found']);
    exit;
}

try {
    $existing = $db->fetchOne(
        "SELECT * FROM ai_memory_connections WHERE source_node_id = ? AND target_node_id = ?",
        [$sourceNodeId, $targetNodeId]
    );

    if ($existing) {
        $db->update('ai_memory_connections', [
            'connection_type' => $connectionType,
            'strength' => max(0, min(1, $strength))
        ], 'id = ?', [$existing['id']]);
        
        $connectionId = $existing['id'];
        $message = 'Connection updated successfully';
    } else {
        $connectionId = $db->insert('ai_memory_connections', [
            'source_node_id' => $sourceNodeId,
            'target_node_id' => $targetNodeId,
            'connection_type' => $connectionType,
            'strength' => max(0, min(1, $strength))
        ]);
        
        $message = 'Connection created successfully';
    }

    echo json_encode([
        'success' => true,
        'connection_id' => $connectionId,
        'message' => $message
    ]);
} catch (Exception $e) {
    error_log("Memory connection creation error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to create connection'
    ]);
}
