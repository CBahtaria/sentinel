<?php
declare(strict_types=1);
/**
 * Adaptive parameter approval endpoint.
 * GET  — returns pending adaptive changes from LCE NestJS backend.
 * POST /approve/<uuid> — Commander (superadmin) approves a pending change.
 *
 * RBAC: superadmin only (commander-equivalent). All other roles receive 403.
 * Audit log is append-only JSONL (Sentinel CLAUDE.md rule #6).
 */

// Security headers on every API response (Sentinel CLAUDE.md rule #8)
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: no-referrer');
header('Content-Security-Policy: default-src \'none\'');

// RBAC at the request layer — before any data access (rule #3)
// superadmin is the highest role; requireAuth terminates with 403 if insufficient.
$_sentinelAuth = new SentinelAuth();
$_currentUser = $_sentinelAuth->requireAuth('superadmin');

// Rate limiting (tighter limit for write operations)
$_rl = new UEDF\Middleware\RateLimitMiddleware(2, 20, 1);
if (!$_rl->check($_SERVER['REMOTE_ADDR'] ?? 'unknown', true)) {
    http_response_code(429);
    echo json_encode(['error' => 'Too Many Requests']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$path   = $_SERVER['PATH_INFO'] ?? '';

if ($method === 'POST' && preg_match('#^/approve/([a-f0-9-]{36})$#', $path, $m)) {
    $id = $m[1];

    // Audit log — append-only JSONL (rule #6). File path from environment only (rule #2).
    $auditPath = getenv('SENTINEL_AUDIT_LOG') ?: '/var/log/uav/sentinel-audit.jsonl';
    $event = json_encode([
        'ts'     => date('c'),
        'action' => 'ADAPTIVE_APPROVE',
        'id'     => $id,
        'user'   => $_currentUser['username'] ?? 'unknown',
        'ip'     => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    ]);
    file_put_contents($auditPath, $event . "\n", FILE_APPEND | LOCK_EX);

    // Invalidate LCE metrics cache so next request fetches fresh data
    try {
        $cache = UEDF\Cache\RedisCache::getInstance();
        $cache->delete('lce:metrics');
    } catch (\RuntimeException $e) {
        error_log("Adaptive cache invalidate error: " . $e->getMessage());
    }

    echo json_encode(['approved' => true, 'id' => $id]);

} elseif ($method === 'POST' && preg_match('#^/reject/([a-f0-9-]{36})$#', $path, $m)) {
    $id = $m[1];

    $auditPath = getenv('SENTINEL_AUDIT_LOG') ?: '/var/log/uav/sentinel-audit.jsonl';
    $event = json_encode([
        'ts'     => date('c'),
        'action' => 'ADAPTIVE_REJECT',
        'id'     => $id,
        'user'   => $_currentUser['username'] ?? 'unknown',
        'ip'     => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    ]);
    file_put_contents($auditPath, $event . "\n", FILE_APPEND | LOCK_EX);

    try {
        $cache = UEDF\Cache\RedisCache::getInstance();
        $cache->delete('lce:metrics');
    } catch (\RuntimeException $e) {
        error_log("Adaptive cache invalidate error: " . $e->getMessage());
    }

    echo json_encode(['rejected' => true, 'id' => $id]);

} elseif ($method === 'GET') {
    // Proxy pending adaptive changes from LCE NestJS backend.
    // LCE_BACKEND_URL must be set in environment (rule #2).
    $lceBase = getenv('LCE_BACKEND_URL');
    if ($lceBase === false || $lceBase === '') {
        // Fail closed — do not silently fall back to an assumed address (global rule #8).
        http_response_code(503);
        echo json_encode(['error' => 'LCE_BACKEND_URL not configured']);
        exit;
    }
    $ctx  = stream_context_create(['http' => ['timeout' => 3]]);
    $body = @file_get_contents("{$lceBase}/api/adaptive/pending", false, $ctx);
    if ($body === false) {
        echo json_encode([]);
    } else {
        echo $body;
    }

} else {
    http_response_code(404);
    echo json_encode(['error' => 'Not found']);
}
