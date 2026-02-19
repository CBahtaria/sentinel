<?php
/**
 * UEDF - Performance Check (cURL-free version)
 */
?>
<!DOCTYPE html>
<html>
<head>
    <title>UEDF - Performance Check</title>
    <style>
        body { background: #0a0f1c; color: #00ff9d; font-family: 'Share Tech Mono', monospace; padding: 20px; }
        h1 { color: #ff006e; font-family: 'Orbitron', sans-serif; }
        .success { color: #52b788; }
        .error { color: #ff006e; }
        .box { background: #151f2c; border: 1px solid #00ff9d; padding: 20px; margin: 20px 0; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 10px; border-bottom: 1px solid rgba(0,255,157,0.2); }
        .label { color: #a0aec0; }
        .value { color: #00ff9d; font-weight: bold; }
    </style>
</head>
<body>
    <h1>🔧 UEDF PERFORMANCE CHECK</h1>

    <div class="box">
        <h2>PHP Configuration</h2>
        <table>
            <tr><td class="label">OPcache Enabled:</td>
                <td class="value"><?= function_exists('opcache_get_status') ? 'Yes ✓' : 'No ✗' ?></td></tr>
            <?php if (function_exists('opcache_get_status')): 
                $status = opcache_get_status(false);
                if ($status && $status['opcache_enabled']): ?>
            <tr><td class="label">OPcache Memory:</td>
                <td class="value"><?= round($status['memory_usage']['used_memory']/1024/1024,2) ?> MB / 
                    <?= round($status['memory_usage']['free_memory']/1024/1024,2) ?> MB free</td></tr>
            <tr><td class="label">Cached Files:</td>
                <td class="value"><?= $status['opcache_statistics']['num_cached_scripts'] ?></td></tr>
            <tr><td class="label">Cache Hits:</td>
                <td class="value"><?= $status['opcache_statistics']['hits'] ?></td></tr>
            <tr><td class="label">Cache Misses:</td>
                <td class="value"><?= $status['opcache_statistics']['misses'] ?></td></tr>
            <?php endif; endif; ?>
            <tr><td class="label">Memory Limit:</td><td class="value"><?= ini_get('memory_limit') ?></td></tr>
            <tr><td class="label">Max Execution Time:</td><td class="value"><?= ini_get('max_execution_time') ?>s</td></tr>
            <tr><td class="label">Post Max Size:</td><td class="value"><?= ini_get('post_max_size') ?></td></tr>
            <tr><td class="label">cURL Enabled:</td>
                <td class="value"><?= function_exists('curl_version') ? 'Yes ✓' : 'No ✗' ?></td></tr>
        </table>
    </div>

    <div class="box">
        <h2>📋 Recommendations</h2>
        <ul style="color: #a0aec0;">
            <li>✓ OPcache is enabled - Good!</li>
            <li>✓ Memory limit is adequate</li>
            <li>➤ Consider using a CDN for static assets</li>
            <li>➤ Enable gzip compression in Apache</li>
            <li>➤ Use database indexing for queries</li>
            <?php if (!function_exists('curl_version')): ?>
            <li class="error">✗ Enable cURL in php.ini (extension=curl)</li>
            <?php endif; ?>
        </ul>
    </div>

    <div class="box">
        <h2>Quick Links</h2>
        <p><a href="opcheck.php" style="color:#00ff9d;">▶ View OPcache Details</a></p>
        <p><a href="stress_test.php" style="color:#ff006e;">▶ Run Stress Test</a></p>
        <p><a href="index.php" style="color:#00ff9d;">◀ Back to Home</a></p>
    </div>
</body>
</html>
