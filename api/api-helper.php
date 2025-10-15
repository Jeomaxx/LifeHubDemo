<?php

function authenticateApiRequest() {
    $headers = getallheaders();
    $token = null;
    
    if (isset($headers['Authorization'])) {
        $auth = $headers['Authorization'];
        if (preg_match('/Bearer\s+(.+)/', $auth, $matches)) {
            $token = $matches[1];
        }
    }
    
    if (!$token) {
        http_response_code(401);
        echo json_encode(['error' => 'No token provided']);
        exit;
    }
    
    $db = Database::getInstance();
    $apiToken = $db->queryOne(
        "SELECT at.*, u.id as user_id, u.name, u.email 
         FROM api_tokens at
         JOIN users u ON at.user_id = u.id
         WHERE at.token = ? AND at.expires_at > NOW()",
        [$token]
    );
    
    if (!$apiToken) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid or expired token']);
        exit;
    }
    
    return $apiToken;
}

function sendApiResponse($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data);
    exit;
}

function sendApiError($message, $code = 400) {
    http_response_code($code);
    echo json_encode(['error' => $message]);
    exit;
}
