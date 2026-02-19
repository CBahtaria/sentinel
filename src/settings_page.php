<?php
// Database connection
require_once 'includes/db.php';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>UEDF Sentinel Settings</title>
    <style>
        body { background: #0a0f1e; color: #fff; font-family: Arial; padding: 20px; }
        h1 { color: #00ff00; border-bottom: 2px solid #00ff00; padding-bottom: 10px; }
        .card { background: #1a1f2e; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .stat { padding: 10px; margin: 5px 0; background: #0a0f1e; border-radius: 4px; }
        .label { color: #888; }
        .value { color: #00ff00; float: right; }
        a { color: #00ff00; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <h1>?? UEDF Sentinel Settings</h1>
    
    <div class="card">
        <h2>System Information</h2>
        <div class="stat">
            <span class="label">Current Time:</span>
            <span class="value"><?php echo date('Y-m-d H:i:s'); ?></span>
        </div>
        <div class="stat">
            <span class="label">PHP Version:</span>
            <span class="value"><?php echo phpversion(); ?></span>
        </div>
        <div class="stat">
            <span class="label">Server:</span>
            <span class="value"><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Apache'; ?></span>
        </div>
    </div>
    
    <div class="card">
        <h2>Database Statistics</h2>
        <?php
        if ($conn && $conn->ping()) {
            // Get drone count
            $result = $conn->query("SELECT COUNT(*) as count FROM drones");
            if ($result) {
                $row = $result->fetch_assoc();
                echo "<div class='stat'><span class='label'>Total Drones:</span> <span class='value'>" . $row['count'] . "</span></div>";
            }
            
            // Get active threats
            $result = $conn->query("SELECT COUNT(*) as count FROM threats WHERE status='ACTIVE'");
            if ($result) {
                $row = $result->fetch_assoc();
                echo "<div class='stat'><span class='label'>Active Threats:</span> <span class='value'>" . $row['count'] . "</span></div>";
            }
            
            // Get active missions
            $result = $conn->query("SELECT COUNT(*) as count FROM missions WHERE status='active'");
            if ($result) {
                $row = $result->fetch_assoc();
                echo "<div class='stat'><span class='label'>Active Missions:</span> <span class='value'>" . $row['count'] . "</span></div>";
            }
            
            echo "<div class='stat'><span class='label'>Database Status:</span> <span class='value' style='color:#00ff00;'>? Connected</span></div>";
        } else {
            echo "<div class='stat'><span class='label'>Database Status:</span> <span class='value' style='color:#ff4444;'>? Not Connected</span></div>";
        }
        ?>
    </div>
    
    <p><a href="dashboard.php">? Back to Dashboard</a></p>
</body>
</html>
