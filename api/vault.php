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
            if ($action === 'list') {
                $type = $_GET['type'] ?? '';
                
                if ($type) {
                    $items = $db->fetchAll(
                        "SELECT id, user_id, item_type, title, tags, created_at, updated_at FROM vault_items WHERE user_id = ? AND item_type = ? ORDER BY created_at DESC",
                        [$userId, $type]
                    );
                } else {
                    $items = $db->fetchAll(
                        "SELECT id, user_id, item_type, title, tags, created_at, updated_at FROM vault_items WHERE user_id = ? ORDER BY created_at DESC",
                        [$userId]
                    );
                }
                echo json_encode(['success' => true, 'items' => $items]);
            } elseif ($action === 'single' && isset($_GET['id'])) {
                // Return full item including encrypted content
                $item = $db->fetchOne(
                    "SELECT * FROM vault_items WHERE id = ? AND user_id = ?",
                    [$_GET['id'], $userId]
                );
                echo json_encode(['success' => true, 'item' => $item]);
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            if ($action === 'create') {
                $id = $db->insert('vault_items', [
                    'user_id' => $userId,
                    'item_type' => $data['item_type'],
                    'title' => $data['title'],
                    'encrypted_content' => $data['encrypted_content'],
                    'encryption_key_id' => $data['encryption_key_id'] ?? null,
                    'tags' => $data['tags'] ?? null
                ]);
                echo json_encode(['success' => true, 'id' => $id, 'message' => 'Item added to vault successfully']);
            } elseif ($action === 'update' && isset($data['id'])) {
                $db->execute(
                    "UPDATE vault_items SET 
                        title = ?, encrypted_content = ?, encryption_key_id = ?, tags = ?, updated_at = CURRENT_TIMESTAMP
                    WHERE id = ? AND user_id = ?",
                    [
                        $data['title'], $data['encrypted_content'], $data['encryption_key_id'] ?? null,
                        $data['tags'] ?? null, $data['id'], $userId
                    ]
                );
                echo json_encode(['success' => true, 'message' => 'Vault item updated successfully']);
            }
            break;

        case 'DELETE':
            if (isset($_GET['id'])) {
                $db->execute("DELETE FROM vault_items WHERE id = ? AND user_id = ?", [$_GET['id'], $userId]);
                echo json_encode(['success' => true, 'message' => 'Vault item deleted successfully']);
            }
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
