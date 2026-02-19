<?php
$conn = new mysqli('localhost', 'root', '', 'uedf_sentinel');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error . "\n");
}
echo "✅ Connected to database\n\n";

// Read schema file
$sql = file_get_contents('database/schema.sql');
if ($sql === false) {
    die("Could not read schema file\n");
}
echo "📄 Schema file loaded (" . round(strlen($sql)/1024, 2) . " KB)\n\n";

// Split by DELIMITER and process
$lines = explode("\n", $sql);
$current_query = '';
$in_procedure = false;
$procedure_content = '';
$delimiter = ';';
$count = 0;
$proc_count = 0;

echo "⚙️  Importing database...\n";
echo str_repeat("-", 40) . "\n";

foreach ($lines as $line) {
    $line = rtrim($line);
    
    // Check for DELIMITER command
    if (preg_match('/^DELIMITER\s+(.+)$/i', $line, $matches)) {
        $delimiter = $matches[1];
        continue;
    }
    
    // Check for stored procedure start
    if (stripos($line, 'CREATE PROCEDURE') !== false) {
        $in_procedure = true;
        $procedure_content = $line . "\n";
        continue;
    }
    
    // If we're in a procedure
    if ($in_procedure) {
        $procedure_content .= $line . "\n";
        
        // Check if procedure ends
        if (strpos($line, "END" . $delimiter) !== false) {
            $in_procedure = false;
            
            // Remove the delimiter from the end
            $procedure_content = str_replace($delimiter, '', $procedure_content);
            
            // Execute the procedure
            if ($conn->query($procedure_content)) {
                $proc_count++;
                echo "✓ Created stored procedure\n";
            } else {
                echo "⚠ Error creating procedure: " . $conn->error . "\n";
            }
        }
        continue;
    }
    
    // Regular SQL statements
    if (!empty($line) && strpos($line, '--') !== 0 && strpos($line, '#') !== 0) {
        $current_query .= $line . " ";
        
        if (strpos($line, $delimiter) !== false) {
            $query = trim(str_replace($delimiter, '', $current_query));
            if (!empty($query)) {
                if ($conn->query($query)) {
                    $count++;
                    if ($count % 10 == 0) {
                        echo "✓ Executed $count queries...\n";
                    }
                } else {
                    if (strpos($conn->error, 'Duplicate') === false && 
                        strpos($conn->error, 'already exists') === false) {
                        echo "⚠ Error: " . $conn->error . "\n";
                    }
                }
            }
            $current_query = '';
        }
    }
}

echo str_repeat("-", 40) . "\n\n";
echo "📊 IMPORT SUMMARY:\n";
echo "   Tables/queries created: $count\n";
echo "   Stored procedures created: $proc_count\n\n";

// Show tables
echo "📋 Tables in database:\n";
$result = $conn->query("SHOW TABLES");
$tables = [];
while ($row = $result->fetch_array()) {
    $tables[] = $row[0];
    echo "   - " . $row[0] . "\n";
}
echo "   Total: " . count($tables) . " tables\n\n";

// Check users
$result = $conn->query("SELECT COUNT(*) as count FROM users");
if ($result) {
    $row = $result->fetch_assoc();
    echo "👥 Users in database: " . $row['count'] . "\n";
    
    // Show sample users
    if ($row['count'] > 0) {
        $result = $conn->query("SELECT username, role FROM users LIMIT 5");
        echo "   Sample users:\n";
        while ($user = $result->fetch_assoc()) {
            echo "     - " . $user['username'] . " (" . $user['role'] . ")\n";
        }
    }
}

$conn->close();
echo "\n✅ Import completed!\n";
