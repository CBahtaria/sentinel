<?php
require_once '../src/session.php';

// Clear any existing session issues
if (isset($_GET['reset'])) {
    destroySession();
    header('Location: login.php');
    exit;
}

// Simple check
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = getCurrentUser();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Working</title>
    <meta http-equiv="refresh" content="2;url=dashboard.php">
</head>
<body>
    <h1>Redirecting to dashboard...</h1>
    <p>If you're not redirected, <a href="dashboard.php">click here</a></p>
    <p><a href="?reset=1">Reset Session</a></p>
</body>
</html>
