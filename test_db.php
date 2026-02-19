<?php
require_once 'src/db_connect.php';
try {
    getDB();
    echo "Database connected!\n";
} catch (Exception $e) {
    echo "Failed: " . $e->getMessage() . "\n";
}
