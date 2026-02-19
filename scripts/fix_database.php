<?php
// Simple database fix for Bartarian Defence
echo "🔧 BARTARIAN DEFENCE - DATABASE FIX\n";
echo "===================================\n\n";

try {
    // Connect to MySQL
    $pdo = new PDO("mysql:host=localhost", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Connected to MySQL\n";
    
    // Check if old database exists
    $stmt = $pdo->query("SHOW DATABASES LIKE 'uedf_sentinel'");
    $oldExists = $stmt->fetch();
    
    // Check if new database exists
    $stmt = $pdo->query("SHOW DATABASES LIKE 'bartarian_defence'");
    $newExists = $stmt->fetch();
    
    if ($oldExists && !$newExists) {
        echo "📋 Found old database 'uedf_sentinel', creating new...\n";
        
        // Create new database
        $pdo->exec("CREATE DATABASE bartarian_defence CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        
        // Copy all tables (excluding views)
        $pdo->exec("USE uedf_sentinel");
        $tables = $pdo->query("SHOW FULL TABLES WHERE Table_Type = 'BASE TABLE'")->fetchAll(PDO::FETCH_COLUMN);
        
        echo "   Copying " . count($tables) . " tables...\n";
        foreach ($tables as $table) {
            $pdo->exec("CREATE TABLE bartarian_defence.$table LIKE uedf_sentinel.$table");
            $pdo->exec("INSERT INTO bartarian_defence.$table SELECT * FROM uedf_sentinel.$table");
            echo "   ✅ Copied $table\n";
        }
        
        echo "\n✅ Database migration complete!\n";
        
    } elseif ($newExists) {
        echo "✅ Database 'bartarian_defence' already exists\n";
        
        // Count tables
        $pdo->exec("USE bartarian_defence");
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        echo "   📊 Tables found: " . count($tables) . "\n";
        
    } else {
        echo "⚠️ No database found. Creating fresh database...\n";
        $pdo->exec("CREATE DATABASE bartarian_defence CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "✅ Created fresh database\n";
    }
    
    // Update config file
    $configFile = __DIR__ . '/config/database.php';
    if (file_exists($configFile)) {
        $config = "<?php\nreturn [\n    'host' => 'localhost',\n    'username' => 'root',\n    'password' => '',\n    'database' => 'bartarian_defence',\n    'port' => 3306,\n    'charset' => 'utf8mb4'\n];";
        file_put_contents($configFile, $config);
        echo "✅ Updated database configuration\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n✅ Database fix complete!\n";
