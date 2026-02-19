<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$database = 'uedf_sentinel';

echo "====================================\n";
echo "UEDF Sentinel Database Import (Final)\n";
echo "====================================\n\n";

// Connect to MySQL
$conn = new mysqli($host, $user, $pass);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "✓ Connected to MySQL\n";

// Create database
echo "Creating database '$database'... ";
$conn->query("DROP DATABASE IF EXISTS $database");
$conn->query("CREATE DATABASE $database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$conn->select_db($database);
echo "✓ Done\n\n";

// Read schema file
$sql = file_get_contents('database/schema.sql');
if ($sql === false) {
    die("Could not read schema file\n");
}
echo "✓ Schema file loaded (" . round(strlen($sql)/1024, 2) . " KB)\n\n";

// Clean the SQL - remove comments and empty lines
$lines = explode("\n", $sql);
$cleanSql = '';
$inProcedure = false;

foreach ($lines as $line) {
    $line = trim($line);
    
    // Skip empty lines
    if (empty($line)) {
        continue;
    }
    
    // Skip comment lines
    if (strpos($line, '--') === 0 || strpos($line, '#') === 0) {
        continue;
    }
    
    // Skip CREATE DATABASE and USE statements
    if (stripos($line, 'CREATE DATABASE') === 0 || 
        stripos($line, 'USE ') === 0 ||
        stripos($line, 'DROP DATABASE') === 0) {
        continue;
    }
    
    $cleanSql .= $line . "\n";
}

// Split by semicolon, but be careful with procedures
$queries = explode(';', $cleanSql);
$tableCount = 0;
$procCount = 0;
$otherCount = 0;
$errorCount = 0;

echo "Importing database...\n";
echo "----------------------------------------\n";

foreach ($queries as $index => $query) {
    $query = trim($query);
    if (empty($query)) continue;
    
    // Add semicolon back for procedures
    if (stripos($query, 'CREATE PROCEDURE') !== false) {
        $query .= ';';
    }
    
    // Execute the query with error suppression for duplicates
    if ($conn->query($query)) {
        if (stripos($query, 'CREATE TABLE') !== false) {
            $tableCount++;
            if ($tableCount % 5 == 0) {
                echo "✓ Created $tableCount tables...\n";
            }
        } elseif (stripos($query, 'CREATE PROCEDURE') !== false) {
            $procCount++;
            echo "✓ Created stored procedure\n";
        } else {
            $otherCount++;
        }
    } else {
        // Only show errors that aren't duplicates
        if (strpos($conn->error, 'Duplicate') === false && 
            strpos($conn->error, 'already exists') === false) {
            echo "⚠ Warning on query " . ($index+1) . ": " . $conn->error . "\n";
            $errorCount++;
        }
    }
}

echo "----------------------------------------\n\n";

// Verify import
echo "📊 IMPORT SUMMARY:\n";
echo "   Tables created: $tableCount\n";
echo "   Procedures created: $procCount\n";
echo "   Other queries: $otherCount\n";
echo "   Non-critical errors: $errorCount\n\n";

// List all tables
echo "📋 Tables in database:\n";
$result = $conn->query("SHOW TABLES");
$tables = [];
while ($row = $result->fetch_array()) {
    $tables[] = $row[0];
}

foreach ($tables as $table) {
    echo "   - $table\n";
}

// Check users table
$result = $conn->query("SELECT COUNT(*) as count FROM users");
if ($result) {
    $row = $result->fetch_assoc();
    echo "\n👥 Users in database: " . $row['count'] . "\n";
    
    // Show sample users
    if ($row['count'] > 0) {
        $result = $conn->query("SELECT username, role FROM users LIMIT 3");
        echo "   Sample users:\n";
        while ($user = $result->fetch_assoc()) {
            echo "     - " . $user['username'] . " (" . $user['role'] . ")\n";
        }
    }
}

$conn->close();
echo "\n✅ Import completed!\n";
echo "Press any key to exit...";
fgets(STDIN);
