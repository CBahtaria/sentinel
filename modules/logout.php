<?php
require_once __DIR__ . '/../includes/session.php';
/**
 * BARTARIA DEFENSE SYSTEM - Logout Module
 */

// Log the logout action if user was logged in
if (isset($_SESSION['user_id'])) {
    // You can add logout logging here if needed
    $username = $_SESSION['username'] ?? 'Unknown';
    error_log("Logout: User {$username} logged out at " . date('Y-m-d H:i:s'));
}

// Unset all session variables
$_SESSION = array();

// Delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Redirect to login page with logout message
header('Location: ?module=login&logout=success');
exit;
?>