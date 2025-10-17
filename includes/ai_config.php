<?php

class AIConfig {
    private static $instance = null;
    private $geminiApiKey;
    private $apiEndpoint = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent';
    
    private function __construct() {
        $this->geminiApiKey = getenv('GEMINI_API_KEY') ?: $_ENV['GEMINI_API_KEY'] ?? '';
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function generateContent($prompt, $temperature = 0.7) {
        if (empty($this->geminiApiKey)) {
            throw new Exception('Gemini API key not configured');
        }
        
        $url = $this->apiEndpoint . '?key=' . $this->geminiApiKey;
        
        $data = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => $temperature,
                'maxOutputTokens' => 2048
            ]
        ];
        
        $options = [
            'http' => [
                'header'  => "Content-Type: application/json\r\n",
                'method'  => 'POST',
                'content' => json_encode($data),
                'timeout' => 30
            ]
        ];
        
        $context  = stream_context_create($options);
        $response = @file_get_contents($url, false, $context);
        
        if ($response === false) {
            throw new Exception('Failed to connect to Gemini API');
        }
        
        $result = json_decode($response, true);
        
        if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            return $result['candidates'][0]['content']['parts'][0]['text'];
        }
        
        throw new Exception('Invalid API response');
    }
    
    public function analyzeMood($moodText, $context = []) {
        $prompt = "Analyze this mood/emotion entry and provide insights:\n\n";
        $prompt .= "Entry: $moodText\n\n";
        
        if (!empty($context)) {
            $prompt .= "Context:\n";
            foreach ($context as $key => $value) {
                $prompt .= "- $key: $value\n";
            }
        }
        
        $prompt .= "\nProvide:\n1. Sentiment (positive/neutral/negative)\n2. Key emotions detected\n3. Brief insight or suggestion\n\nRespond in JSON format.";
        
        return $this->generateContent($prompt, 0.5);
    }
    
    public function predictFinancial($transactions, $bills, $range = '1 month') {
        $prompt = "As a financial advisor, analyze this data and predict future cash flow:\n\n";
        $prompt .= "Recent Transactions: " . json_encode($transactions) . "\n";
        $prompt .= "Upcoming Bills: " . json_encode($bills) . "\n";
        $prompt .= "Forecast Range: $range\n\n";
        $prompt .= "Provide:\n1. Predicted balance\n2. Expense forecast\n3. Savings recommendation\n4. Financial risks\n\nRespond in JSON format.";
        
        return $this->generateContent($prompt, 0.3);
    }
    
    public function analyzeGoalProgress($goalData, $activities) {
        $prompt = "Analyze this goal and user activities to provide progress insights:\n\n";
        $prompt .= "Goal: " . json_encode($goalData) . "\n";
        $prompt .= "Recent Activities: " . json_encode($activities) . "\n\n";
        $prompt .= "Provide:\n1. Progress percentage\n2. Success likelihood\n3. Actionable recommendations\n4. Motivational message\n\nRespond in JSON format.";
        
        return $this->generateContent($prompt, 0.6);
    }
    
    public function predictLifeEvents($userData) {
        $prompt = "Analyze user data patterns to predict upcoming life events or situations:\n\n";
        $prompt .= json_encode($userData) . "\n\n";
        $prompt .= "Identify:\n1. Potential events (budget issues, wellness changes, productivity patterns)\n2. Confidence score (0-100)\n3. Preventive actions\n4. Timeline estimate\n\nRespond in JSON format.";
        
        return $this->generateContent($prompt, 0.4);
    }
    
    public function analyzeRelationships($interactions, $events) {
        $prompt = "Analyze relationship patterns and provide insights:\n\n";
        $prompt .= "Interactions: " . json_encode($interactions) . "\n";
        $prompt .= "Events: " . json_encode($events) . "\n\n";
        $prompt .= "Provide:\n1. Connection health scores\n2. Communication patterns\n3. Suggested actions\n4. Sentiment trends\n\nRespond in JSON format.";
        
        return $this->generateContent($prompt, 0.5);
    }
}
