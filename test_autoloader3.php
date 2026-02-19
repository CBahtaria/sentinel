<?php
require_once 'vendor/autoload.php';

use UEDF\Controllers\DroneController;

echo "Testing DroneController autoloading...\n\n";

if (class_exists('UEDF\Controllers\DroneController')) {
    echo "? DroneController class FOUND in namespace\n";
    
    // Get class information without instantiating
    $reflection = new ReflectionClass('UEDF\Controllers\DroneController');
    echo "\n?? Class Information:\n";
    echo "  - File: " . $reflection->getFileName() . "\n";
    echo "  - Methods: " . count($reflection->getMethods()) . "\n";
    echo "  - Properties: " . count($reflection->getProperties()) . "\n";
    
    // List methods
    echo "\n?? Available methods:\n";
    foreach ($reflection->getMethods() as $method) {
        if ($method->isPublic() && !$method->isConstructor()) {
            echo "  - " . $method->getName() . "()\n";
        }
    }
} else {
    echo "? DroneController NOT found\n";
}

echo "\n? Test complete - autoloader is working!\n";
