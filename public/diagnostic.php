<?php
// COMPLETELY STANDALONE TEST - No dependencies on other files
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html>
<head>
    <title>UEDF Sentinel - Diagnostic Tool</title>
    <style>
        body { background: #0a0f1c; color: #00ff9d; font-family: monospace; padding: 20px; }
        h1 { color: #ff006e; }
        .success { color: #00ff9d; }
        .error { color: #ff006e; }
        .box { border: 1px solid #ff006e; padding: 10px; margin: 10px 0; }
        pre { background: #151f2c; padding: 10px; }
    </style>
</head>
<body>
    <h1>🔧 UEDF Sentinel Diagnostic Tool</h1>";

// Test 1: PHP Version
echo "<div class='box'>";
echo "<h3>Test 1: PHP Version</h3>";
echo "PHP Version: " . phpversion() . "<br>";
echo "</div>";

// Test 2: MySQL Connection
echo "<div class='box'>";
echo "<h3>Test 2: MySQL Connection</h3>";
try {
    $pdo = new PDO("mysql:host=localhost;charset=utf8mb4", "root", "");
    echo "<span class='success'>✅ MySQL connection successful</span><br>";
    
    // List databases
    $stmt = $pdo->query("SHOW DATABASES");
    $dbs = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Available databases:<br>";
    foreach ($dbs as $db) {
        echo "  • " . htmlspecialchars($db) . "<br>";
    }
} catch (PDOException $e) {
    echo "<span class='error'>❌ MySQL connection failed: " . $e->getMessage() . "</span>";
}
echo "</div>";

// Test 3: Check uedf_sentinel database
echo "<div class='box'>";
echo "<h3>Test 3: Check uedf_sentinel Database</h3>";
try {
    $pdo = new PDO("mysql:host=localhost;dbname=uedf_sentinel;charset=utf8mb4", "root", "");
    echo "<span class='success'>✅ Connected to uedf_sentinel database</span><br>";
    
    // Show tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if (count($tables) > 0) {
        echo "Tables found:<br>";
        foreach ($tables as $table) {
            echo "  • " . htmlspecialchars($table) . "<br>";
        }
    } else {
        echo "No tables found in database<br>";
    }
    
    // Check users table specifically
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "<br><span class='success'>✅ Users table exists</span><br>";
        
        // Count users
        $count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        echo "Users in database: $count<br>";
        
        if ($count > 0) {
            $users = $pdo->query("SELECT username, role FROM users")->fetchAll();
            echo "User list:<br>";
            foreach ($users as $user) {
                echo "  • " . htmlspecialchars($user['username']) . " (" . htmlspecialchars($user['role']) . ")<br>";
            }
        }
    } else {
        echo "<br><span class='error'>❌ Users table does not exist</span><br>";
    }
    
} catch (PDOException $e) {
    echo "<span class='error'>❌ Cannot connect to uedf_sentinel: " . $e->getMessage() . "</span>";
}
echo "</div>";

// Test 4: Session Test
echo "<div class='box'>";
echo "<h3>Test 4: Session Test</h3>";
session_start();
echo "Session ID: " . session_id() . "<br>";
$_SESSION['test'] = time();
echo "Session data written: test = " . $_SESSION['test'] . "<br>";
echo "</div>";

// Test 5: Simple Login Simulation
echo "<div class='box'>";
echo "<h3>Test 5: Login Form Test</h3>";
echo "<form method='POST' style='margin-top: 10px;'>";
echo "Username: <input type='text' name='test_user' value='admin'><br>";
echo "Password: <input type='text' name='test_pass' value='Admin123!'><br>";
echo "<input type='submit' value='Test Login'>";
echo "</form>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_user'])) {
    $user = $_POST['test_user'];
    $pass = $_POST['test_pass'];
    
    echo "<br>Login attempt with: $user / $pass<br>";
    
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=uedf_sentinel;charset=utf8mb4", "root", "");
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$user]);
        $dbUser = $stmt->fetch();
        
        if ($dbUser) {
            echo "User found in database<br>";
            echo "Stored hash: " . substr($dbUser['password_hash'], 0, 20) . "...<br>";
            echo "Role: " . $dbUser['role'] . "<br>";
            
            if (password_verify($pass, $dbUser['password_hash'])) {
                echo "<span class='success'>✅ Password verification successful!</span><br>";
            } else {
                echo "<span class='error'>❌ Password verification failed</span><br>";
            }
        } else {
            echo "<span class='error'>❌ User not found in database</span><br>";
        }
    } catch (PDOException $e) {
        echo "<span class='error'>❌ Database error: " . $e->getMessage() . "</span>";
    }
}
echo "</div>";

echo "</body></html>";
?>
