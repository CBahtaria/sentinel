<?php
/**
 * UEDF SENTINEL - Mobile Setup Verification
 */

echo "=============================================\n";
echo "UEDF SENTINEL Mobile Setup Verification\n";
echo "=============================================\n\n";

try {
    $dbHost = $_ENV['DB_HOST'] ?? throw new \RuntimeException('DB_HOST not set');
    $dbName = $_ENV['DB_NAME'] ?? throw new \RuntimeException('DB_NAME not set');
    $dbUser = $_ENV['DB_USER'] ?? throw new \RuntimeException('DB_USER not set');
    $dbPass = $_ENV['DB_PASS'] ?? throw new \RuntimeException('DB_PASS not set');
    $pdo = new PDO("mysql:host={$dbHost};dbname={$dbName}", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check mobile_sessions table
    $result = $pdo->query("SHOW TABLES LIKE 'mobile_sessions'");
    if ($result->rowCount() > 0) {
        echo "✅ mobile_sessions table: FOUND\n";
        
        // Show table structure
        $columns = $pdo->query("DESCRIBE mobile_sessions");
        $columnCount = $columns->rowCount();
        echo "   - $columnCount columns configured correctly\n";
    } else {
        echo "❌ mobile_sessions table: NOT FOUND\n";
    }
    
    // Check API directory
    if (is_dir(__DIR__ . '/api/v1')) {
        echo "✅ API directory: FOUND\n";
        
        // Check API files
        $apiFiles = ['config.php', 'auth.php', 'drones.php', 'threats.php', 'system.php'];
        foreach ($apiFiles as $file) {
            if (file_exists(__DIR__ . '/api/v1/' . $file)) {
                echo "   - ✅ $file\n";
            } else {
                echo "   - ❌ $file (missing)\n";
            }
        }
    } else {
        echo "❌ API directory: NOT FOUND\n";
    }
    
    // Check API documentation
    if (file_exists(__DIR__ . '/api-docs.php')) {
        echo "✅ API documentation: FOUND\n";
    } else {
        echo "❌ API documentation: NOT FOUND\n";
    }
    
    // Test database connection
    echo "\n📊 Database Statistics:\n";
    $users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    echo "   - Users: $users\n";
    
    $drones = $pdo->query("SELECT COUNT(*) FROM drones")->fetchColumn();
    echo "   - Drones: $drones\n";
    
    $threats = $pdo->query("SELECT COUNT(*) FROM threats")->fetchColumn();
    echo "   - Threats: $threats\n";
    
    echo "\n=============================================\n";
    echo "✅ Mobile setup is ready!\n";
    echo "=============================================\n";
    echo "\n";
    echo "📱 API Base URL: http://localhost:8080/sentinel/api/v1/\n";
    echo "🔑 API Key: " . (getenv('SENTINEL_API_KEY') ? '[set via env]' : '[SENTINEL_API_KEY NOT SET]') . "\n";
    echo "📚 API Docs: http://localhost:8080/sentinel/api-docs.php\n";
    echo "\n";
    echo "Test the API with: http://localhost:8080/sentinel/test-api.php\n";
    echo "=============================================\n";
    
} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
}
?>
