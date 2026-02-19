<?php
require_once 'includes/session.php'; 
 
if (!isset($_SESSION['user_id'])) { header('Location: login_simple.php'); exit; } 
$role = $_SESSION['role'] ?? 'client'; 
$name = $_SESSION['full_name'] ?? 'User'; 
?> 
<!DOCTYPE html> 
<html> 
<head> 
    <title>Home</title> 
    <style> 
        body { background: #0a0f1c; color: white; font-family: Arial; padding: 20px; } 
        .header { background: #151f2c; padding: 15px; border-bottom: 2px solid <?= $role === 'admin' ? '#ff006e' : '#00ff9d' ?>; display: flex; justify-content: space-between; } 
        a { color: #00ff9d; text-decoration: none; padding: 5px 15px; border: 1px solid #00ff9d; } 
        a:hover { background: #00ff9d; color: black; } 
        .admin { color: #ff006e; } 
    </style> 
</head> 
<body> 
    <div class="header"> 
        <div>Welcome, <?= $name ?> (<?= $role ?>)</div> 
        <div><a href="logout_simple.php">Logout</a></div> 
    </div> 
    <h1>Dashboard</h1> 
    <p>You are logged in successfully!</p> 
    <p><a href="index.php">Go to main site</a></p> 
</body> 
</html> 
