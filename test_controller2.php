<?php
require_once 'vendor/autoload.php';

use UEDF\Controllers\DroneController;

echo "Testing DroneController...\n";

if (class_exists('UEDF\Controllers\DroneController')) {
    echo "? DroneController loaded successfully!\n";
    
    // Check if it can be instantiated (might fail if constructor needs params)
    try {
        $reflection = new ReflectionClass('UEDF\Controllers\DroneController');
        $constructor = $reflection->getConstructor();
        
        if ($constructor) {
            $params = $constructor->getParameters();
            echo "   Constructor requires " . count($params) . " parameters\n";
        } else {
            echo "   No constructor - can be instantiated freely\n";
        }
    } catch (Exception $e) {
        echo "   Error inspecting: " . $e->getMessage() . "\n";
    }
} else {
    echo "? DroneController not found\n";
}
