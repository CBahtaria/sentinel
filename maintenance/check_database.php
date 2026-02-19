<?php
/**
 * UEDF SENTINEL v4.0 - Database Health Check
 */

require_once __DIR__ . '/../src/Database/Connection.php';

echo "========================================\n";
echo "UEDF SENTINEL - Database Health Check\n";
echo "========================================\n\n";

try {
    $db = DatabaseConnection::getInstance()->getConnection();
    
    // Check connection
    echo "✓ Database connection: OK\n\n";
    
    // Check tables
    $tables = ['users', 'nodes', 'drones', 'threats', 'audit_logs', 'sessions', 'notifications'];
    echo "Table Statistics:\n";
    echo "-----------------\n";
    
    foreach ($tables as $table) {
        $count = $db->query("SELECT COUNT(*) FROM $table")->fetchColumn();
        echo sprintf("%-15s: %d records\n", $table, $count);
    }
    
    // Check for warnings
    echo "\nSystem Warnings:\n";
    echo "----------------\n";
    
    $critical_threats = $db->query("SELECT COUNT(*) FROM threats WHERE severity = 'CRITICAL' AND status = 'ACTIVE'")->fetchColumn();
    if ($critical_threats > 0) {
        echo "⚠️  $critical_threats critical threats active\n";
    } else {
        echo "✓ No critical threats\n";
    }
    
    $low_battery = $db->query("SELECT COUNT(*) FROM drones WHERE battery_level < 20 AND status != 'MAINTENANCE'")->fetchColumn();
    if ($low_battery > 0) {
        echo "⚠️  $low_battery drones with low battery\n";
    } else {
        echo "✓ All drones battery OK\n";
    }
    
    $offline_nodes = $db->query("SELECT COUNT(*) FROM nodes WHERE status = 'OFFLINE'")->fetchColumn();
    if ($offline_nodes > 0) {
        echo "⚠️  $offline_nodes nodes offline\n";
    } else {
        echo "✓ All nodes online\n";
    }
    
    echo "\n========================================\n";
    echo "Database health check complete\n";
    echo "========================================\n";
    
} catch (Exception $e) {
    echo "✗ Database error: " . $e->getMessage() . "\n";
    exit(1);
}
