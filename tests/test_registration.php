<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$auth = new Auth();

$testName = 'Test User ' . rand(1000, 9999);
$testEmail = 'test' . rand(1000, 9999) . '@example.com';
$testPassword = 'testpass123';

echo "Testing registration with:\n";
echo "Name: $testName\n";
echo "Email: $testEmail\n";
echo "Password: $testPassword\n\n";

$result = $auth->register($testName, $testEmail, $testPassword);

if ($result['success']) {
    echo "✓ Registration successful!\n";
    echo "User ID: " . $result['user_id'] . "\n\n";
    
    echo "Testing login with the new account...\n";
    $loginResult = $auth->login($testEmail, $testPassword);
    
    if ($loginResult['success']) {
        echo "✓ Login successful!\n";
        echo "Account creation and login are working properly!\n";
    } else {
        echo "✗ Login failed: " . $loginResult['message'] . "\n";
    }
} else {
    echo "✗ Registration failed: " . $result['message'] . "\n";
}
