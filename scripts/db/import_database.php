<?php
// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'sentinel';

echo "====================================\n";
echo "Sentinel Database Import Tool\n";
echo "====================================\n\n";

// Connect to MySQL server (without database)
echo "Connecting to MySQL... ";
$conn = new mysqli($host, $username, $password);
if ($conn->connect_error) {
    die("❌ Failed: " . $conn->connect_error . "\n");
}
echo "✅ Connected\n";

// Create database if it doesn't exist
echo "Creating database '$database'... ";
$conn->query("CREATE DATABASE IF NOT EXISTS $database");
echo "✅ Done\n";

// Select the database
$conn->select_db($database);

// Path to schema file
$schemaFile = __DIR__ . '/database/schema.sql';
echo "Reading schema file: $schemaFile... ";
if (!file_exists($schemaFile)) {
    die("❌ File not found!\n");
}
$sql = file_get_contents($schemaFile);
echo "✅ Loaded (" . round(strlen($sql)/1024, 2) . " KB)\n";

// Remove comments and split into individual queries
echo "\nExecuting SQL queries...\n";
echo "----------------------------------------\n";

// Remove MySQL comments (-- and #)
$lines = explode("\n", $sql);
$cleanSql = '';
foreach ($lines as $line) {
    $line = trim($line);
    if (strpos($line, '--') === 0 || strpos($line, '#') === 0 || empty($line)) {
        continue;
    }
    $cleanSql .= $line . "\n";
}

// Split by semicolon
$queries = explode(';', $cleanSql);
$success = 0;
$errors = 0;

foreach ($queries as $index => $query) {
    $query = trim($query);
    if (empty($query)) continue;
    
    if ($conn->query($query)) {
        $success++;
        if ($success % 10 == 0) {
            echo "✓ Processed $success queries...\n";
        }
    } else {
        $errors++;
        echo "❌ Error on query " . ($index+1) . ": " . $conn->error . "\n";
        echo "   Query: " . substr($query, 0, 100) . "...\n";
    }
}

echo "----------------------------------------\n";
echo "\n📊 Import Summary:\n";
echo "   ✓ Successful: $success\n";
echo "   ✗ Failed: $errors\n";

// Show created tables
if ($success > 0) {
    echo "\n📋 Tables in database:\n";
    $result = $conn->query("SHOW TABLES");
    while ($row = $result->fetch_array()) {
        echo "   - " . $row[0] . "\n";
    }
}

$conn->close();
echo "\n✅ Import process completed!\n";
echo "Press any key to exit...";
fgets(STDIN);
