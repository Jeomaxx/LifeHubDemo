<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "========================================\n";
echo "Comprehensive Page Loading Test\n";
echo "========================================\n\n";

// Test if pages load without fatal errors
$pages_to_test = [
    'bills.php',
    'budgets.php',
    'goals.php',
    'habits.php',
    'tasks.php',
    'finance.php',
    'health.php',
    'gym.php',
    'diet.php',
    'journal.php',
    'calendar.php',
    'contacts.php',
    'investments.php',
    'crypto.php',
    'career_center.php',
    'freelance_tracker.php',
    'learning.php',
    'hobbies.php',
    'media.php',
    'family_manager.php',
    'events.php',
    'birthdays.php',
    'documents.php',
    'ai_assistant.php',
    'ai_briefing.php',
    'life_advisor.php',
    'analytics.php'
];

$passed = 0;
$failed = 0;
$errors = [];

foreach ($pages_to_test as $page) {
    echo "Testing $page... ";
    
    if (!file_exists($page)) {
        echo "✗ MISSING\n";
        $failed++;
        $errors[] = "$page - File does not exist";
        continue;
    }
    
    // Check for basic PHP syntax errors
    $output = shell_exec("php -l $page 2>&1");
    if (strpos($output, 'No syntax errors') !== false) {
        echo "✓ PASS\n";
        $passed++;
    } else {
        echo "✗ SYNTAX ERROR\n";
        $failed++;
        $errors[] = "$page - Syntax error: $output";
    }
}

echo "\n========================================\n";
echo "TEST SUMMARY\n";
echo "========================================\n";
echo "Pages Passed: $passed\n";
echo "Pages Failed: $failed\n";
echo "Total Pages:  " . ($passed + $failed) . "\n";
echo "Success Rate: " . round(($passed / ($passed + $failed)) * 100, 2) . "%\n";

if (count($errors) > 0) {
    echo "\nERRORS FOUND:\n";
    foreach ($errors as $error) {
        echo "  - $error\n";
    }
}

echo "========================================\n";
