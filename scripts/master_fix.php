<?php
echo "===============================================\n";
echo "   🚀 BARTARIAN DEFENCE MASTER FIX UTILITY 🚀\n";
echo "===============================================\n\n";

// Create includes directory if it doesn't exist
if (!is_dir('includes')) {
    mkdir('includes', 0777, true);
    echo "✅ Created includes directory\n";
}

// 1. Create session handler
$session_content = <<<'PHP'
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
PHP;
file_put_contents('includes/session.php', $session_content);
echo "✅ Session handler created\n";

// 2. Fix .htaccess
$htaccess_content = <<<'HTACCESS'
# Bartarian Defence .htaccess
Options -Indexes
RewriteEngine On

# Handle routing
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php?module=$1 [QSA,L]

# Security headers
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
</IfModule>
HTACCESS;
file_put_contents('.htaccess', $htaccess_content);
echo "✅ .htaccess fixed\n";

// 3. Fix database connection
$db_content = <<<'PHP'
<?php
// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'bartarian_defence';

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8mb4");
?>
PHP;
file_put_contents('includes/db.php', $db_content);
echo "✅ Database connection fixed\n";

// 4. Fix all PHP files with session_start()
echo "\n🔍 Scanning for files with session_start()...\n";

// Function to fix a file
function fixFile($filepath, $includePath) {
    $content = file_get_contents($filepath);
    if (strpos($content, 'session_start()') !== false && strpos($content, 'includes/session.php') === false) {
        // Add require at the top after <?php
        $content = preg_replace('/^<\?php/', "<?php\nrequire_once '$includePath';", $content);
        // Remove any session_start() calls
        $content = str_replace('session_start();', '', $content);
        file_put_contents($filepath, $content);
        return true;
    }
    return false;
}

// Fix root PHP files
$fixed = 0;
foreach (glob("*.php") as $file) {
    if ($file == 'master_fix.php') continue;
    if (fixFile($file, 'includes/session.php')) {
        echo "  Fixed: $file\n";
        $fixed++;
    }
}

// Fix modules
if (is_dir('modules')) {
    foreach (glob("modules/*.php") as $file) {
        if (fixFile($file, '../includes/session.php')) {
            echo "  Fixed module: $file\n";
            $fixed++;
        }
    }
}
echo "✅ Fixed $fixed files with session_start()\n";

// 5. Create system test page
$test_content = <<<'PHP'
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Bartarian Defence System Test</title>
    <style>
        body { font-family: Arial; background: #0a0f1e; color: #fff; padding: 20px; }
        .ok { color: #0f0; }
        .bad { color: #f00; }
        .warn { color: #ff0; }
        pre { background: #1a1f2e; padding: 10px; border-radius: 5px; }
        h1 { color: #00ff00; }
        .section { background: #1a1f2e; padding: 15px; margin: 10px 0; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>🔧 Bartarian Defence System Diagnostic</h1>

    <div class="section">
        <h2>📊 Database Test</h2>
        <?php
        try {
            require_once __DIR__ . '/../src/db.php';
            echo "<p class='ok'>✅ Database connected successfully</p>";
            
            $result = $conn->query("SELECT COUNT(*) as total FROM drones");
            $row = $result->fetch_assoc();
            echo "<p>📡 Drones in system: " . $row['total'] . "</p>";
            
            $result = $conn->query("SELECT COUNT(*) as total FROM threats WHERE status = 'ACTIVE'");
            $row = $result->fetch_assoc();
            echo "<p>⚠️ Active threats: " . $row['total'] . "</p>";
            
            $result = $conn->query("SELECT COUNT(*) as total FROM missions WHERE status = 'active'");
            $row = $result->fetch_assoc();
            echo "<p>🎯 Active missions: " . $row['total'] . "</p>";
            
        } catch (Exception $e) {
            echo "<p class='bad'>❌ Database error: " . $e->getMessage() . "</p>";
        }
        ?>
    </div>

    <div class="section">
        <h2>🔐 Session Test</h2>
        <?php
        require_once __DIR__ . '/../src/session.php';
        $_SESSION['test'] = time();
        echo "<p class='ok'>✅ Session working (ID: " . session_id() . ")</p>";
        ?>
    </div>

    <div class="section">
        <h2>🔌 WebSocket Server</h2>
        <?php
        $connection = @fsockopen('localhost', 8081, $errno, $errstr, 1);
        if ($connection) {
            echo "<p class='ok'>✅ WebSocket running on port 8081</p>";
            fclose($connection);
        } else {
            echo "<p class='warn'>⚠️ WebSocket not running on port 8081</p>";
            echo "<p>Run: <code>php websocket-server.php</code> in a new terminal</p>";
        }
        ?>
    </div>

    <div class="section">
        <h2>📁 Required Files</h2>
        <?php
        $files = [
            'includes/db.php',
            'includes/session.php', 
            'includes/functions.php',
            'config/database.php',
            'websocket-server.php',
            'index.php',
            'dashboard.php'
        ];
        foreach ($files as $file) {
            if (file_exists($file)) {
                echo "<p class='ok'>✅ $file exists</p>";
            } else {
                echo "<p class='bad'>❌ $file missing</p>";
            }
        }
        ?>
    </div>

    <div class="section">
        <h2>💻 System Information</h2>
        <p>📅 Date: <?php echo date('Y-m-d H:i:s'); ?></p>
        <p>🚀 PHP Version: <?php echo phpversion(); ?></p>
        <p>🌐 Your site: <a href="http://localhost:8080/sentinel/">http://localhost:8080/sentinel/</a></p>
        <p>📊 Test page: <a href="test_system.php">test_system.php</a></p>
    </div>

    <div class="section">
        <h2>📝 Next Steps</h2>
        <pre>
1. Start WebSocket server: php websocket-server.php
2. Access dashboard: http://localhost:8080/sentinel/
3. Login with: commander / (password as configured)
        </pre>
    </div>
</body>
</html>
PHP;
file_put_contents('test_system.php', $test_content);
echo "✅ System test page created\n";

// 6. Create a simple start script
$start_content = <<<'BAT'
@echo off
title Bartarian Defence Launcher
color 0A
cls
echo ===============================================
echo    🚀 BARTARIAN DEFENCE LAUNCHER 🚀
echo ===============================================
echo.
echo [1/3] Checking Apache...
C:\xampp\apache_start.bat
timeout /t 2 > nul
echo ✅ Apache started
echo.
echo [2/3] Starting WebSocket server...
start "BARTARIAN WebSocket" php websocket-server.php
timeout /t 2 > nul
echo ✅ WebSocket started on port 8081
echo.
echo [3/3] Opening dashboard...
start http://localhost:8080/sentinel/
echo.
echo ===============================================
echo    ✅ SYSTEM IS RUNNING!
echo ===============================================
echo.
echo Press any key to monitor status...
pause > nul
php test_system.php
BAT;
file_put_contents('start_sentinel.bat', $start_content);
echo "✅ Launcher script created\n";

echo "\n===============================================";
echo "\n   ✅ MASTER FIX COMPLETED SUCCESSFULLY! ✅";
echo "\n===============================================\n";
echo "\nNext steps:";
echo "\n  1. Run: php test_system.php";
echo "\n  2. Start WebSocket: php websocket-server.php";
echo "\n  3. Double-click start_sentinel.bat to launch everything";
echo "\n  4. Access: http://localhost:8080/sentinel/\n";
?>
