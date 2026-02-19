<?php
/**
 * Check Database Indexes
 */
echo "<!DOCTYPE html><html><head>";
echo "<title>Database Index Check</title>";
echo "<style>
    body { background: #0a0f1c; color: #00ff9d; font-family: monospace; padding: 20px; }
    h1 { color: #ff006e; font-family: 'Orbitron', sans-serif; }
    table { border-collapse: collapse; width: 100%; background: #151f2c; }
    th { background: #ff006e; color: white; padding: 10px; text-align: left; }
    td { padding: 8px; border-bottom: 1px solid #ff006e; }
    .success { color: #00ff9d; }
    .back { margin-top: 20px; display: block; }
</style>";
echo "</head><body>";

echo "<h1>🔍 Database Index Check</h1>";

try {
    $pdo = new PDO('mysql:host=localhost;dbname=uedf_sentinel', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $tables = ['threats', 'drones', 'nodes', 'users', 'sessions'];
    
    foreach ($tables as $table) {
        echo "<h2>Table: $table</h2>";
        
        $stmt = $pdo->query("SHOW INDEX FROM $table");
        $indexes = $stmt->fetchAll();
        
        if (count($indexes) > 0) {
            echo "<table>";
            echo "<tr><th>Key Name</th><th>Column</th><th>Cardinality</th><th>Index Type</th></tr>";
            
            foreach ($indexes as $index) {
                $key = $index['Key_name'] ?? $index[2] ?? 'N/A';
                $col = $index['Column_name'] ?? $index[4] ?? 'N/A';
                $card = $index['Cardinality'] ?? $index[6] ?? 'N/A';
                $type = $index['Index_type'] ?? $index[10] ?? 'N/A';
                
                $highlight = ($key != 'PRIMARY') ? ' style="color:#00ff9d;"' : '';
                echo "<tr$highlight>";
                echo "<td>$key</td>";
                echo "<td>$col</td>";
                echo "<td>$card</td>";
                echo "<td>$type</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No indexes found on this table.</p>";
        }
        echo "<br>";
    }
    
    // Check if our new indexes exist
    echo "<h2 class='success'>✅ Index Creation Summary</h2>";
    
    $expected_indexes = [
        'idx_threats_severity' => 'threats',
        'idx_threats_status' => 'threats',
        'idx_drones_status' => 'drones',
        'idx_nodes_zone' => 'nodes',
        'idx_nodes_status' => 'nodes',
        'idx_users_role' => 'users',
        'idx_sessions_expires' => 'sessions'
    ];
    
    $all_good = true;
    
    foreach ($expected_indexes as $index_name => $table) {
        try {
            $stmt = $pdo->query("SHOW INDEX FROM $table WHERE Key_name = '$index_name'");
            if ($stmt->rowCount() > 0) {
                echo "<p style='color:#00ff9d;'>✓ Index '$index_name' exists on $table</p>";
            } else {
                echo "<p style='color:#ff006e;'>✗ Index '$index_name' NOT found on $table</p>";
                $all_good = false;
            }
        } catch (Exception $e) {
            echo "<p style='color:#ff006e;'>✗ Error checking '$index_name': " . $e->getMessage() . "</p>";
            $all_good = false;
        }
    }
    
    if ($all_good) {
        echo "<h2 style='color:#00ff9d;'>✅ ALL INDEXES CREATED SUCCESSFULLY!</h2>";
    } else {
        echo "<h2 style='color:#ff006e;'>⚠️ Some indexes are missing. Run the optimization script again.</h2>";
    }
    
} catch (Exception $e) {
    echo "<p style='color:#ff006e;'>❌ Database error: " . $e->getMessage() . "</p>";
}

echo "<p><a href='?module=home' style='color:#00ff9d;'>← Return to Command Center</a></p>";
echo "</body></html>";
?>
