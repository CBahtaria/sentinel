<?php
require_once 'vendor/autoload.php';

use UEDF\Controllers\DroneController;

echo "?? Testing DroneController autoloading...\n\n";

if (class_exists('UEDF\Controllers\DroneController')) {
    echo "? DroneController class FOUND\n";
    
    $reflection = new ReflectionClass('UEDF\Controllers\DroneController');
    echo "  - File: " . $reflection->getFileName() . "\n";
    echo "  - Methods: " . count($reflection->getMethods()) . "\n";
    
    echo "\n?? Available public methods:\n";
    foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
        if (!$method->isConstructor()) {
            $params = [];
            foreach ($method->getParameters() as $param) {
                $params[] = '$' . $param->getName();
            }
            $paramStr = implode(', ', $params);
            echo "  - " . $method->getName() . "($paramStr)\n";
        }
    }
    
    echo "\n? Class is ready to use!\n";
} else {
    echo "? DroneController NOT found\n";
}
