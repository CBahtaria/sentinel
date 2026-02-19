<?php
require_once '../src/session.php';

// Check login
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = getCurrentUser();
?>
<!DOCTYPE html>
<html>
<head>
    <title>UEDF Sentinel Dashboard</title>
    <style>
        body { background: #0a0f1c; color: #00ff9d; font-family: monospace; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; background: #151f2c; padding: 30px; border-radius: 10px; }
        h1 { color: #ff006e; }
        .welcome { background: #0a0f1c; padding: 20px; border-radius: 5px; margin: 20px 0; }
        .menu a { color: #ff006e; text-decoration: none; margin-right: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>?? UEDF Sentinel Dashboard</h1>
        
        <div class="menu">
            <a href="dashboard.php">Home</a>
            <a href="logout.php">Logout</a>
        </div>

        <div class="welcome">
            <h2>Welcome, <?= htmlspecialchars($user['username']) ?>!</h2>
            <p>Role: <?= htmlspecialchars($user['role']) ?></p>
            <p>Session ID: <?= session_id() ?></p>
        </div>

        <p><a href="diagnostic.php">Run Diagnostic</a></p>
    </div>
</body>
</html>
