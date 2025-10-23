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

$date = $_GET['date'] ?? date('Y-m-d');

try {
    $entries = $db->fetchAll(
        "SELECT * FROM journal_entries 
         WHERE user_id = ? AND DATE(created_at) = ? 
         ORDER BY created_at DESC",
        [$userId, $date]
    );

    $totalEntries = count($entries);
    $avgSentiment = 0;
    $dominantEmotions = [];
    $totalWordCount = 0;

    foreach ($entries as $entry) {
        if (isset($entry['sentiment_score'])) {
            $avgSentiment += floatval($entry['sentiment_score']);
        }
        if (isset($entry['content'])) {
            $totalWordCount += str_word_count($entry['content']);
        }
        if (isset($entry['tags'])) {
            $tags = json_decode($entry['tags'], true);
            if (is_array($tags)) {
                $dominantEmotions = array_merge($dominantEmotions, $tags);
            }
        }
    }

    $avgSentiment = $totalEntries > 0 ? $avgSentiment / $totalEntries : 0;
    $dominantEmotions = array_unique($dominantEmotions);
    $dominantEmotions = array_slice($dominantEmotions, 0, 5);

    $reflection = [
        'date' => $date,
        'total_entries' => $totalEntries,
        'average_sentiment' => round($avgSentiment, 2),
        'total_words' => $totalWordCount,
        'dominant_emotions' => $dominantEmotions,
        'entries' => $entries
    ];

    echo json_encode([
        'success' => true,
        'reflection' => $reflection
    ]);
} catch (Exception $e) {
    error_log("Daily reflection error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to retrieve daily reflection'
    ]);
}
