<?php
/**
 * Performance Monitor
 */
$start_time = microtime(true);
$start_memory = memory_get_usage();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Performance Monitor</title>
    <style>
        body { background: #0a0f1c; color: #00ff9d; font-family: monospace; padding: 20px; }
        .header { border-bottom: 2px solid #ff006e; padding: 20px; margin-bottom: 30px; }
        .grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 20px; }
        .card { background: #151f2c; border: 1px solid #ff006e; padding: 20px; border-radius: 8px; text-align: center; }
        .value { font-size: 32px; color: #ff006e; font-family: 'Orbitron', sans-serif; }
        .label { color: #a0aec0; margin-top: 10px; }
        a { color: #00ff9d; text-decoration: none; display: inline-block; margin-top: 30px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>📊 Performance Monitor</h1>
        <a href="?module=home">← Back to Command Center</a>
    </div>
    
    <div class="grid">
        <div class="card">
            <div class="value"><?php echo round((microtime(true) - $start_time) * 1000, 2); ?> ms</div>
            <div class="label">Page Load Time</div>
        </div>
        <div class="card">
            <div class="value"><?php echo round((memory_get_usage() - $start_memory) / 1024, 2); ?> KB</div>
            <div class="label">Memory Usage</div>
        </div>
        <div class="card">
            <div class="value"><?php echo round(memory_get_peak_usage() / 1024 / 1024, 2); ?> MB</div>
            <div class="label">Peak Memory</div>
        </div>
        <div class="card">
            <div class="value"><?php echo ini_get('max_execution_time'); ?> s</div>
            <div class="label">Max Execution Time</div>
        </div>
        <div class="card">
            <div class="value"><?php echo ini_get('memory_limit'); ?></div>
            <div class="label">Memory Limit</div>
        </div>
        <div class="card">
            <div class="value"><?php echo phpversion(); ?></div>
            <div class="label">PHP Version</div>
        </div>
    </div>
</body>
</html>
