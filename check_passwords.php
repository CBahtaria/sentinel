<?php
require 'src/db_connect.php';
$db = getDB();
$users = $db->query("SELECT username, password, role FROM users");
echo "?? Current users in database:\n\n";
while ($user = $users->fetch()) {
    echo "Username: " . $user['username'] . "\n";
    echo "Password: " . $user['password'] . "\n";
    echo "Role: " . $user['role'] . "\n";
    echo "------------------------\n";
}
