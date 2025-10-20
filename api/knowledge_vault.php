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

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($method) {
        case 'GET':
            if ($action === 'stats') {
                $stats = [
                    'total_items' => $db->fetchColumn("SELECT COUNT(*) FROM knowledge_vault_items WHERE user_id = ?", [$userId]) ?: 0,
                    'total_categories' => $db->fetchColumn("SELECT COUNT(DISTINCT category) FROM knowledge_vault_items WHERE user_id = ? AND category IS NOT NULL", [$userId]) ?: 0,
                    'total_favorites' => $db->fetchColumn("SELECT COUNT(*) FROM knowledge_vault_items WHERE user_id = ? AND is_favorite = true", [$userId]) ?: 0,
                    'total_connections' => $db->fetchColumn("SELECT COUNT(*) FROM knowledge_connections WHERE user_id = ?", [$userId]) ?: 0
                ];
                echo json_encode(['success' => true, 'data' => $stats]);
            } elseif ($action === 'list') {
                $type = $_GET['type'] ?? '';
                $search = $_GET['search'] ?? '';
                
                $query = "SELECT id, item_type, title, source_url, tags, category, ai_summary, ai_keywords, is_favorite, read_count, created_at FROM knowledge_vault_items WHERE user_id = ?";
                $params = [$userId];
                
                if ($type) {
                    $query .= " AND item_type = ?";
                    $params[] = $type;
                }
                
                if ($search) {
                    $query .= " AND (title ILIKE ? OR content ILIKE ? OR tags ILIKE ?)";
                    $searchParam = "%$search%";
                    $params[] = $searchParam;
                    $params[] = $searchParam;
                    $params[] = $searchParam;
                }
                
                $query .= " ORDER BY created_at DESC LIMIT 100";
                
                $items = $db->fetchAll($query, $params);
                echo json_encode(['success' => true, 'data' => $items]);
            } elseif ($action === 'categories') {
                $categories = $db->fetchAll("SELECT category, COUNT(*) as count FROM knowledge_vault_items WHERE user_id = ? AND category IS NOT NULL GROUP BY category ORDER BY count DESC", [$userId]);
                echo json_encode(['success' => true, 'data' => $categories]);
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            if ($action === 'create') {
                $id = $db->insert('knowledge_vault_items', [
                    'user_id' => $userId,
                    'item_type' => $data['item_type'],
                    'title' => $data['title'],
                    'content' => $data['content'] ?? null,
                    'source_url' => $data['source_url'] ?? null,
                    'source_type' => $data['source_type'] ?? null,
                    'tags' => $data['tags'] ?? null,
                    'category' => $data['category'] ?? null,
                    'ai_summary' => $data['ai_summary'] ?? null,
                    'ai_keywords' => $data['ai_keywords'] ?? null
                ]);
                echo json_encode(['success' => true, 'id' => $id, 'message' => 'Knowledge item added successfully']);
            } elseif ($action === 'toggle-favorite') {
                $id = $data['id'];
                $affected = $db->execute("UPDATE knowledge_vault_items SET is_favorite = NOT is_favorite WHERE id = ? AND user_id = ?", [$id, $userId]);
                
                if ($affected === 0) {
                    throw new Exception('Knowledge item not found or access denied');
                }
                echo json_encode(['success' => true, 'message' => 'Favorite toggled']);
            }
            break;

        case 'DELETE':
            $id = $_GET['id'] ?? null;
            if (!$id) throw new Exception('ID required');
            
            $affected = $db->execute("DELETE FROM knowledge_vault_items WHERE id = ? AND user_id = ?", [$id, $userId]);
            if ($affected === 0) {
                throw new Exception('Knowledge item not found or access denied');
            }
            echo json_encode(['success' => true, 'message' => 'Knowledge item deleted']);
            break;

        default:
            throw new Exception('Invalid request method');
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
