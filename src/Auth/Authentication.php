<?php
namespace Sentinel\Auth;

use Sentinel\Security\RateLimiter;
use Sentinel\Security\AuditLogger;
use Sentinel\Security\CSRF;

class Authentication {
    private $pdo;
ECHO is on.
    public function __construct() {
        $this->pdo = \SentinelDB::getInstance();
        $this->initSession();
    }
ECHO is on.
    private function initSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_name('SENTINEL_SESSID');
ECHO is on.
            // Secure session settings
            ini_set('session.cookie_httponly', 1);
            ini_set('session.use_only_cookies', 1);
            ini_set('session.cookie_secure', 1);
            ini_set('session.cookie_samesite', 'Strict');
            ini_set('session.gc_maxlifetime', $_ENV['SESSION_LIFETIME'] ?? 7200);
            ini_set('session.cookie_lifetime', $_ENV['SESSION_LIFETIME'] ?? 7200);
ECHO is on.
            session_start();
        }
ECHO is on.
        if (!isset($_SESSION['created'])) {
            $_SESSION['created'] = time();
        } else if (time() - $_SESSION['created'] > 300) {
            session_regenerate_id(true);
            $_SESSION['created'] = time();
        }
    }
ECHO is on.
    public function login($username, $password, $twofaCode = null) {
        $key = 'login:' . $_SERVER['REMOTE_ADDR'];
        $rateCheck = RateLimiter::check($key);
ECHO is on.
        if (!$rateCheck['allowed']) {
            AuditLogger::log('rate_limit_exceeded', [
                'username' => $username,
                'wait' => $rateCheck['wait']
            ], 'denied');
ECHO is on.
            return [
                'success' => false,
                'error' => 'Too many attempts. Try again in ' . ceil($rateCheck['wait'] / 60) . ' minutes'
            ];
        }
ECHO is on.
        if (!CSRF::validate($_POST['csrf_token'] ?? null)) {
            RateLimiter::increment($key);
            return ['success' => false, 'error' => 'Invalid security token'];
        }
ECHO is on.
        try {
            $stmt = $this->pdo->prepare("
                SELECT id, username, email, password_hash, role, twofa_secret, twofa_enabled, is_active 
                FROM users 
                WHERE username = ? OR email = ?
            ");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();
ECHO is on.
            if (!$user) {
                RateLimiter::increment($key);
                AuditLogger::log('login_failed', ['username' => $username, 'reason' => 'user_not_found'], 'denied');
                return ['success' => false, 'error' => 'Invalid credentials'];
            }
ECHO is on.
            if (!$user['is_active']) {
                AuditLogger::log('login_failed', ['username' => $username, 'reason' => 'account_disabled'], 'denied');
                return ['success' => false, 'error' => 'Account disabled'];
            }
ECHO is on.
            if (!password_verify($password, $user['password_hash'])) {
                RateLimiter::increment($key);
                AuditLogger::log('login_failed', ['username' => $username, 'reason' => 'wrong_password'], 'denied');
                return ['success' => false, 'error' => 'Invalid credentials'];
            }
ECHO is on.
            if ($user['twofa_enabled']) {
                if (!$twofaCode) {
                    return ['success' => false, 'requires_2fa' => true];
                }
ECHO is on.
                $tfa = new \RobThree\Auth\TwoFactorAuth($_ENV['APP_NAME']);
                if (!$tfa->verifyCode($user['twofa_secret'], $twofaCode)) {
                    RateLimiter::increment($key);
                    AuditLogger::log('login_failed', ['username' => $username, 'reason' => 'invalid_2fa'], 'denied');
                    return ['success' => false, 'error' => 'Invalid 2FA code'];
                }
            }
ECHO is on.
            RateLimiter::clear($key);
            session_regenerate_id(true);
ECHO is on.
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['logged_in_at'] = time();
            $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
            $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
ECHO is on.
            $stmt = $this->pdo->prepare("
                UPDATE users 
                SET last_login_ip = ?, last_login_at = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$_SERVER['REMOTE_ADDR'], $user['id']]);
ECHO is on.
            AuditLogger::log('login_success', [
                'username' => $user['username'],
                'role' => $user['role']
            ], 'success');
ECHO is on.
            return ['success' => true, 'user' => $user];
ECHO is on.
        } catch (\Exception $e) {
            error_log("Login error: " . $e->getMessage());
            RateLimiter::increment($key);
            return ['success' => false, 'error' => 'System error'];
        }
    }
ECHO is on.
    public function logout() {
        $userId = $_SESSION['user_id'] ?? null;
        $username = $_SESSION['username'] ?? 'unknown';
ECHO is on.
        AuditLogger::log('logout', ['username' => $username], 'success');
ECHO is on.
        $_SESSION = [];
ECHO is on.
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
ECHO is on.
        session_destroy();
    }
ECHO is on.
    public function checkAuth() {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
ECHO is on.
        if ($_SESSION['ip_address'] !== $_SERVER['REMOTE_ADDR']) {
            $this->logout();
            return false;
        }
ECHO is on.
        if ($_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
            $this->logout();
            return false;
        }
ECHO is on.
        $lifetime = $_ENV['SESSION_LIFETIME'] ?? 7200;
        if (time() - $_SESSION['logged_in_at'] > $lifetime) {
            $this->logout();
            return false;
        }
ECHO is on.
        return true;
    }
ECHO is on.
    public function getUser() {
        if (!$this->checkAuth()) {
            return null;
        }
ECHO is on.
        $stmt = $this->pdo->prepare("
            SELECT id, username, email, role, last_login_ip, last_login_at, created_at 
            FROM users WHERE id = ?
        ");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    }
}
?>
