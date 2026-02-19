<?php
/**
 * UEDF SENTINEL v4.0 - Archive Old Logs
 * Run this script monthly to archive old audit logs
 */

require_once __DIR__ . '/../src/Database/Connection.php';

try {
    $db = DatabaseConnection::getInstance()->getConnection();
    
    // Create archive table if not exists
    $db->exec("
        CREATE TABLE IF NOT EXISTS audit_logs_archive (
            id INT,
            user_id INT,
            action VARCHAR(255),
            category VARCHAR(50),
            details JSON,
            ip_address VARCHAR(45),
            user_agent TEXT,
            created_at TIMESTAMP,
            archived_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    
    // Archive old logs
    $archived = $db->exec("
        INSERT INTO audit_logs_archive (id, user_id, action, category, details, ip_address, user_agent, created_at)
        SELECT id, user_id, action, category, details, ip_address, user_agent, created_at
        FROM audit_logs 
        WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)
    ");
    
    // Delete archived logs
    $deleted = $db->exec("
        DELETE FROM audit_logs 
        WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)
    ");
    
    echo "[" . date('Y-m-d H:i:s') . "] Archived $archived and deleted $deleted old audit logs\n";
} catch (Exception $e) {
    echo "[" . date('Y-m-d H:i:s') . "] ERROR: " . $e->getMessage() . "\n";
}
