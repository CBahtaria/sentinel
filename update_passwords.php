<?php
require 'src/db_connect.php';
$db = getDB();

$plainPassword = 'Password123!';
$hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);

echo "Updating all users to use: '$plainPassword'\n";
echo "New hash: $hashedPassword\n\n";

$stmt = $db->prepare("UPDATE users SET password = ?");
$result = $stmt->execute([$hashedPassword]);

if ($result) {
    echo "? Updated " . $stmt->rowCount() . " users successfully!\n";
}

// Verify the update
echo "\n?? Verifying:\n";
$users = $db->query("SELECT username, password FROM users");
while ($user = $users->fetch()) {
    $verify = password_verify($plainPassword, $user['password']);
    $status = $verify ? "? works" : "? fails";
    echo "  - {$user['username']}: $status\n";
}
