<?php
namespace Sentinel\Security;

class CSRF {
    private static $tokenName = 'csrf_token';
ECHO is on.
    public static function generate() {
        if (!isset($_SESSION[self::$tokenName])) {
            $_SESSION[self::$tokenName] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::$tokenName];
    }
ECHO is on.
    public static function getTokenField() {
        $token = self::generate();
    }
ECHO is on.
    public static function validate($token = null) {
        if (!$token) {
            $token = $_POST[self::$tokenName] ?? $_GET[self::$tokenName] ?? null;
        }
ECHO is on.
        if (!$token 
            AuditLogger::log('csrf_failed', ['reason' => 'missing_token'], 'denied');
            return false;
        }
ECHO is on.
        $isValid = hash_equals($_SESSION[self::$tokenName], $token);
ECHO is on.
        if (!$isValid) {
            AuditLogger::log('csrf_failed', [
                'expected' => $_SESSION[self::$tokenName],
                'received' => $token
            ], 'denied');
        }
ECHO is on.
        return $isValid;
    }
ECHO is on.
    public static function refresh() {
        $_SESSION[self::$tokenName] = bin2hex(random_bytes(32));
        return $_SESSION[self::$tokenName];
    }
}
?>
