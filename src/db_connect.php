<?php
/**
 * Database Connection
 * UEDF Sentinel Database Connection File
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'uedf_sentinel');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Create connection function
function getDB() {
    static $db = null;

    if ($db === null) {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
            $db = new PDO($dsn, DB_USER, DB_PASS);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die(json_encode([
                'error' => true,
                'message' => 'Database connection failed: ' . $e->getMessage(),
                'timestamp' => date('c')
            ]));
        }
    }

    return $db;
}

// For backward compatibility - include SentinelDB class
require_once __DIR__ . '/SentinelDB.php';
