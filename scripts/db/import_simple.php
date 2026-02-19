<?php
$conn = new mysqli('localhost', 'root', '', 'uedf_sentinel');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error . "\n");
}
echo "Connected to database\n";

// Read and execute schema
$sql = file_get_contents('database/schema.sql');
if ($sql === false) {
    die("Could not read schema file\n");
}

// Split by semicolon and execute
$queries = explode(';', $sql);
$count = 0;
foreach ($queries as $query) {
    $query = trim($query);
    if (empty($query)) continue;
    
    if ($conn->query($query)) {
        $count++;
        if ($count % 10 == 0) {
            echo "Executed $count queries...\n";
        }
    } else {
        // Ignore duplicate errors
        if (strpos($conn->error, 'Duplicate') === false) {
            echo "Error: " . $conn->error . "\n";
        }
    }
}
echo "Import completed! Executed $count queries\n";
