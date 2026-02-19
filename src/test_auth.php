<?php
// Include required files
require_once 'src/SentinelDB.php';
require_once 'src/Auth.php';

echo "=== UEDF SENTINEL Authentication Test ===\n\n";

try {
    // Test database connection
    echo "Testing database connection... ";
    $pdo = SentinelDB::getInstance();
    echo "✓ OK\n";

    // Create auth instance
    $auth = new SentinelAuth();

    // Check if users table exists
    echo "Checking users table... ";
    $result = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($result->rowCount() > 0) {
        echo "✓ Found\n";

        // Count users
        $count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        echo "Users in database: $count\n\n";

        if ($count > 0) {
            // List users
            echo "User list:\n";
            $users = $pdo->query("SELECT id, username, email, role, is_active FROM users")->fetchAll();
            foreach ($users as $user) {
                echo "  - {$user['username']} ({$user['role']}) - Active: {$user['is_active']}\n";
            }
            echo "\n";
        } else {
            echo "No users found. Creating default admin...\n";
            // Auth class should create default admin automatically
        }
    } else {
        echo "✗ Not found - tables may need to be created\n";
        echo "The Auth class will create tables on first use.\n\n";
    }

    // Test login with default credentials
    echo "Testing login with admin/Admin123!...\n";
    $result = $auth->login('admin', 'Admin123!');

    if ($result['success']) {
        echo "✓ Login successful!\n";
        echo "  User: " . $result['user']['username'] . "\n";
        echo "  Role: " . $result['user']['role'] . "\n";
    } else {
        echo "✗ Login failed: " . ($result['error'] ?? 'Unknown error') . "\n";
    }

} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

echo "\nTest complete.\n";
