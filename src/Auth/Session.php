<?php
/**
 * UEDF SENTINEL v4.0 - Session Management Class
 */

class Session {
    public static function checkLogin() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id'])) {
            header('Location: ?module=login');
            exit;
        }
    }
    
    public static function getUser() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id'])) {
            return null;
        }
        
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'] ?? null,
            'role' => $_SESSION['role'] ?? null,
            'full_name' => $_SESSION['full_name'] ?? null
        ];
    }
}
