<?php
/**
 * UEDF SENTINEL v4.0 - Enhanced Features Configuration
 * UMBUTFO ESWATINI DEFENCE FORCE
 */

// Create config directory if it doesn't exist
if (!is_dir(__DIR__)) {
    mkdir(__DIR__, 0777, true);
}

class Features {
    // WebSocket settings
    const WS_ENABLED = true;
    const WS_HOST = 'localhost';
    const WS_PORT = 8081;
    
    // Email settings
    const EMAIL_ENABLED = true;
    const SMTP_HOST = 'smtp.gmail.com';
    const SMTP_PORT = 587;
    const SMTP_USER = 'sentinel@uedf.gov.sz';
    const SMTP_PASS = 'your-password'; // Change this!
    
    // API settings
    const API_ENABLED = true;
    const API_KEY = 'uedf-sentinel-api-key-2026'; // Change this!
    const API_RATE_LIMIT = 100; // requests per minute
    
    // Real-time updates
    const REALTIME_REFRESH = 5000; // milliseconds
    
    // Feature flags
    public static $features = [
        'drone_auto_pilot' => true,
        'threat_prediction' => true,
        'weather_integration' => true,
        'mobile_push' => true,
        'offline_mode' => true,
        'biometric_login' => false // Coming soon
    ];
}
?>
