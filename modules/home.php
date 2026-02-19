<?php
// Start session if needed
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Simple check
if (!isset($_SESSION['user_id'])) {
    header('Location: ?module=login');
    exit;
}

$full_name = $_SESSION['full_name'] ?? 'Charles Bartaria';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Bartaria Defense</title>
    <style>
        body { background: #0a0f1c; color: white; font-family: Arial; padding: 20px; }
        h1 { color: #00ff9d; }
        .card { background: #1a1f2e; padding: 20px; border-radius: 10px; margin: 20px 0; }
        a { color: #00ff9d; }
    </style>
</head>
<body>
    <h1>⚡ BARTARIA DEFENSE</h1>
    <div class="card">
        <p>Welcome, <?php echo htmlspecialchars($full_name); ?>!</p>
        <p>System is working.</p>
    </div>
    <p><a href="?module=logout">Logout</a></p>
</body>
</html>