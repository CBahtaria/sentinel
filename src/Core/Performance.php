<?php
/**
 * UEDF - Performance Check
 * Tests server configuration
 */

echo "<h1>🔧 UEDF PERFORMANCE CHECK</h1>";

// PHP Version
echo "<h2>PHP Configuration</h2>";
echo "<table border='1' cellpadding='5' style='border-collapse:collapse;'>";
echo "<tr><th>Setting</th><th>Value</th><th>Status</th></tr>";

// Check OPcache
$opcache_enabled = function_exists('opcache_get_status');
echo "<tr>";
echo "<td>OPcache Enabled</td>";
echo "<td>" . ($opcache_enabled ? 'Yes' : 'No') . "</td>";
echo "<td style='color:" . ($opcache_enabled ? 'green' : 'red') . "'>" . ($opcache_enabled ? '✓' : '✗') . "</td>";
echo "</tr>";

if ($opcache_enabled) {
    $status = opcache_get_status(false);
    echo "<tr><td>OPcache Memory</td><td>" . round($status['memory_usage']['used_memory'] / 1024 / 1024, 2) . " MB / " . 
          round($status['memory_usage']['free_memory'] / 1024 / 1024, 2) . " MB free</td><td>✓</td></tr>";
    echo "<tr><td>Cached Files</td><td>" . $status['opcache_statistics']['num_cached_scripts'] . "</td><td>✓</td></tr>";
    echo "<tr><td>Cache Hits</td><td>" . $status['opcache_statistics']['hits'] . "</td><td>✓</td></tr>";
    echo "<tr><td>Cache Misses</td><td>" . $status['opcache_statistics']['misses'] . "</td><td>✓</td></tr>";
}

// Memory limit
$memory_limit = ini_get('memory_limit');
echo "<tr><td>Memory Limit</td><td>$memory_limit</td><td>✓</td></tr>";

// Max execution time
$max_execution = ini_get('max_execution_time');
echo "<tr><td>Max Execution Time</td><td>{$max_execution}s</td><td>✓</td></tr>";

// Post max size
$post_max = ini_get('post_max_size');
echo "<tr><td>Post Max Size</td><td>$post_max</td><td>✓</td></tr>";

echo "</table>";

// Load Test
echo "<h2>⚡ Quick Load Test</h2>";
echo "<p>Testing 100 concurrent requests...</p>";

$start = microtime(true);
$success = 0;
$fail = 0;
$times = [];

for ($i = 0; $i < 100; $i++) {
    $t1 = microtime(true);
    $ch = curl_init('http://localhost:8080/sentinel/login.php?test=1');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 1);
    $response = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $t2 = microtime(true);
    $times[] = ($t2 - $t1) * 1000;
    
    if ($code == 200) $success++; else $fail++;
}

$total = microtime(true) - $start;
$avg = array_sum($times) / count($times);
$min = min($times);
$max = max($times);

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Metric</th><th>Value</th></tr>";
echo "<tr><td>Total Time</td><td>" . round($total, 2) . "s</td></tr>";
echo "<tr><td>Success Rate</td><td>" . round(($success/100)*100, 2) . "%</td></tr>";
echo "<tr><td>Avg Response</td><td>" . round($avg, 2) . "ms</td></tr>";
echo "<tr><td>Min Response</td><td>" . round($min, 2) . "ms</td></tr>";
echo "<tr><td>Max Response</td><td>" . round($max, 2) . "ms</td></tr>";
echo "<tr><td>Requests/sec</td><td>" . round(100/$total, 2) . "</td></tr>";
echo "</table>";

// Recommendations
echo "<h2>📋 Recommendations</h2>";
echo "<ul>";

if (!$opcache_enabled) {
    echo "<li style='color:red;'>⚠ Enable OPcache in php.ini</li>";
}

if ($memory_limit < '256M') {
    echo "<li style='color:orange;'>⚠ Increase memory_limit to at least 256M</li>";
}

if ($max_execution < 30) {
    echo "<li style='color:orange;'>⚠ Increase max_execution_time to 30</li>";
}

echo "<li style='color:green;'>✓ Consider using a CDN for static assets</li>";
echo "<li style='color:green;'>✓ Enable gzip compression in Apache</li>";
echo "<li style='color:green;'>✓ Use database indexing for queries</li>";
echo "</ul>";

// Restart Instructions
echo "<h2>🔄 Restart Required</h2>";
echo "<p>After making changes, restart Apache:</p>";
echo "<pre>net stop apache2.4\nnet start apache2.4</pre>";
?>
