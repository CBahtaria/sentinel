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
    'debug' => false,  // Never expose errors to users in production; use error_log instead
    'maintenance' => false,
    
    // Display Settings
    'theme' => 'dark',
    'items_per_page' => 25,
    
    // Security
    'session_timeout' => 3600,
    'session_httponly' => true,
    
    // Database credentials are loaded from environment variables only — never hardcoded here.
    // Required: DB_HOST, DB_NAME, DB_USER, DB_PASS
    'database' => [
        'host' => $_ENV['DB_HOST'] ?? null,
        'name' => $_ENV['DB_NAME'] ?? null,
        'user' => $_ENV['DB_USER'] ?? null,
        'pass' => $_ENV['DB_PASS'] ?? null,
    ]
];
?>
