<?php
// Absolute paths - no guessing
$root_path = 'C:/xampp/htdocs/sentinel';

// Include session using absolute path
require_once $root_path . '/includes/session.php';

// Simple test to see if we're working
echo "<!-- Settings module loaded at " . date('Y-m-d H:i:s') . " -->";
?>
<!DOCTYPE html>
<html>
<head>
    <title>UEDF Settings</title>
    <style>
        body { font-family: Arial; background: #0a0f1e; color: white; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; }
        h1 { color: #00ff00; }
        .card { background: #1a1f2e; padding: 20px; border-radius: 10px; margin: 20px 0; }
        .success { color: #00ff00; }
        .error { color: #ff4444; }
    </style>
</head>
<body>
    <div class="container">
        <h1>⚙️ Settings Module Test</h1>
        
        <div class="card">
            <h2>System Check</h2>
            <?php
            // Check if session works
            if (session_status() === PHP_SESSION_ACTIVE) {
                echo "<p class='success'>✅ Session is active</p>";
            } else {
                echo "<p class='error'>❌ Session is not active</p>";
            }
            
            // Check root path
            if (is_dir($root_path)) {
                echo "<p class='success'>✅ Root path exists: $root_path</p>";
            } else {
                echo "<p class='error'>❌ Root path not found: $root_path</p>";
            }
            
            // Check includes directory
            if (is_dir($root_path . '/includes')) {
                echo "<p class='success'>✅ Includes directory exists</p>";
            } else {
                echo "<p class='error'>❌ Includes directory missing</p>";
            }
            
            // Current file info
            echo "<p>📁 Current file: " . __FILE__ . "</p>";
            echo "<p>📁 Current directory: " . __DIR__ . "</p>";
            ?>
        </div>
        
        <div class="card">
            <h2>Navigation</h2>
            <p><a href="?module=dashboard" style="color: #00ff00;">← Back to Dashboard</a></p>
            <p><a href="index.php" style="color: #00ff00;">🏠 Home</a></p>
        </div>
    </div>
</body>
</html>
