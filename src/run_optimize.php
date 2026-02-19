<?php
/**
 * Run Database Optimization
 */
try {
    $pdo = new PDO('mysql:host=localhost;dbname=uedf_sentinel', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Running database optimization...\n\n";
    
    $queries = [
        "CREATE INDEX idx_threats_severity ON threats(severity)",
        "CREATE INDEX idx_threats_status ON threats(status)",
        "CREATE INDEX idx_drones_status ON drones(status)",
        "CREATE INDEX idx_nodes_zone ON nodes(zone)",
        "CREATE INDEX idx_nodes_status ON nodes(status)",
        "CREATE INDEX idx_users_role ON users(role)",
        "CREATE INDEX idx_sessions_expires ON sessions(expires_at)",
        "OPTIMIZE TABLE users",
        "OPTIMIZE TABLE nodes",
        "OPTIMIZE TABLE drones",
        "OPTIMIZE TABLE threats",
        "OPTIMIZE TABLE audit_logs",
        "OPTIMIZE TABLE sessions"
    ];
    
    foreach ($queries as $sql) {
        try {
            $pdo->exec($sql);
            echo "✓ " . substr($sql, 0, 50) . "...\n";
        } catch (PDOException $e) {
            // Index might already exist
            echo "⚠ " . substr($sql, 0, 50) . "... (may already exist)\n";
        }
    }
    
    echo "\n✅ Database optimization complete!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
