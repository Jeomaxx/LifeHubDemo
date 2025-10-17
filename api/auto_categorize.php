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
    // Enhanced expense categories with comprehensive keywords
    $categories = [
        'Groceries' => ['grocery', 'supermarket', 'food', 'walmart', 'costco', 'whole foods', 'trader joe', 'safeway', 'kroger', 'albertsons', 'market', 'aldi', 'lidl', 'wegmans', 'publix', 'fresh', 'organic', 'produce'],
        'Dining & Restaurants' => ['restaurant', 'cafe', 'coffee', 'starbucks', 'mcdonald', 'pizza', 'burger', 'dining', 'bar', 'pub', 'lunch', 'dinner', 'breakfast', 'takeout', 'delivery', 'uber eats', 'doordash', 'grubhub', 'chipotle', 'subway', 'kfc', 'taco bell', 'wendy', 'chick-fil-a', 'panera', 'dunkin', 'brunch', 'dine'],
        'Transportation' => ['gas', 'fuel', 'uber', 'lyft', 'taxi', 'parking', 'toll', 'bus', 'train', 'subway', 'metro', 'car', 'auto', 'vehicle', 'transport', 'shell', 'chevron', 'exxon', 'bp', 'mobil', 'gas station', 'ride share', 'carpool'],
        'Utilities' => ['electric', 'electricity', 'water', 'gas bill', 'internet', 'phone', 'mobile', 'cable', 'utility', 'verizon', 'at&t', 't-mobile', 'comcast', 'spectrum', 'pge', 'power', 'energy', 'wifi', 'broadband', 'cellular'],
        'Shopping' => ['amazon', 'ebay', 'target', 'walmart', 'clothing', 'shoes', 'apparel', 'store', 'shop', 'retail', 'purchase', 'buy', 'nike', 'adidas', 'zara', 'h&m', 'gap', 'online shopping', 'electronics', 'best buy', 'apple store'],
        'Entertainment' => ['movie', 'cinema', 'theater', 'netflix', 'spotify', 'hulu', 'disney', 'gaming', 'game', 'concert', 'event', 'ticket', 'entertainment', 'youtube', 'amazon prime', 'hbo', 'apple music', 'steam', 'playstation', 'xbox', 'nintendo'],
        'Healthcare' => ['pharmacy', 'doctor', 'hospital', 'clinic', 'medical', 'health', 'dentist', 'vision', 'cvs', 'walgreens', 'prescription', 'medicine', 'rite aid', 'wellness', 'therapy', 'checkup', 'copay', 'lab', 'x-ray'],
        'Housing' => ['rent', 'mortgage', 'apartment', 'condo', 'property', 'house', 'hoa', 'housing', 'lease', 'landlord', 'home repair', 'maintenance'],
        'Insurance' => ['insurance', 'policy', 'premium', 'coverage', 'geico', 'state farm', 'allstate', 'progressive', 'liberty mutual', 'farmers', 'usaa'],
        'Education' => ['school', 'tuition', 'course', 'education', 'training', 'book', 'learning', 'university', 'college', 'udemy', 'coursera', 'skillshare', 'textbook', 'supplies', 'student'],
        'Personal Care' => ['salon', 'haircut', 'spa', 'gym', 'fitness', 'personal care', 'beauty', 'cosmetics', 'barber', 'manicure', 'pedicure', 'massage', 'yoga', 'pilates', 'sephora', 'ulta'],
        'Subscriptions' => ['subscription', 'membership', 'annual fee', 'monthly fee', 'recurring', 'prime', 'plus', 'premium', 'pro'],
        'Travel' => ['hotel', 'flight', 'airbnb', 'booking', 'travel', 'vacation', 'trip', 'airline', 'expedia', 'kayak', 'united', 'delta', 'southwest', 'american airlines', 'marriott', 'hilton', 'rental car'],
        'Pets' => ['pet', 'vet', 'veterinary', 'petco', 'petsmart', 'dog', 'cat', 'animal', 'pet food', 'grooming'],
        'Gifts & Donations' => ['gift', 'donation', 'charity', 'present', 'donate', 'contribution', 'nonprofit'],
        'Bills & Fees' => ['bill', 'fee', 'charge', 'payment', 'late fee', 'service charge', 'overdraft'],
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
