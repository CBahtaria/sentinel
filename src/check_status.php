<?php
$conn = new mysqli('localhost', 'root', '', 'sentinel');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "=== DATABASE STATUS ===\n\n";

// Show tables
$result = $conn->query("SHOW TABLES");
echo "Tables in database:\n";
while ($row = $result->fetch_array()) {
    echo "  - " . $row[0] . "\n";
}

// Check users table specifically
$result = $conn->query("SELECT COUNT(*) as count FROM users");
if ($result) {
    $row = $result->fetch_assoc();
    echo "\nUsers in database: " . $row['count'] . "\n";
}

$conn->close();
