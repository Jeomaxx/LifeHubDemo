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
            if ($action === 'conversations') {
                $conversations = $db->fetchAll(
                    "SELECT c.*, COUNT(m.id) as message_count 
                    FROM ai_conversations c 
                    LEFT JOIN ai_messages m ON c.id = m.conversation_id 
                    WHERE c.user_id = ? 
                    GROUP BY c.id 
                    ORDER BY c.updated_at DESC",
                    [$userId]
                );
                echo json_encode(['success' => true, 'conversations' => $conversations]);
            } elseif ($action === 'messages' && isset($_GET['conversation_id'])) {
                $messages = $db->fetchAll(
                    "SELECT * FROM ai_messages 
                    WHERE conversation_id = ? AND user_id = ? 
                    ORDER BY created_at ASC",
                    [$_GET['conversation_id'], $userId]
                );
                echo json_encode(['success' => true, 'messages' => $messages]);
            } elseif ($action === 'briefing') {
                $date = $_GET['date'] ?? date('Y-m-d');
                $briefing = $db->fetchOne(
                    "SELECT * FROM ai_briefings WHERE user_id = ? AND briefing_date = ?",
                    [$userId, $date]
                );
                
                if (!$briefing) {
                    // Generate new briefing
                    $briefingContent = generateDailyBriefing($userId, $db);
                    $db->insert('ai_briefings', [
                        'user_id' => $userId,
                        'briefing_date' => $date,
                        'briefing_content' => $briefingContent
                    ]);
                    $briefing = ['briefing_content' => $briefingContent, 'briefing_date' => $date];
                }
                echo json_encode(['success' => true, 'briefing' => $briefing]);
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            if ($action === 'create_conversation') {
                $id = $db->insert('ai_conversations', [
                    'user_id' => $userId,
                    'conversation_title' => $data['title'] ?? 'New Conversation'
                ]);
                echo json_encode(['success' => true, 'id' => $id, 'message' => 'Conversation created']);
            } elseif ($action === 'send_message') {
                $messageId = $db->insert('ai_messages', [
                    'conversation_id' => $data['conversation_id'],
                    'user_id' => $userId,
                    'role' => 'user',
                    'content' => $data['content']
                ]);
                
                // Update conversation timestamp
                $db->execute(
                    "UPDATE ai_conversations SET updated_at = CURRENT_TIMESTAMP WHERE id = ?",
                    [$data['conversation_id']]
                );
                
                // Here you would integrate with AI API (OpenAI, Gemini, etc.)
                $aiResponse = "This is a placeholder response. Integrate with your AI API here.";
                
                $responseId = $db->insert('ai_messages', [
                    'conversation_id' => $data['conversation_id'],
                    'user_id' => $userId,
                    'role' => 'assistant',
                    'content' => $aiResponse
                ]);
                
                echo json_encode([
                    'success' => true, 
                    'user_message_id' => $messageId,
                    'ai_message_id' => $responseId,
                    'response' => $aiResponse
                ]);
            }
            break;

        case 'DELETE':
            if ($action === 'conversation' && isset($_GET['id'])) {
                $db->execute("DELETE FROM ai_conversations WHERE id = ? AND user_id = ?", [$_GET['id'], $userId]);
                echo json_encode(['success' => true, 'message' => 'Conversation deleted']);
            }
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

function generateDailyBriefing($userId, $db) {
    $briefing = "Good morning! Here's your daily briefing:\n\n";
    
    // Tasks due today
    $tasksDue = $db->fetchColumn(
        "SELECT COUNT(*) FROM tasks WHERE user_id = ? AND due_date = CURRENT_DATE",
        [$userId]
    );
    if ($tasksDue > 0) {
        $briefing .= "📋 You have $tasksDue task(s) due today.\n";
    }
    
    // Bills due soon
    $billsDue = $db->fetchColumn(
        "SELECT COUNT(*) FROM bills WHERE user_id = ? AND due_date BETWEEN CURRENT_DATE AND CURRENT_DATE + INTERVAL '7 days' AND payment_status != 'paid'",
        [$userId]
    );
    if ($billsDue > 0) {
        $briefing .= "💰 You have $billsDue bill(s) due in the next 7 days.\n";
    }
    
    // Upcoming birthdays
    $birthdays = $db->fetchColumn(
        "SELECT COUNT(*) FROM birthdays WHERE user_id = ? AND DATE_PART('month', birth_date) = DATE_PART('month', CURRENT_DATE + INTERVAL '7 days') AND DATE_PART('day', birth_date) <= DATE_PART('day', CURRENT_DATE + INTERVAL '7 days')",
        [$userId]
    );
    if ($birthdays > 0) {
        $briefing .= "🎂 $birthdays birthday(s) coming up soon.\n";
    }
    
    $briefing .= "\nHave a productive day!";
    return $briefing;
}
