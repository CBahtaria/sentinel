<?php
// Test direct database authentication
require 'src/db_connect.php';

$username = 'commander';
$password = 'Password123!';

echo "?? Testing direct authentication for: $username\n\n";

$db = getDB();

// Get user from database
$stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

if ($user) {
    echo "? User found in database\n";
    echo "Stored hash: " . $user['password'] . "\n";
    
    // Test password verification
    if (password_verify($password, $user['password'])) {
        echo "? Password verification PASSED\n";
    } else {
        echo "? Password verification FAILED\n";
        
        // Try different verification methods
        echo "\nTrying alternative verification:\n";
        if (md5($password) === $user['password']) {
            echo "  - Matches MD5\n";
        }
        if (sha1($password) === $user['password']) {
            echo "  - Matches SHA1\n";
        }
        if ($password === $user['password']) {
            echo "  - Matches plain text\n";
        }
    }
} else {
    echo "? User not found\n";
}

// Show the login form HTML for reference
echo "\n?? To see what your login page expects, check: http://localhost:8080/login.php\n";
