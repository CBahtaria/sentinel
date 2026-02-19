<?php
// Simple database and auth test
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>UEDF SENTINEL - System Test</h1>";

try {
    // Test 1: Include files
    echo "<h3>Test 1: Including files...</h3>";
    require_once '../src/SentinelDB.php';
    require_once '../src/Auth.php';
    echo "✅ Files included successfully<br>";

    // Test 2: Database connection
    echo "<h3>Test 2: Database connection...</h3>";
    $pdo = SentinelDB::getInstance();
    echo "✅ Connected to database<br>";

    // Test 3: Check users table
    echo "<h3>Test 3: Checking users table...</h3>";
    $result = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($result->rowCount() > 0) {
        echo "✅ Users table exists<br>";
        
        $count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        echo "👥 Users in database: $count<br>";
        
        if ($count > 0) {
            $users = $pdo->query("SELECT username, role FROM users")->fetchAll();
            echo "<ul>";
            foreach ($users as $user) {
                echo "<li>{$user['username']} - {$user['role']}</li>";
            }
            echo "</ul>";
        }
    } else {
        echo "❌ Users table not found<br>";
    }

    // Test 4: Try authentication
    echo "<h3>Test 4: Testing authentication...</h3>";
    $auth = new SentinelAuth();
    
    // Try default admin
    $result = $auth->login('admin', 'Admin123!');
    if ($result['success']) {
        echo "✅ Login successful with admin/Admin123!<br>";
        echo "User: " . $result['user']['username'] . " (" . $result['user']['role'] . ")<br>";
    } else {
        echo "❌ Admin login failed: " . ($result['error'] ?? 'Unknown error') . "<br>";
    }
    
    // Try commander
    $result = $auth->login('commander', 'Password123!');
    if ($result['success']) {
        echo "✅ Login successful with commander/Password123!<br>";
    } else {
        echo "❌ Commander login failed: " . ($result['error'] ?? 'Unknown error') . "<br>";
    }

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
