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

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $description = $data['description'] ?? '';
    
    if (empty($description)) {
        echo json_encode(['success' => false, 'message' => 'Description is required']);
        exit;
    }
    
    // AI-powered categorization
    $category = categorizeExpense($description, $userId, $db);
    
    echo json_encode(['success' => true, 'category' => $category]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

function categorizeExpense($description, $userId, $db) {
    // Standard expense categories with keywords
    $categories = [
        'Groceries' => ['grocery', 'supermarket', 'food', 'walmart', 'costco', 'whole foods', 'trader joe', 'safeway', 'kroger', 'albertsons', 'market'],
        'Dining & Restaurants' => ['restaurant', 'cafe', 'coffee', 'starbucks', 'mcdonald', 'pizza', 'burger', 'dining', 'bar', 'pub', 'lunch', 'dinner', 'breakfast', 'takeout', 'delivery', 'uber eats', 'doordash', 'grubhub'],
        'Transportation' => ['gas', 'fuel', 'uber', 'lyft', 'taxi', 'parking', 'toll', 'bus', 'train', 'subway', 'metro', 'car', 'auto', 'vehicle', 'transport'],
        'Utilities' => ['electric', 'water', 'gas bill', 'internet', 'phone', 'mobile', 'cable', 'utility', 'verizon', 'at&t', 't-mobile', 'comcast', 'spectrum'],
        'Shopping' => ['amazon', 'ebay', 'target', 'walmart', 'clothing', 'shoes', 'apparel', 'store', 'shop', 'retail', 'purchase', 'buy'],
        'Entertainment' => ['movie', 'cinema', 'theater', 'netflix', 'spotify', 'hulu', 'disney', 'gaming', 'game', 'concert', 'event', 'ticket', 'entertainment'],
        'Healthcare' => ['pharmacy', 'doctor', 'hospital', 'clinic', 'medical', 'health', 'dentist', 'vision', 'cvs', 'walgreens', 'prescription', 'medicine'],
        'Housing' => ['rent', 'mortgage', 'apartment', 'condo', 'property', 'house', 'hoa', 'housing'],
        'Insurance' => ['insurance', 'policy', 'premium', 'coverage', 'geico', 'state farm', 'allstate'],
        'Education' => ['school', 'tuition', 'course', 'education', 'training', 'book', 'learning', 'university', 'college'],
        'Personal Care' => ['salon', 'haircut', 'spa', 'gym', 'fitness', 'personal care', 'beauty', 'cosmetics'],
        'Subscriptions' => ['subscription', 'membership', 'annual fee', 'monthly fee', 'recurring'],
        'Travel' => ['hotel', 'flight', 'airbnb', 'booking', 'travel', 'vacation', 'trip', 'airline'],
        'Other' => []
    ];
    
    $description = strtolower($description);
    
    // Check against keywords
    foreach ($categories as $category => $keywords) {
        if ($category === 'Other') continue;
        
        foreach ($keywords as $keyword) {
            if (strpos($description, $keyword) !== false) {
                return $category;
            }
        }
    }
    
    // Check user's historical patterns
    $historicalCategory = $db->fetchColumn(
        "SELECT category FROM finance 
         WHERE user_id = ? AND LOWER(description) LIKE ? AND category IS NOT NULL 
         ORDER BY date DESC LIMIT 1",
        [$userId, '%' . strtolower($description) . '%']
    );
    
    if ($historicalCategory) {
        return $historicalCategory;
    }
    
    // AI-based categorization using Gemini (if API key is set)
    if (defined('GEMINI_API_KEY') && !empty(GEMINI_API_KEY) && GEMINI_API_KEY !== 'your_gemini_api_key_here') {
        try {
            require_once '../includes/ai_config.php';
            $aiConfig = new AIConfig();
            
            $prompt = "Categorize this expense description into ONE of these categories: " . 
                     implode(', ', array_keys($categories)) . 
                     "\n\nExpense description: " . $description . 
                     "\n\nRespond with ONLY the category name, nothing else.";
            
            $result = $aiConfig->generateContent($prompt);
            $aiCategory = trim($result);
            
            // Validate AI response
            if (array_key_exists($aiCategory, $categories)) {
                return $aiCategory;
            }
        } catch (Exception $e) {
            error_log("AI categorization failed: " . $e->getMessage());
        }
    }
    
    return 'Other';
}
