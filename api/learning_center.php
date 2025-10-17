<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/ai_config.php';

header('Content-Type: application/json');

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = $auth->getUserId();
$db = Database::getInstance();
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'add_course':
            $courseName = $_POST['course_name'] ?? '';
            $coursePlatform = $_POST['course_platform'] ?? '';
            $courseUrl = $_POST['course_url'] ?? '';
            $skillCategory = $_POST['skill_category'] ?? '';
            $startDate = $_POST['start_date'] ?? null;
            $targetDate = $_POST['target_completion_date'] ?? null;
            
            $courseId = $db->insert("INSERT INTO learning_courses (user_id, course_name, course_platform, course_url, skill_category, start_date, target_completion_date) VALUES (?, ?, ?, ?, ?, ?, ?)",
                [$userId, $courseName, $coursePlatform, $courseUrl, $skillCategory, $startDate, $targetDate]);
            
            echo json_encode(['success' => true, 'course_id' => $courseId]);
            break;
            
        case 'update_progress':
            $courseId = $_POST['course_id'] ?? 0;
            $progress = $_POST['progress_percentage'] ?? 0;
            $status = $_POST['status'] ?? 'in_progress';
            
            if ($progress >= 100) {
                $status = 'completed';
            }
            
            $db->update("UPDATE learning_courses SET progress_percentage = ?, status = ? WHERE id = ? AND user_id = ?",
                [$progress, $status, $courseId, $userId]);
            
            echo json_encode(['success' => true]);
            break;
            
        case 'add_note':
            $courseId = $_POST['course_id'] ?? 0;
            $noteTitle = $_POST['note_title'] ?? '';
            $noteContent = $_POST['note_content'] ?? '';
            
            $noteId = $db->insert("INSERT INTO learning_notes (course_id, note_title, note_content) VALUES (?, ?, ?)",
                [$courseId, $noteTitle, $noteContent]);
            
            echo json_encode(['success' => true, 'note_id' => $noteId]);
            break;
            
        case 'get_notes':
            $courseId = $_GET['course_id'] ?? 0;
            $notes = $db->fetchAll("SELECT * FROM learning_notes WHERE course_id = ? ORDER BY created_at DESC", [$courseId]) ?: [];
            
            echo json_encode(['success' => true, 'notes' => $notes]);
            break;
            
        case 'ai_recommendations':
            $aiConfig = AIConfig::getInstance();
            
            $career = $db->fetchAll("SELECT * FROM career_projects WHERE user_id = ? AND status = 'active'", [$userId]) ?: [];
            $completedCourses = $db->fetchAll("SELECT skill_category FROM learning_courses WHERE user_id = ? AND status = 'completed'", [$userId]) ?: [];
            $inProgressCourses = $db->fetchAll("SELECT skill_category FROM learning_courses WHERE user_id = ? AND status = 'in_progress'", [$userId]) ?: [];
            
            $prompt = "You are a career development AI. Based on the user's data, recommend 3-5 new skills or courses they should learn. Return JSON with array of recommendations, each having: skill_name, reason, priority (high/medium/low), estimated_time.\n\n";
            $prompt .= "Active Projects: " . json_encode(array_column($career, 'project_name')) . "\n";
            $prompt .= "Completed Skills: " . json_encode(array_column($completedCourses, 'skill_category')) . "\n";
            $prompt .= "Current Learning: " . json_encode(array_column($inProgressCourses, 'skill_category')) . "\n";
            $prompt .= "\nRecommend skills that complement their career path and fill knowledge gaps.";
            
            $aiResponse = $aiConfig->generateContent($prompt);
            $recommendations = json_decode($aiResponse, true);
            
            if (!$recommendations || !isset($recommendations['recommendations'])) {
                $recommendations = [
                    'recommendations' => [
                        ['skill_name' => 'Advanced JavaScript', 'reason' => 'Enhance web development skills', 'priority' => 'high', 'estimated_time' => '2 months'],
                        ['skill_name' => 'Python Data Analysis', 'reason' => 'Learn data-driven decision making', 'priority' => 'medium', 'estimated_time' => '1 month'],
                        ['skill_name' => 'Cloud Computing Basics', 'reason' => 'Modern infrastructure knowledge', 'priority' => 'medium', 'estimated_time' => '3 weeks']
                    ]
                ];
            }
            
            echo json_encode(['success' => true, 'recommendations' => $recommendations['recommendations'] ?? []]);
            break;
            
        case 'delete_course':
            $courseId = $_POST['course_id'] ?? 0;
            $db->delete("DELETE FROM learning_courses WHERE id = ? AND user_id = ?", [$courseId, $userId]);
            echo json_encode(['success' => true]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    error_log("Learning Center API Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}
