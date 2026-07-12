<?php
/**
 * UEDF SENTINEL v4.0 - Enhanced Features Configuration
 * Unified Eswatini Defence Force
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
    // SMTP_PASS intentionally omitted — loaded from env via smtpPass()
    
    // API settings
    const API_ENABLED = true;
    const API_RATE_LIMIT = 100; // requests per minute

    public static function apiKey(): string {
        $key = $_ENV['SENTINEL_API_KEY'] ?? getenv('SENTINEL_API_KEY');
        if ($key === false || $key === '') {
            throw new \RuntimeException('SENTINEL_API_KEY environment variable is not set — refusing to start.');
        }
        return $key;
    }

    public static function smtpPass(): string {
        $v = $_ENV['SENTINEL_SMTP_PASS'] ?? getenv('SENTINEL_SMTP_PASS');
        if (!$v) throw new \RuntimeException('SENTINEL_SMTP_PASS env var not set');
        return $v;
    }
    
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
