<?php
// Improved Database Import Script
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'sentinel';

echo "====================================\n";
echo "Sentinel Database Import Tool v2\n";
echo "====================================\n\n";

// Connect to MySQL
echo "Connecting to MySQL... ";
$conn = new mysqli($host, $username, $password);
if ($conn->connect_error) {
    die("❌ Failed: " . $conn->connect_error . "\n");
}
echo "✅ Connected\n";

// Create and select database
echo "Setting up database '$database'... ";
$conn->query("DROP DATABASE IF EXISTS $database");
$conn->query("CREATE DATABASE $database");
$conn->select_db($database);
echo "✅ Done\n";

// Read schema file
$schemaFile = __DIR__ . '/database/schema.sql';
echo "Reading schema file: $schemaFile... ";
if (!file_exists($schemaFile)) {
    die("❌ File not found!\n");
}
$sql = file_get_contents($schemaFile);
echo "✅ Loaded (" . round(strlen($sql)/1024, 2) . " KB)\n";

// Process the SQL with DELIMITER handling
echo "\n📦 Importing database...\n";
echo "----------------------------------------\n";

// Split by DELIMITER statements
$delimiter = ';';
$fullSql = '';
$lines = explode("\n", $sql);
$inProcedure = false;
$procedureContent = '';
$success = 0;
$errors = 0;

foreach ($lines as $line) {
    $line = rtrim($line);
    
    // Check for DELIMITER command
    if (preg_match('/^DELIMITER\s+(.+)$/i', $line, $matches)) {
        $delimiter = $matches[1];
        continue;
    }
    
    // Check for procedure start
    if (preg_match('/CREATE\s+PROCEDURE/i', $line)) {
        $inProcedure = true;
        $procedureContent = $line . "\n";
        continue;
    }
    
    // If we're in a procedure
    if ($inProcedure) {
        $procedureContent .= $line . "\n";
        
        // Check if procedure ends with the current delimiter
        if (strpos($line, "END" . $delimiter) !== false) {
            $inProcedure = false;
            
            // Execute the procedure
            if ($conn->query($procedureContent)) {
                $success++;
                echo "✓ Procedure created\n";
            } else {
                $errors++;
                echo "❌ Error creating procedure: " . $conn->error . "\n";
            }
        }
        continue;
    }
    
    // Regular SQL statements
    if (!empty(trim($line)) && strpos($line, '--') !== 0 && strpos($line, '#') !== 0) {
        $fullSql .= $line . "\n";
        
        // Check if we have a complete statement
        if (strpos($line, $delimiter) !== false) {
            $statement = substr($fullSql, 0, -1); // Remove the delimiter
            
            if (!empty(trim($statement))) {
                if ($conn->query($statement)) {
                    $success++;
                    if ($success % 10 == 0) {
                        echo "✓ Processed $success queries...\n";
                    }
                } else {
                    $errors++;
                    echo "❌ Error: " . $conn->error . "\n";
                }
            }
            $fullSql = '';
        }
    }
}

echo "----------------------------------------\n";
echo "\n📊 Import Summary:\n";
echo "   ✓ Successful: $success\n";
echo "   ✗ Failed: $errors\n";

// Show created tables
echo "\n📋 Tables in database:\n";
$result = $conn->query("SHOW TABLES");
$tables = 0;
while ($row = $result->fetch_array()) {
    echo "   - " . $row[0] . "\n";
    $tables++;
}
echo "   Total tables: $tables\n";

// Show stored procedures
echo "\n⚙️  Stored Procedures:\n";
$result = $conn->query("SHOW PROCEDURE STATUS WHERE Db = '$database'");
$procedures = 0;
while ($row = $result->fetch_array()) {
    echo "   - " . $row['Name'] . "\n";
    $procedures++;
}
echo "   Total procedures: $procedures\n";

$conn->close();
echo "\n✅ Import completed!\n";
echo "Press any key to exit...";
fgets(STDIN);
