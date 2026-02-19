<?php
/**
 * UEDF SENTINEL v4.0 - System Verification
 * Run this to check if everything is working
 */

echo "=============================================\n";
echo "   UEDF SENTINEL v4.0 - SYSTEM CHECK\n";
echo "=============================================\n\n";

// Check PHP version
echo "🔍 PHP Version: " . phpversion() . "\n";
if (version_compare(phpversion(), '7.4.0', '>=')) {
    echo "✅ PHP version OK\n\n";
} else {
    echo "❌ PHP version太低，需要7.4或更高\n\n";
}

// Check MySQL connection
echo "🔍 Checking MySQL connection...\n";
try {
    $pdo = new PDO('mysql:host=localhost;dbname=uedf_sentinel', 'root', '');
    echo "✅ MySQL connected successfully\n";
    
    // Check tables
    $tables = ['users', 'drones', 'threats', 'nodes', 'audit_logs', 'notifications', 'settings'];
    foreach ($tables as $table) {
        $result = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($result->rowCount() > 0) {
            $count = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
            echo "  ✅ Table '$table' exists ($count records)\n";
        } else {
            echo "  ❌ Table '$table' missing\n";
        }
    }
    
    // Check users
    $users = $pdo->query("SELECT username, role FROM users")->fetchAll(PDO::FETCH_ASSOC);
    echo "\n👥 Users in database:\n";
    foreach ($users as $user) {
        echo "  - {$user['username']} ({$user['role']})\n";
    }
    
} catch (PDOException $e) {
    echo "❌ MySQL connection failed: " . $e->getMessage() . "\n";
    echo "   Run install.php to set up database\n";
}

// Check modules directory
echo "\n📁 Checking modules...\n";
$modules_dir = __DIR__ . '/modules';
if (is_dir($modules_dir)) {
    $required_modules = [
        'home.php', 'login.php', 'logout.php', 'dashboard.php', 'drones.php',
        'map.php', 'concurrency.php', 'audit.php', 'analytics.php', 
        'notifications.php', 'admin.php', 'settings.php', 'reports.php',
        'ai-assistant.php'
    ];
    
    foreach ($required_modules as $module) {
        if (file_exists($modules_dir . '/' . $module)) {
            echo "  ✅ $module\n";
        } else {
            echo "  ❌ $module MISSING\n";
        }
    }
} else {
    echo "❌ Modules directory not found\n";
}

// Check includes directory
echo "\n📁 Checking includes...\n";
$includes_dir = __DIR__ . '/includes';
if (is_dir($includes_dir)) {
    $required_includes = ['auth.php'];
    foreach ($required_includes as $include) {
        if (file_exists($includes_dir . '/' . $include)) {
            echo "  ✅ $include\n";
        } else {
            echo "  ❌ $include MISSING\n";
        }
    }
}

// Check JS directory
echo "\n📁 Checking JavaScript...\n";
$js_dir = __DIR__ . '/js';
if (is_dir($js_dir)) {
    $required_js = ['websocket-client.js'];
    foreach ($required_js as $js) {
        if (file_exists($js_dir . '/' . $js)) {
            echo "  ✅ $js\n";
        } else {
            echo "  ❌ $js MISSING\n";
        }
    }
} else {
    echo "  Creating js directory...\n";
    mkdir($js_dir, 0777, true);
}

// Check config directory
echo "\n📁 Checking config...\n";
$config_dir = __DIR__ . '/config';
if (!is_dir($config_dir)) {
    mkdir($config_dir, 0777, true);
    echo "  Created config directory\n";
}

// Check WebSocket server
echo "\n🔌 Checking WebSocket server...\n";
$connection = @fsockopen('localhost', 8081, $errno, $errstr, 1);
if ($connection) {
    echo "✅ WebSocket server is running on port 8081\n";
    fclose($connection);
} else {
    echo "⚠️ WebSocket server not running (start with: php websocket-server.php)\n";
}

// Final summary
echo "\n=============================================\n";
echo "📊 SYSTEM SUMMARY\n";
echo "=============================================\n";
echo "Base URL: http://localhost:8080/sentinel\n";
echo "Login: http://localhost:8080/sentinel/?module=login\n";
echo "Home: http://localhost:8080/sentinel/?module=home\n";
echo "AI Assistant: http://localhost:8080/sentinel/?module=ai-assistant\n";
echo "WebSocket Test: http://localhost:8080/sentinel/websocket-test.php\n";
echo "=============================================\n\n";

echo "✅ To start WebSocket server:\n";
echo "   cd C:\\xampp\\htdocs\\sentinel\n";
echo "   php websocket-server.php\n\n";

echo "🎉 Your UEDF SENTINEL system is ready!\n";
?>
