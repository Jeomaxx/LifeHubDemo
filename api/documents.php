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
                $category = $_GET['category'] ?? '';
                $search = $_GET['search'] ?? '';
                
                $query = "SELECT * FROM documents WHERE user_id = ?";
                $params = [$userId];
                
                if ($category) {
                    $query .= " AND category = ?";
                    $params[] = $category;
                }
                
                if ($search) {
                    $query .= " AND (title LIKE ? OR tags LIKE ? OR ocr_text LIKE ?)";
                    $searchTerm = "%$search%";
                    $params[] = $searchTerm;
                    $params[] = $searchTerm;
                    $params[] = $searchTerm;
                }
                
                $query .= " ORDER BY created_at DESC";
                
                $documents = $db->fetchAll($query, $params);
                echo json_encode(['success' => true, 'documents' => $documents]);
            } elseif ($action === 'single' && isset($_GET['id'])) {
                $doc = $db->fetchOne(
                    "SELECT * FROM documents WHERE id = ? AND user_id = ?",
                    [$_GET['id'], $userId]
                );
                echo json_encode(['success' => true, 'document' => $doc]);
            } elseif ($action === 'versions' && isset($_GET['parent_id'])) {
                $versions = $db->fetchAll(
                    "SELECT * FROM documents WHERE parent_id = ? AND user_id = ? ORDER BY version DESC",
                    [$_GET['parent_id'], $userId]
                );
                echo json_encode(['success' => true, 'versions' => $versions]);
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            if ($action === 'create') {
                $id = $db->insert('documents', [
                    'user_id' => $userId,
                    'title' => $data['title'],
                    'file_path' => $data['file_path'],
                    'file_type' => $data['file_type'] ?? null,
                    'file_size' => $data['file_size'] ?? null,
                    'category' => $data['category'] ?? null,
                    'tags' => $data['tags'] ?? null,
                    'ocr_text' => $data['ocr_text'] ?? null,
                    'ai_summary' => $data['ai_summary'] ?? null,
                    'version' => 1
                ]);
                echo json_encode(['success' => true, 'id' => $id, 'message' => 'Document added successfully']);
            } elseif ($action === 'update' && isset($data['id'])) {
                $db->execute(
                    "UPDATE documents SET 
                        title = ?, category = ?, tags = ?, ai_summary = ?, updated_at = CURRENT_TIMESTAMP
                    WHERE id = ? AND user_id = ?",
                    [$data['title'], $data['category'] ?? null, $data['tags'] ?? null, $data['ai_summary'] ?? null, $data['id'], $userId]
                );
                echo json_encode(['success' => true, 'message' => 'Document updated successfully']);
            } elseif ($action === 'create_version' && isset($data['parent_id'])) {
                // Get parent document
                $parent = $db->fetchOne(
                    "SELECT * FROM documents WHERE id = ? AND user_id = ?",
                    [$data['parent_id'], $userId]
                );
                
                if ($parent) {
                    $id = $db->insert('documents', [
                        'user_id' => $userId,
                        'title' => $parent['title'],
                        'file_path' => $data['file_path'],
                        'file_type' => $data['file_type'] ?? $parent['file_type'],
                        'file_size' => $data['file_size'] ?? null,
                        'category' => $parent['category'],
                        'tags' => $parent['tags'],
                        'version' => $parent['version'] + 1,
                        'parent_id' => $data['parent_id']
                    ]);
                    echo json_encode(['success' => true, 'id' => $id, 'message' => 'New version created successfully']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Parent document not found']);
                }
            }
            break;

        case 'DELETE':
            if (isset($_GET['id'])) {
                $db->execute("DELETE FROM documents WHERE id = ? AND user_id = ?", [$_GET['id'], $userId]);
                echo json_encode(['success' => true, 'message' => 'Document deleted successfully']);
            }
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
