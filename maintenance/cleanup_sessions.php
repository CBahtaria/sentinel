<?php
/**
 * UEDF SENTINEL v4.0 - Session Cleanup
 * Run this script periodically to clean expired sessions
 */

require_once __DIR__ . '/../src/Database/Connection.php';

try {
    $db = DatabaseConnection::getInstance()->getConnection();
    $deleted = $db->exec("DELETE FROM sessions WHERE expires_at < NOW()");
    echo "[" . date('Y-m-d H:i:s') . "] Cleaned up $deleted expired sessions\n";
} catch (Exception $e) {
    echo "[" . date('Y-m-d H:i:s') . "] ERROR: " . $e->getMessage() . "\n";
}
