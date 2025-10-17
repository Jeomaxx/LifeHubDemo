<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = $auth->getUserId();
$db = Database::getInstance();
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            $type = $_GET['type'] ?? 'courses';
            
            if ($type === 'courses') {
                $courses = $db->fetchAll("SELECT * FROM courses WHERE user_id = ? ORDER BY created_at DESC", [$userId]);
                echo json_encode(['success' => true, 'data' => $courses]);
            } elseif ($type === 'books') {
                $books = $db->fetchAll("SELECT * FROM books WHERE user_id = ? ORDER BY created_at DESC", [$userId]);
                echo json_encode(['success' => true, 'data' => $books]);
            } elseif ($type === 'flashcards') {
                $flashcards = $db->fetchAll("SELECT * FROM flashcards WHERE user_id = ? ORDER BY created_at DESC", [$userId]);
                echo json_encode(['success' => true, 'data' => $flashcards]);
            } elseif ($type === 'stats') {
                $stats = [
                    'total_courses' => $db->fetchOne("SELECT COUNT(*) as count FROM courses WHERE user_id = ?", [$userId])['count'] ?? 0,
                    'completed_courses' => $db->fetchOne("SELECT COUNT(*) as count FROM courses WHERE user_id = ? AND status = 'completed'", [$userId])['count'] ?? 0,
                    'total_books' => $db->fetchOne("SELECT COUNT(*) as count FROM books WHERE user_id = ?", [$userId])['count'] ?? 0,
                    'completed_books' => $db->fetchOne("SELECT COUNT(*) as count FROM books WHERE user_id = ? AND status = 'completed'", [$userId])['count'] ?? 0
                ];
                echo json_encode(['success' => true, 'data' => $stats]);
            }
            break;
            
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $type = $data['type'] ?? 'course';
            
            if ($type === 'course') {
                $id = $db->insert('courses', [
                    'user_id' => $userId,
                    'title' => $data['title'],
                    'platform' => $data['platform'] ?? '',
                    'instructor' => $data['instructor'] ?? '',
                    'course_url' => $data['course_url'] ?? '',
                    'status' => $data['status'] ?? 'not_started',
                    'progress' => $data['progress'] ?? 0,
                    'notes' => $data['notes'] ?? ''
                ]);
                echo json_encode(['success' => true, 'id' => $id]);
            } elseif ($type === 'book') {
                $id = $db->insert('books', [
                    'user_id' => $userId,
                    'title' => $data['title'],
                    'author' => $data['author'] ?? '',
                    'isbn' => $data['isbn'] ?? '',
                    'status' => $data['status'] ?? 'to_read',
                    'total_pages' => $data['total_pages'] ?? 0,
                    'notes' => $data['notes'] ?? ''
                ]);
                echo json_encode(['success' => true, 'id' => $id]);
            } elseif ($type === 'flashcard') {
                $id = $db->insert('flashcards', [
                    'user_id' => $userId,
                    'front_text' => $data['front_text'],
                    'back_text' => $data['back_text'],
                    'category' => $data['category'] ?? '',
                    'difficulty' => $data['difficulty'] ?? 'medium'
                ]);
                echo json_encode(['success' => true, 'id' => $id]);
            }
            break;
            
        case 'DELETE':
            $id = (int)($_GET['id'] ?? 0);
            $type = $_GET['type'] ?? 'course';
            
            if ($type === 'course') $db->delete('courses', $id);
            elseif ($type === 'book') $db->delete('books', $id);
            elseif ($type === 'flashcard') $db->delete('flashcards', $id);
            
            echo json_encode(['success' => true]);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
