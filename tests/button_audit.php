<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "==========================================\n";
echo "Button Functionality Audit\n";
echo "==========================================\n\n";

// Scan all PHP files for onclick handlers
$phpFiles = glob('*.php');
$jsFiles = glob('assets/js/*.js');

$onClickFunctions = [];
$definedFunctions = [];

// Extract onclick functions from PHP files
foreach ($phpFiles as $file) {
    $content = file_get_contents($file);
    
    // Match onclick="functionName(...)" patterns
    preg_match_all('/onclick=["\']([a-zA-Z_][a-zA-Z0-9_]*)\s*\(/', $content, $matches);
    
    if (!empty($matches[1])) {
        foreach ($matches[1] as $funcName) {
            if (!isset($onClickFunctions[$funcName])) {
                $onClickFunctions[$funcName] = [];
            }
            $onClickFunctions[$funcName][] = $file;
        }
    }
}

// Extract function definitions from JS files
foreach ($jsFiles as $file) {
    $content = file_get_contents($file);
    
    // Match function declarations
    preg_match_all('/(?:function\s+|async\s+function\s+|const\s+|let\s+|var\s+)([a-zA-Z_][a-zA-Z0-9_]*)\s*(?:=\s*)?(?:async\s*)?\(?/', $content, $matches);
    
    if (!empty($matches[1])) {
        foreach ($matches[1] as $funcName) {
            if (!in_array($funcName, ['i', 'e', 't', 'r', 'n', 'data', 'result', 'error', 'response'])) {
                $definedFunctions[$funcName] = $file;
            }
        }
    }
}

// Check footer for inline functions
$footerContent = file_get_contents('includes/footer.php');
preg_match_all('/function\s+([a-zA-Z_][a-zA-Z0-9_]*)\s*\(/', $footerContent, $matches);
if (!empty($matches[1])) {
    foreach ($matches[1] as $funcName) {
        $definedFunctions[$funcName] = 'includes/footer.php (inline)';
    }
}

echo "ANALYSIS RESULTS\n";
echo "==========================================\n\n";

echo "Total onclick functions found: " . count($onClickFunctions) . "\n";
echo "Total functions defined in JS: " . count($definedFunctions) . "\n\n";

$missing = [];
$found = [];

foreach ($onClickFunctions as $funcName => $files) {
    if (isset($definedFunctions[$funcName])) {
        $found[$funcName] = [
            'used_in' => $files,
            'defined_in' => $definedFunctions[$funcName]
        ];
    } else {
        $missing[$funcName] = $files;
    }
}

echo "MISSING FUNCTIONS (" . count($missing) . ")\n";
echo "==========================================\n";
if (empty($missing)) {
    echo "✅ All functions are defined!\n\n";
} else {
    foreach ($missing as $funcName => $files) {
        echo "❌ $funcName\n";
        echo "   Used in: " . implode(', ', array_unique($files)) . "\n\n";
    }
}

echo "\nFOUND FUNCTIONS (" . count($found) . ")\n";
echo "==========================================\n";
$sampleCount = 0;
foreach ($found as $funcName => $info) {
    if ($sampleCount++ < 10) {
        echo "✅ $funcName\n";
        echo "   Used in: " . implode(', ', array_slice(array_unique($info['used_in']), 0, 3)) . "\n";
        echo "   Defined in: " . $info['defined_in'] . "\n\n";
    }
}
if (count($found) > 10) {
    echo "... and " . (count($found) - 10) . " more\n\n";
}

echo "\nSUMMARY\n";
echo "==========================================\n";
$coverage = count($found) / (count($found) + count($missing)) * 100;
echo "Coverage: " . number_format($coverage, 1) . "%\n";
echo "Missing: " . count($missing) . " functions\n";
echo "Found: " . count($found) . " functions\n";

// Generate recommendations
if (!empty($missing)) {
    echo "\nRECOMMENDATIONS\n";
    echo "==========================================\n";
    echo "The following functions should be added to assets/js/module-utils.js:\n\n";
    
    foreach (array_keys($missing) as $funcName) {
        echo "function $funcName() {\n";
        echo "    // TODO: Implement this function\n";
        echo "    console.log('$funcName called');\n";
        echo "}\n\n";
    }
}

echo "\n==========================================\n";
echo "Audit Complete\n";
echo "==========================================\n";
