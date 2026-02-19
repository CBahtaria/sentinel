<?php
echo "🛡️ BARTARIAN DEFENCE - DATABASE DIAGNOSTIC\n";
echo "==========================================\n\n";

// Test 1: Check if MySQL is running
echo "Test 1: MySQL Process\n";
$process = shell_exec('tasklist | findstr mysqld');
if ($process) {
    echo "   ✅ MySQL is running\n";
} else {
    echo "   ❌ MySQL is not running\n";
}

// Test 2: Try to connect
echo "\nTest 2: Database Connection\n";
try {
    $pdo = new PDO("mysql:host=localhost", "root", "");
    echo "   ✅ Connected to MySQL server\n";
    
    // Test 3: Check databases
    echo "\nTest 3: Available Databases\n";
    $stmt = $pdo->query("SHOW DATABASES");
    $dbs = $stmt->fetchAll(PDO::FETCH_COLUMN);
    foreach ($dbs as $db) {
        echo "   • " . $db;
        if ($db == 'bartarian_defence') echo " ⭐ (our database)";
        if ($db == 'uedf_sentinel') echo " ⏪ (old database)";
        echo "\n";
    }
    
    // Test 4: Check bartarian_defence
    echo "\nTest 4: Bartarian Defence Database\n";
    $stmt = $pdo->query("SHOW DATABASES LIKE 'bartarian_defence'");
    if ($stmt->fetch()) {
        echo "   ✅ Database exists\n";
        
        $pdo->exec("USE bartarian_defence");
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        echo "   📊 Tables: " . count($tables) . "\n";
        
        if (in_array('users', $tables)) {
            $count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
            echo "   👥 Users: $count\n";
        }
    } else {
        echo "   ❌ Database not found\n";
    }
    
} catch (PDOException $e) {
    echo "   ❌ Connection failed: " . $e->getMessage() . "\n";
}

// Test 5: PHP Configuration
echo "\nTest 5: PHP Configuration\n";
echo "   • PHP Version: " . phpversion() . "\n";
echo "   • PDO Drivers: " . implode(', ', PDO::getAvailableDrivers()) . "\n";
echo "   • Display Errors: " . (ini_get('display_errors') ? 'ON' : 'OFF') . "\n";
