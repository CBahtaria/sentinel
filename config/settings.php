<?php
/**
 * UEDF Sentinel Configuration
 */

return [
    // System Information
    'system_name' => 'UEDF Sentinel',
    'version' => '1.0.0',
    'timezone' => 'Africa/Mbabane',
    
    // Feature Flags
    'websocket' => true,
    'debug' => true,
    'maintenance' => false,
    
    // Display Settings
    'theme' => 'dark',
    'items_per_page' => 25,
    
    // Security
    'session_timeout' => 3600,
    'session_httponly' => true,
    
    // Database (if needed)
    'database' => [
        'host' => 'localhost',
        'name' => 'uedf_sentinel',
        'user' => 'root',
        'pass' => ''
    ]
];
?>
