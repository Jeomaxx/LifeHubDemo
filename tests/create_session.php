<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

$auth = new Auth();
$db = Database::getInstance();

$testUser = $db->fetchOne("SELECT * FROM users WHERE email = ?", ['test@example.com']);

if ($testUser) {
    $_SESSION['user_id'] = $testUser['id'];
    $_SESSION['user_name'] = $testUser['name'];
    $_SESSION['user_email'] = $testUser['email'];
    $_SESSION['logged_in'] = true;
    echo "Session created for user: " . $testUser['email'] . "\n";
    echo "User ID: " . $testUser['id'] . "\n";
    echo "Session ID: " . session_id() . "\n";
} else {
    echo "Test user not found!\n";
}
