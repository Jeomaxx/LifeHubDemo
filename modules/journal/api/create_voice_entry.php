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

$title = trim($_POST['title'] ?? '');
$transcription = trim($_POST['transcription'] ?? '');
$audioPath = trim($_POST['audio_path'] ?? '');
$duration = intval($_POST['duration'] ?? 0);

if (empty($title) || empty($transcription)) {
    echo json_encode(['success' => false, 'message' => 'Title and transcription are required']);
    exit;
}

try {
    $sentiment = analyzeSentiment($transcription);
    $emotionalSummary = generateEmotionalSummary($transcription, $sentiment);

    $entryId = $db->insert('journal_entries', [
        'user_id' => $userId,
        'title' => $title,
        'content' => $transcription,
        'entry_type' => 'voice',
        'audio_path' => $audioPath,
        'duration' => $duration,
        'sentiment_score' => $sentiment['score'] ?? 0,
        'emotional_summary' => $emotionalSummary,
        'tags' => json_encode($sentiment['keywords'] ?? [])
    ]);

    echo json_encode([
        'success' => true,
        'entry_id' => $entryId,
        'sentiment' => $sentiment,
        'emotional_summary' => $emotionalSummary,
        'message' => 'Voice entry created successfully'
    ]);
} catch (Exception $e) {
    error_log("Voice entry creation error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to create voice entry'
    ]);
}

function analyzeSentiment($text) {
    $positiveWords = ['happy', 'joy', 'excited', 'grateful', 'love', 'amazing', 'wonderful', 'great', 'excellent', 'fantastic'];
    $negativeWords = ['sad', 'angry', 'frustrated', 'disappointed', 'hate', 'terrible', 'awful', 'bad', 'horrible', 'depressed'];
    
    $textLower = strtolower($text);
    $positiveCount = 0;
    $negativeCount = 0;
    $keywords = [];
    
    foreach ($positiveWords as $word) {
        if (strpos($textLower, $word) !== false) {
            $positiveCount++;
            $keywords[] = $word;
        }
    }
    
    foreach ($negativeWords as $word) {
        if (strpos($textLower, $word) !== false) {
            $negativeCount++;
            $keywords[] = $word;
        }
    }
    
    $totalEmotionalWords = $positiveCount + $negativeCount;
    $score = $totalEmotionalWords > 0 ? ($positiveCount - $negativeCount) / $totalEmotionalWords : 0;
    
    $sentiment = 'neutral';
    if ($score > 0.3) $sentiment = 'positive';
    elseif ($score < -0.3) $sentiment = 'negative';
    
    return [
        'score' => round($score, 2),
        'sentiment' => $sentiment,
        'positive_count' => $positiveCount,
        'negative_count' => $negativeCount,
        'keywords' => array_unique($keywords)
    ];
}

function generateEmotionalSummary($text, $sentiment) {
    $summary = "Overall mood: " . ucfirst($sentiment['sentiment']) . ". ";
    
    if ($sentiment['positive_count'] > 0) {
        $summary .= "Found " . $sentiment['positive_count'] . " positive emotional indicators. ";
    }
    if ($sentiment['negative_count'] > 0) {
        $summary .= "Found " . $sentiment['negative_count'] . " negative emotional indicators. ";
    }
    
    $wordCount = str_word_count($text);
    $summary .= "Entry contains $wordCount words.";
    
    return $summary;
}
