<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>UEDF Sentinel Diagnostic</h1>";

// Check file structure
echo "<h2>File Structure</h2>";
$paths = [
    'modules/settings.php',
    'includes/session.php',
    'includes/paths.php',
    'config/settings.php',
    'index.php'
];

foreach ($paths as $path) {
    if (file_exists($path)) {
        echo "✅ $path - " . filesize($path) . " bytes<br>";
    } else {
        echo "❌ $path - MISSING<br>";
    }
}

// Check permissions
echo "<h2>Module Access</h2>";
echo "Current URL: " . $_SERVER['REQUEST_URI'] . "<br>";
echo "Query String: " . ($_SERVER['QUERY_STRING'] ?? 'none') . "<br>";
echo "Module param: " . ($_GET['module'] ?? 'none') . "<br>";

// Try to include settings
echo "<h2>Testing Include</h2>";
if (file_exists('modules/settings.php')) {
    echo "Attempting to include...<br>";
    ob_start();
    include 'modules/settings.php';
    $output = ob_get_clean();
    echo "✅ Include successful<br>";
    echo "Output length: " . strlen($output) . " bytes<br>";
} else {
    echo "❌ Cannot include - file missing<br>";
}

// Test direct access
echo "<h2>Direct Access Test</h2>";
echo "<a href='modules/settings.php'>Open settings.php directly</a><br>";
echo "<a href='settings_page.php'>Open via module parameter</a><br>";
?>
