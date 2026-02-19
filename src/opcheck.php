<?php
/**
 * UEDF - OPcache Status Check
 */
?>
<!DOCTYPE html>
<html>
<head>
    <title>UEDF - OPcache Status</title>
    <style>
        body { background: #0a0f1c; color: #00ff9d; font-family: 'Share Tech Mono', monospace; padding: 20px; }
        h1 { color: #ff006e; font-family: 'Orbitron', sans-serif; }
        .success { color: #52b788; }
        .error { color: #ff006e; }
        .warning { color: #ffbe0b; }
        .box { background: #151f2c; border: 1px solid #00ff9d; padding: 20px; margin: 20px 0; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 10px; border-bottom: 1px solid rgba(0,255,157,0.2); }
        .label { color: #a0aec0; }
        .value { color: #00ff9d; font-weight: bold; }
    </style>
</head>
<body>
    <h1>🔧 OPcache Status Check</h1>
    
    <div class="box">
        <h2>PHP Information</h2>
        <table>
            <tr><td class="label">PHP Version:</td><td class="value"><?= phpversion() ?></td></tr>
            <tr><td class="label">Server:</td><td class="value"><?= $_SERVER['SERVER_SOFTWARE'] ?></td></tr>
        </table>
    </div>

    <div class="box">
        <h2>OPcache Status</h2>
        <?php
        if (function_exists('opcache_get_status')) {
            echo "<p class='success'>✓ OPcache extension IS loaded</p>";
            
            $status = opcache_get_status(false);
            if ($status && $status['opcache_enabled']) {
                echo "<p class='success' style='font-size:18px;'>✓ OPcache is ENABLED and working!</p>";
                
                echo "<h3>📊 Memory Usage:</h3>";
                echo "<table>";
                echo "<tr><td class='label'>Used Memory:</td><td class='value'>" . 
                     round($status['memory_usage']['used_memory'] / 1024 / 1024, 2) . " MB</td></tr>";
                echo "<tr><td class='label'>Free Memory:</td><td class='value'>" . 
                     round($status['memory_usage']['free_memory'] / 1024 / 1024, 2) . " MB</td></tr>";
                echo "<tr><td class='label'>Wasted Memory:</td><td class='value'>" . 
                     round($status['memory_usage']['wasted_memory'] / 1024 / 1024, 2) . " MB</td></tr>";
                echo "</table>";
                
                echo "<h3>📈 Statistics:</h3>";
                echo "<table>";
                echo "<tr><td class='label'>Cached Scripts:</td><td class='value'>" . 
                     $status['opcache_statistics']['num_cached_scripts'] . "</td></tr>";
                echo "<tr><td class='label'>Cache Hits:</td><td class='value'>" . 
                     $status['opcache_statistics']['hits'] . "</td></tr>";
                echo "<tr><td class='label'>Cache Misses:</td><td class='value'>" . 
                     $status['opcache_statistics']['misses'] . "</td></tr>";
                
                $hitRate = ($status['opcache_statistics']['hits'] / 
                           ($status['opcache_statistics']['hits'] + $status['opcache_statistics']['misses'])) * 100;
                echo "<tr><td class='label'>Hit Rate:</td><td class='value'>" . round($hitRate, 2) . "%</td></tr>";
                echo "</table>";
                
            } else {
                echo "<p class='error'>✗ OPcache is DISABLED in settings</p>";
            }
        } else {
            echo "<p class='error'>✗ OPcache extension is NOT loaded</p>";
            echo "<p class='warning'>Try adding 'zend_extension=opcache' to php.ini</p>";
        }
        ?>
    </div>

    <div class="box">
        <h2>Quick Links</h2>
        <p><a href="performance_check.php" style="color:#00ff9d;">▶ Run Performance Check</a></p>
        <p><a href="stress_test.php" style="color:#ff006e;">▶ Run Stress Test</a></p>
        <p><a href="index.php" style="color:#00ff9d;">◀ Back to Home</a></p>
    </div>
</body>
</html>
