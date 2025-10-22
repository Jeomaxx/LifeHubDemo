<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

echo "========================================\n";
echo "Life Atlas Organizer - System Test\n";
echo "========================================\n\n";

$db = Database::getInstance();
$testsPassed = 0;
$testsFailed = 0;

function testModule($name, $callback) {
    global $testsPassed, $testsFailed;
    echo "Testing: $name... ";
    try {
        $result = $callback();
        if ($result) {
            echo "✓ PASS\n";
            $testsPassed++;
        } else {
            echo "✗ FAIL\n";
            $testsFailed++;
        }
    } catch (Exception $e) {
        echo "✗ ERROR: " . $e->getMessage() . "\n";
        $testsFailed++;
    }
}

echo "1. DATABASE CONNECTIVITY TESTS\n";
echo "-------------------------------\n";

testModule("Database connection", function() use ($db) {
    $result = $db->getConnection();
    return $result !== null;
});

testModule("Users table exists", function() use ($db) {
    $result = $db->query("SELECT COUNT(*) FROM users");
    return $result !== false;
});

testModule("Bills table exists", function() use ($db) {
    $result = $db->query("SELECT COUNT(*) FROM bills");
    return $result !== false;
});

testModule("Goals table exists", function() use ($db) {
    $result = $db->query("SELECT COUNT(*) FROM goals");
    return $result !== false;
});

testModule("Habits table exists", function() use ($db) {
    $result = $db->query("SELECT COUNT(*) FROM habits");
    return $result !== false;
});

testModule("Tasks table exists", function() use ($db) {
    $result = $db->query("SELECT COUNT(*) FROM tasks");
    return $result !== false;
});

testModule("Finance table exists", function() use ($db) {
    $result = $db->query("SELECT COUNT(*) FROM finance");
    return $result !== false;
});

testModule("Accounts table exists", function() use ($db) {
    $result = $db->query("SELECT COUNT(*) FROM accounts");
    return $result !== false;
});

testModule("Investments table exists", function() use ($db) {
    $result = $db->query("SELECT COUNT(*) FROM investments");
    return $result !== false;
});

testModule("Health table exists", function() use ($db) {
    $result = $db->query("SELECT COUNT(*) FROM health");
    return $result !== false;
});

testModule("Gym_sessions table exists", function() use ($db) {
    $result = $db->query("SELECT COUNT(*) FROM gym_sessions");
    return $result !== false;
});

testModule("Journal table exists", function() use ($db) {
    $result = $db->query("SELECT COUNT(*) FROM journal");
    return $result !== false;
});

testModule("Calendar_events table exists", function() use ($db) {
    $result = $db->query("SELECT COUNT(*) FROM calendar_events");
    return $result !== false;
});

testModule("Contacts table exists", function() use ($db) {
    $result = $db->query("SELECT COUNT(*) FROM contacts");
    return $result !== false;
});

testModule("Documents table exists", function() use ($db) {
    $result = $db->query("SELECT COUNT(*) FROM documents");
    return $result !== false;
});

echo "\n2. AUTHENTICATION TESTS\n";
echo "------------------------\n";

testModule("Create test user", function() {
    $auth = new Auth();
    $testEmail = 'systemtest' . rand(1000, 9999) . '@test.com';
    $result = $auth->register('System Test User', $testEmail, 'testpass123');
    return $result['success'] === true;
});

echo "\n3. HELPER FUNCTION TESTS\n";
echo "-------------------------\n";

testModule("sanitize() function", function() {
    $input = "<script>alert('xss')</script>Test";
    $output = sanitize($input);
    return strpos($output, '<script>') === false;
});

testModule("formatCurrency() function", function() {
    $result = formatCurrency(1234.56);
    return $result === '$1,234.56';
});

testModule("formatDate() function", function() {
    $result = formatDate('2025-10-22', 'Y-m-d');
    return $result === '2025-10-22';
});

echo "\n4. STATS CALCULATION TESTS\n";
echo "----------------------------\n";

testModule("getStats() function", function() use ($db) {
    $result = $db->fetchOne("SELECT id FROM users LIMIT 1");
    if ($result) {
        $stats = getStats($result['id']);
        return is_array($stats) && isset($stats['assets']);
    }
    return true;
});

echo "\n5. FILE EXISTENCE TESTS\n";
echo "------------------------\n";

$criticalFiles = [
    'index.php',
    'login.php',
    'register.php',
    'dashboard.php',
    'bills.php',
    'goals.php',
    'habits.php',
    'finance.php',
    'health.php',
    'calendar.php',
    'contacts.php',
    'includes/config.php',
    'includes/db.php',
    'includes/auth.php',
    'includes/functions.php',
    'assets/css/style.css',
    'assets/js/main.js'
];

foreach ($criticalFiles as $file) {
    testModule("File exists: $file", function() use ($file) {
        return file_exists($file);
    });
}

echo "\n========================================\n";
echo "TEST SUMMARY\n";
echo "========================================\n";
echo "Tests Passed: $testsPassed\n";
echo "Tests Failed: $testsFailed\n";
echo "Total Tests:  " . ($testsPassed + $testsFailed) . "\n";
echo "Success Rate: " . round(($testsPassed / ($testsPassed + $testsFailed)) * 100, 2) . "%\n";
echo "========================================\n";
