<?php
// Session handler for Bartarian Defence
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// User functions
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
    return $_SESSION['user_role'] ?? $_SESSION['role'] ?? 'viewer';
}

function getCurrentUserFullName() {
    return $_SESSION['full_name'] ?? getCurrentUsername();
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function requireRole($requiredRole) {
    requireLogin();
    $userRole = getCurrentUserRole();
    $roleHierarchy = [
        'viewer' => 1,
        'analyst' => 2,
        'operator' => 3,
        'commander' => 4,
        'admin' => 5,
        'superadmin' => 6
    ];

    $userLevel = $roleHierarchy[$userRole] ?? 0;
    $requiredLevel = $roleHierarchy[$requiredRole] ?? 0;

    if ($userLevel < $requiredLevel) {
        header('Location: dashboard.php?error=unauthorized');
        exit;
    }
}

function setUserSession($userData) {
    $_SESSION['user_id'] = $userData['id'];
    $_SESSION['username'] = $userData['username'];
    $_SESSION['user_role'] = $userData['role'];
    $_SESSION['full_name'] = $userData['full_name'] ?? $userData['username'];
    $_SESSION['login_time'] = time();
    $_SESSION['session_id'] = session_id();
}

function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'role' => $_SESSION['user_role'],
        'full_name' => $_SESSION['full_name']
    ];
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
