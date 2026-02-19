<?php
require_once 'vendor/autoload.php';

use UEDF\Controllers\DroneController;
use UEDF\Services\SecurityService;
use UEDF\Database\Connection;

echo "?? Testing autoloader with real classes...\n\n";

// Test DroneController
if (class_exists('UEDF\Controllers\DroneController')) {
    echo "? DroneController found\n";
    
    // Check if we can inspect it without instantiating
    $reflection = new ReflectionClass('UEDF\Controllers\DroneController');
    echo "   - Constructor: " . ($reflection->hasMethod('__construct') ? 'yes' : 'no') . "\n";
    echo "   - Methods: " . count($reflection->getMethods()) . "\n";
} else {
    echo "? DroneController not found\n";
}

// Test SecurityService
if (class_exists('UEDF\Services\SecurityService')) {
    echo "? SecurityService found\n";
} else {
    echo "? SecurityService not found\n";
}

// Test Database Connection
if (class_exists('UEDF\Database\Connection')) {
    echo "? Database Connection found\n";
} else {
    echo "? Database Connection not found\n";
}

echo "\n? Autoloader test complete!\n";
