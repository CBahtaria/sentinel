<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$database = 'uedf_sentinel';

echo "====================================\n";
echo "UEDF Sentinel Database Import\n";
echo "====================================\n\n";

// Connect to MySQL
$conn = new mysqli($host, $user, $pass);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "✓ Connected to MySQL\n";

// Drop and recreate database
echo "Creating database '$database'... ";
$conn->query("DROP DATABASE IF EXISTS $database");
$conn->query("CREATE DATABASE $database");
$conn->select_db($database);
echo "✓ Done\n\n";

// Read schema file
$sql = file_get_contents('database/schema.sql');
if ($sql === false) {
    die("Could not read schema file\n");
}
echo "✓ Schema file loaded (" . round(strlen($sql)/1024, 2) . " KB)\n\n";

// Remove the CREATE DATABASE and USE statements from the file
$lines = explode("\n", $sql);
$cleanSql = '';
$skip = false;

foreach ($lines as $line) {
    // Skip CREATE DATABASE and USE statements
    if (stripos($line, 'CREATE DATABASE') !== false || 
        stripos($line, 'USE ') !== false ||
        stripos($line, 'DROP DATABASE') !== false) {
        continue;
    }
    $cleanSql .= $line . "\n";
}

// Execute the schema
echo "Importing tables...\n";
echo "----------------------------------------\n";

$queries = explode(';', $cleanSql);
$tableCount = 0;
$procCount = 0;

foreach ($queries as $query) {
    $query = trim($query);
    if (empty($query)) continue;
    
    if ($conn->query($query)) {
        if (stripos($query, 'CREATE TABLE') !== false) {
            $tableCount++;
            echo "✓ Table created (" . $tableCount . ")\n";
        } elseif (stripos($query, 'CREATE PROCEDURE') !== false) {
            $procCount++;
            echo "✓ Procedure created\n";
        }
    } else {
        // Ignore duplicate errors
        if (strpos($conn->error, 'Duplicate') === false) {
            echo "⚠ Warning: " . $conn->error . "\n";
        }
    }
}

echo "----------------------------------------\n\n";

// Verify import
echo "Verifying import:\n";
$result = $conn->query("SHOW TABLES");
$tables = [];
while ($row = $result->fetch_array()) {
    $tables[] = $row[0];
}

echo "✓ " . count($tables) . " tables created:\n";
echo "  " . implode(", ", array_slice($tables, 0, 5));
if (count($tables) > 5) echo "...";
echo "\n\n";

// Check users table specifically
$result = $conn->query("SELECT COUNT(*) as count FROM users");
if ($result) {
    $row = $result->fetch_assoc();
    echo "✓ users table has " . $row['count'] . " records\n";
}

$conn->close();
echo "\n✅ Import completed successfully!\n";
echo "Press any key to exit...";
fgets(STDIN);
