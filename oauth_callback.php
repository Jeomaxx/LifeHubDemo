<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/oauth_config.php';
require_once 'includes/functions.php';

session_start();

if (!isGoogleOAuthConfigured()) {
    die('Google OAuth is not configured. Please set GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET environment variables.');
}

$provider = getGoogleProvider();

if (empty($_GET['code'])) {
    $authUrl = $provider->getAuthorizationUrl(['scope' => ['email', 'profile']]);
    $_SESSION['oauth2state'] = $provider->getState();
    header('Location: ' . $authUrl);
    exit;
}

if (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
    unset($_SESSION['oauth2state']);
    $_SESSION['error'] = 'Invalid OAuth state. Please try again.';
    header('Location: /login.php');
    exit;
}

try {
    $token = $provider->getAccessToken('authorization_code', [
        'code' => $_GET['code']
    ]);
    
    $user = $provider->getResourceOwner($token);
    $googleId = $user->getId();
    $email = $user->getEmail();
    $name = $user->getName();
    $profilePicture = $user->getAvatar();
    
    $db = Database::getInstance();
    
    $existingUser = $db->fetchOne(
        "SELECT * FROM users WHERE oauth_provider = ? AND oauth_id = ?", 
        ['google', $googleId]
    );
    
    if (!$existingUser) {
        $existingUser = $db->fetchOne(
            "SELECT * FROM users WHERE email = ?", 
            [$email]
        );
        
        if ($existingUser) {
            $db->execute(
                "UPDATE users SET oauth_provider = ?, oauth_id = ?, profile_picture = ? WHERE id = ?",
                ['google', $googleId, $profilePicture, $existingUser['id']]
            );
        } else {
            $userId = $db->insert('users', [
                'name' => $name,
                'email' => $email,
                'password' => password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT),
                'oauth_provider' => 'google',
                'oauth_id' => $googleId,
                'profile_picture' => $profilePicture,
                'settings' => json_encode(['theme' => 'light', 'notifications' => true])
            ]);
            
            $existingUser = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$userId]);
        }
    } else {
        $db->execute(
            "UPDATE users SET profile_picture = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?",
            [$profilePicture, $existingUser['id']]
        );
    }
    
    $_SESSION['user_id'] = $existingUser['id'];
    $_SESSION['user_name'] = $existingUser['name'];
    $_SESSION['user_email'] = $existingUser['email'];
    $_SESSION['is_admin'] = $existingUser['is_admin'];
    $_SESSION['logged_in'] = true;
    
    session_regenerate_id(true);
    
    header('Location: /dashboard.php');
    exit;
    
} catch (Exception $e) {
    $_SESSION['error'] = 'OAuth authentication failed: ' . $e->getMessage();
    header('Location: /login.php');
    exit;
}
