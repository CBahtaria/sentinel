<?php
declare(strict_types=1);
/**
 * Ecosystem status API — reads from a JSON snapshot written by the brain daemon.
 * Covers reservoir, capacitor SoC, WLAN mesh, solar, and environmental conditions.
 *
 * RBAC: minimum role 'editor' (analyst-equivalent).
 */

// Security headers on every API response (Sentinel CLAUDE.md rule #8)
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: no-referrer');
header('Content-Security-Policy: default-src \'none\'');

// RBAC at the request layer — before any data access (rule #3)
$_sentinelAuth = new SentinelAuth();
$_currentUser = $_sentinelAuth->requireAuth('editor');

// Rate limiting
$_rl = new UEDF\Middleware\RateLimitMiddleware(5, 50, 1);
if (!$_rl->check($_SERVER['REMOTE_ADDR'] ?? 'unknown', true)) {
    http_response_code(429);
    echo json_encode(['error' => 'Too Many Requests']);
    exit;
}

// Try Redis cache first (30s TTL)
try {
    $cache = UEDF\Cache\RedisCache::getInstance();
    $cached = $cache->get('ecosystem:status');
    if ($cached !== null) {
        header('X-Cache: HIT');
        echo json_encode($cached);
        exit;
    }
} catch (\RuntimeException $e) {
    error_log("Ecosystem Redis error: " . $e->getMessage());
}

$snapshotPath = getenv('ECOSYSTEM_STATUS_PATH') ?: '/var/lib/uav/ecosystem_status.json';
$status = file_exists($snapshotPath)
    ? json_decode(file_get_contents($snapshotPath), true)
    : null;

if (!is_array($status)) {
    $status = [
        'reservoir_pct'   => 0.0,
        'capacitor_soc'   => 0.0,
        'wlan_nodes'      => 0,
        'avg_rssi_dbm'    => -100.0,
        'solar_active'    => false,
        'fog_density'     => 'unknown',
        'timestamp'       => date('c'),
        'source'          => 'default',
    ];
}

try {
    $cache = UEDF\Cache\RedisCache::getInstance();
    $cache->set('ecosystem:status', $status, 30);
} catch (\RuntimeException $e) {
    error_log("Ecosystem cache set error: " . $e->getMessage());
}

header('X-Cache: MISS');
echo json_encode($status);
