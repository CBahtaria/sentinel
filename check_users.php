<?php
require 'src/db_connect.php';
try {
    $db = getDB();
    $result = $db->query('SELECT COUNT(*) as count FROM users');
    $row = $result->fetch();
    echo "? Users in database: " . $row['count'] . "\n";
    
    // Show the users
    $users = $db->query('SELECT username, role FROM users');
    echo "\n?? User list:\n";
    while ($user = $users->fetch()) {
        echo "  - " . $user['username'] . " (" . $user['role'] . ")\n";
    }
} catch (Exception $e) {
    echo "? Error: " . $e->getMessage() . "\n";
}
