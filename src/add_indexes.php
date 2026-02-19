<?php
/**
 * SENTINEL v3.1 - DAY 8-9: COMPOSITE B-TREE INDEX
 * FIXED: Idempotent - drops existing indexes first
 * FIXED: Creates evidence directory automatically
 */

require_once 'db_connect.php';

$pdo = SentinelDB::getInstance();

// Ensure evidence directory exists
$evidence_dir = __DIR__ . '/../evidence';
if (!is_dir($evidence_dir)) {
    mkdir($evidence_dir, 0777, true);
    echo "📁 Created evidence directory\n\n";
}

echo "╔══════════════════════════════════════════════════════════════════════╗\n";
echo "║              SENTINEL v3.1 - INDEX OPTIMIZATION                      ║\n";
echo "╠══════════════════════════════════════════════════════════════════════╣\n\n";

// ===========================================
// 1. DROP EXISTING INDEXES (IF THEY EXIST)
// ===========================================
echo "🔷 CLEANING UP OLD INDEXES...\n";
try {
    $pdo->exec("DROP INDEX IF EXISTS idx_coords_composite ON nodes");
    echo "  ✅ Dropped old composite index\n";
} catch (Exception $e) {
    echo "  ⚠️ Could not drop composite index: " . $e->getMessage() . "\n";
}
try {
    $pdo->exec("DROP INDEX IF EXISTS idx_status ON nodes");
    echo "  ✅ Dropped old status index\n";
} catch (Exception $e) {
    echo "  ⚠️ Could not drop status index\n";
}
try {
    $pdo->exec("DROP INDEX IF EXISTS idx_threat_time ON threat_logs");
    echo "  ✅ Dropped old threat time index\n";
} catch (Exception $e) {
    echo "  ⚠️ Could not drop threat time index\n";
}
echo "\n";

// ===========================================
// 2. CREATE COMPOSITE INDEX
// ===========================================
echo "🔷 CREATING COMPOSITE B-TREE INDEX...\n";
$start = microtime(true);
$pdo->exec("CREATE INDEX idx_coords_composite ON nodes (x_pos, y_pos)");
$time = microtime(true) - $start;
echo "  ✅ Composite index created in " . round($time, 4) . "s\n";
echo "  • Index: idx_coords_composite\n";
echo "  • Columns: (x_pos, y_pos)\n";
echo "  • Type: B-Tree\n";
echo "  • Complexity: O(log n)\n\n";

// ===========================================
// 3. CREATE STATUS INDEX
// ===========================================
echo "🔷 CREATING STATUS INDEX...\n";
$pdo->exec("CREATE INDEX idx_status ON nodes (status)");
echo "  ✅ Status index created\n";
echo "  • Use: Emergency filtering (Critical/Warning/Active)\n\n";

// ===========================================
// 4. CREATE THREAT TIMESTAMP INDEX
// ===========================================
echo "🔷 CREATING THREAT TIMESTAMP INDEX...\n";
$pdo->exec("CREATE INDEX idx_threat_time ON threat_logs (created_at, threat_level)");
echo "  ✅ Threat analysis index created\n";
echo "  • Use: Heatmap generation, temporal queries\n\n";

// ===========================================
// 5. VERIFY INDEXES
// ===========================================
echo "📊 INDEX VERIFICATION\n";
echo "────────────────────────────────────\n";

$nodes_indexes = $pdo->query("SHOW INDEX FROM nodes")->fetchAll();
$threat_indexes = $pdo->query("SHOW INDEX FROM threat_logs")->fetchAll();

echo "nodes table: " . count($nodes_indexes) . " indexes\n";
foreach ($nodes_indexes as $idx) {
    echo "  • " . str_pad($idx['Key_name'], 20) . " (" . $idx['Column_name'] . ")\n";
}

echo "\nthreat_logs table: " . count($threat_indexes) . " indexes\n";
foreach ($threat_indexes as $idx) {
    echo "  • " . str_pad($idx['Key_name'], 20) . " (" . $idx['Column_name'] . ")\n";
}

// ===========================================
// 6. SAVE EVIDENCE
// ===========================================
$proof = "SENTINEL v3.1 - INDEX OPTIMIZATION PROOF\n";
$proof .= "Generated: " . date('Y-m-d H:i:s') . "\n";
$proof .= "========================================\n\n";
$proof .= "✅ Composite B-Tree index: idx_coords_composite (x_pos, y_pos)\n";
$proof .= "✅ Status index: idx_status (status)\n";
$proof .= "✅ Threat time index: idx_threat_time (created_at, threat_level)\n\n";
$proof .= "Total indexes - nodes: " . count($nodes_indexes) . ", threats: " . count($threat_indexes) . "\n";

file_put_contents($evidence_dir . '/index_proof.txt', $proof);

echo "\n✅ INDEX OPTIMIZATION COMPLETE\n";
echo "📁 Proof saved to: evidence/index_proof.txt\n";
?>
