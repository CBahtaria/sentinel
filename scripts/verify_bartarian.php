<?php
echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║     ✅ BARTARIAN DEFENCE - SYSTEM VERIFICATION           ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n\n";

// Check database
try {
    $pdo = new PDO("mysql:host=localhost", "root", "");
    $stmt = $pdo->query("SHOW DATABASES LIKE 'bartarian_defence'");
    if ($stmt->fetch()) {
        echo "✅ Database 'bartarian_defence' exists\n";
        
        $pdo->exec("USE bartarian_defence");
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        echo "   📊 Tables found: " . count($tables) . "\n";
    } else {
        echo "❌ Database not found\n";
    }
} catch (Exception $e) {
    echo "❌ Database check failed: " . $e->getMessage() . "\n";
}

// Check key files
echo "\n📁 Key Files:\n";
$keyFiles = [
    'public/login.php',
    'public/dashboard.php',
    'public/node_control.php',
    'public/admin.php',
    'config/database.php',
    'scripts/chaos/run_tests.php',
    'scripts/check_replication.php'
];

foreach ($keyFiles as $file) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "   ✅ $file\n";
    } else {
        echo "   ❌ $file\n";
    }
}

// Check for UEDF remnants
echo "\n🔍 Checking for UEDF remnants...\n";
$remnants = 0;
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__));
foreach ($files as $file) {
    if ($file->isFile() && in_array($file->getExtension(), ['php', 'html', 'js', 'css', 'sql'])) {
        $content = file_get_contents($file->getPathname());
        if (preg_match('/UEDF|uedf/i', $content)) {
            echo "   ⚠️ Found in: " . str_replace(__DIR__, '', $file->getPathname()) . "\n";
            $remnants++;
        }
    }
}

if ($remnants === 0) {
    echo "   ✅ No UEDF remnants found!\n";
}

echo "\n✅ Verification complete!\n";
