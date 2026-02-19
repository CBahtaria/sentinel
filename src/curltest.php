<?php
/**
 * UEDF - cURL Verification Test
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UEDF - cURL Test</title>
    <style>
        body { background: #0a0f1c; color: #00ff9d; font-family: 'Share Tech Mono', monospace; padding: 20px; }
        h1 { color: #ff006e; font-family: 'Orbitron', sans-serif; }
        .success { color: #52b788; font-size: 20px; }
        .error { color: #ff006e; font-size: 20px; }
        .box { background: #151f2c; border: 1px solid #00ff9d; padding: 20px; margin: 20px 0; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 10px; border-bottom: 1px solid rgba(0,255,157,0.2); }
        .label { color: #a0aec0; }
        .value { color: #00ff9d; font-weight: bold; }
    </style>
</head>
<body>
    <h1>🔧 UEDF cURL Verification</h1>
    
    <div class="box">
        <h2>cURL Status</h2>
        <?php if (function_exists('curl_version')): ?>
            <p class="success">✓ cURL IS ENABLED!</p>
            <?php $version = curl_version(); ?>
            <table>
                <tr><td class="label">Version:</td><td class="value"><?= $version['version'] ?></td></tr>
                <tr><td class="label">SSL Version:</td><td class="value"><?= $version['ssl_version'] ?></td></tr>
                <tr><td class="label">Libz Version:</td><td class="value"><?= $version['libz_version'] ?></td></tr>
                <tr><td class="label">Protocols:</td><td class="value"><?= implode(', ', $version['protocols']) ?></td></tr>
            </table>
        <?php else: ?>
            <p class="error">✗ cURL IS NOT ENABLED</p>
        <?php endif; ?>
    </div>

    <div class="box">
        <h2>PHP Information</h2>
        <table>
            <tr><td class="label">PHP Version:</td><td class="value"><?= phpversion() ?></td></tr>
            <tr><td class="label">Extension Dir:</td><td class="value"><?= ini_get('extension_dir') ?></td></tr>
            <tr><td class="label">Loaded Extensions:</td><td class="value"><?= count(get_loaded_extensions()) ?></td></tr>
        </table>
    </div>

    <div class="box">
        <h2>Quick Links</h2>
        <p><a href="performance_check_fixed.php" style="color:#00ff9d;">▶ Run Performance Check</a></p>
        <p><a href="stress_test.php" style="color:#ff006e;">▶ Run Stress Test</a></p>
        <p><a href="opcheck.php" style="color:#00ff9d;">▶ View OPcache Status</a></p>
    </div>
</body>
</html>
