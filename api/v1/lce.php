<?php
declare(strict_types=1);
/**
 * LCE metrics API — aggregates from NATS lce.v1.metrics.* subjects.
 * Metrics are read from a JSON snapshot file written by the brain daemon
 * (lce_moderator coworker). Falls back to zeroed defaults if absent.
 *
 * RBAC: minimum role 'editor' (analyst-equivalent).
 * Roles in ascending order: viewer < editor < admin < superadmin.
 */

// Security headers on every API response (Sentinel CLAUDE.md rule #8)
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: no-referrer');
header('Content-Security-Policy: default-src \'none\'');

// RBAC at the request layer — before any data access (rule #3)
// Minimum role: editor. requireAuth() terminates with 401/403 if not satisfied.
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
    $cached = $cache->get('lce:metrics');
    if ($cached !== null) {
        header('X-Cache: HIT');
        echo json_encode($cached);
        exit;
    }
} catch (\RuntimeException $e) {
    // Redis miss or unavailable — fall through to file read
    error_log("LCE Redis error: " . $e->getMessage());
}

// Read from snapshot file written by brain daemon
$snapshotPath = getenv('LCE_METRICS_PATH') ?: '/var/lib/uav/lce_metrics.json';
$metrics = file_exists($snapshotPath)
    ? json_decode(file_get_contents($snapshotPath), true)
    : null;

if (!is_array($metrics)) {
    $metrics = [
        'user_count'        => 0,
        'mod_queue_depth'   => 0,
        'ban_rate_pct'      => 0.0,
        'spam_blocked_24h'  => 0,
        'adaptive_pending'  => 0,
        'timestamp'         => date('c'),
        'source'            => 'default',
    ];
}

// Cache for 30s
try {
    $cache = UEDF\Cache\RedisCache::getInstance();
    $cache->set('lce:metrics', $metrics, 30);
} catch (\RuntimeException $e) {
    // Non-fatal: proceed without caching
    error_log("LCE cache set error: " . $e->getMessage());
}

header('X-Cache: MISS');
echo json_encode($metrics);
