<?php
require_once '../includes/config.php';
require_once '../includes/db.php';

header('Content-Type: application/json');

$db = Database::getInstance();

function generateApiToken() {
    return bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['email']) || !isset($data['password'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Email and password required']);
        exit;
    }
    
    $user = $db->queryOne("SELECT * FROM users WHERE email = ?", [$data['email']]);
    
    if (!$user || !password_verify($data['password'], $user['password'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid credentials']);
        exit;
    }
    
    $token = generateApiToken();
    $expires_at = date('Y-m-d H:i:s', strtotime('+30 days'));
    
    $db->query(
        "INSERT INTO api_tokens (user_id, token, expires_at, created_at) VALUES (?, ?, ?, NOW())",
        [$user['id'], $token, $expires_at]
    );
    
    echo json_encode([
        'success' => true,
        'token' => $token,
        'expires_at' => $expires_at,
        'user' => [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email']
        ]
    ]);
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
