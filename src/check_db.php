<?php
$conn = new mysqli('localhost', 'root', '', 'sentinel');
if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}
echo "✅ Connected to database successfully!\n\n";

$result = $conn->query("SHOW TABLES");
if ($result && $result->num_rows > 0) {
    echo "📊 Tables in database:\n";
    while ($row = $result->fetch_array()) {
        echo "  • " . $row[0] . "\n";
    }
    echo "\nTotal tables: " . $result->num_rows . "\n";
} else {
    echo "❌ No tables found in database.\n";
}

$conn->close();
