<?php
// Session ini settings - must be set BEFORE session_start()
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Rest of your session functions remain the same...
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

function getCurrentUsername() {
    return $_SESSION['username'] ?? 'Guest';
}

function getCurrentUserRole() {
    return $_SESSION['user_role'] ?? 'viewer';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function destroySession() {
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}
