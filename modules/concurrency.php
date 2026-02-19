<?php
require_once '../includes/session.php';
/**
 * UEDF SENTINEL v4.0 - Threat Monitor
 * UMBUTFO ESWATINI DEFENCE FORCE
 */


if (!isset($_SESSION['user_id'])) {
    header('Location: ?module=login');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UEDF SENTINEL - THREAT MONITOR</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&family=Share+Tech+Mono&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Share Tech Mono', monospace; }
        body {
            background: #0a0f1c;
            color: #e0e0e0;
            padding: 20px;
        }
        .header {
            background: #151f2c;
            border: 2px solid #ff006e;
            padding: 20px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 8px;
        }
        .header h1 {
            font-family: 'Orbitron', sans-serif;
            color: #ff006e;
        }
        .back-btn {
            padding: 10px 20px;
            background: transparent;
            border: 1px solid #00ff9d;
            color: #00ff9d;
            text-decoration: none;
            border-radius: 4px;
        }
        .threat-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: #151f2c;
            border: 1px solid #ff006e;
            padding: 20px;
            text-align: center;
            border-radius: 8px;
        }
        .stat-value {
            font-size: 2.5rem;
            font-family: 'Orbitron', sans-serif;
        }
        .critical { color: #ff006e; }
        .high { color: #ff8c00; }
        .medium { color: #ffbe0b; }
        .low { color: #4cc9f0; }
        
        .threat-table {
            background: #151f2c;
            border: 1px solid #ff006e;
            border-radius: 8px;
            overflow: hidden;
        }
        .threat-row {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 2fr 1fr;
            padding: 15px;
            border-bottom: 1px solid #ff006e40;
        }
        .threat-row.header {
            background: #ff006e20;
            color: #ff006e;
            font-family: 'Orbitron', sans-serif;
        }
        .severity-badge {
            padding: 5px 10px;
            border-radius: 20px;
            text-align: center;
            font-size: 0.8rem;
        }
        .float-ai {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #ff006e, #00ff9d);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border: 2px solid white;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1><i class="fas fa-brain"></i> THREAT MONITOR</h1>
        <a href="?module=home" class="back-btn"><i class="fas fa-arrow-left"></i> BACK</a>
    </div>

    <div class="threat-stats">
        <div class="stat-card">
            <div class="stat-value critical">2</div>
            <div>CRITICAL</div>
        </div>
        <div class="stat-card">
            <div class="stat-value high">3</div>
            <div>HIGH</div>
        </div>
        <div class="stat-card">
            <div class="stat-value medium">4</div>
            <div>MEDIUM</div>
        </div>
        <div class="stat-card">
            <div class="stat-value low">6</div>
            <div>LOW</div>
        </div>
    </div>

    <div class="threat-table">
        <div class="threat-row header">
            <div>THREAT NAME</div>
            <div>SEVERITY</div>
            <div>STATUS</div>
            <div>LOCATION</div>
            <div>DETECTED</div>
        </div>
        
        <?php
        $threats = [
            ['name' => 'Unauthorized Drone Incursion', 'severity' => 'CRITICAL', 'status' => 'ACTIVE', 'location' => 'Sector 7', 'time' => '2 min ago'],
            ['name' => 'Border Crossing Attempt', 'severity' => 'HIGH', 'status' => 'ACTIVE', 'location' => 'Northern Border', 'time' => '15 min ago'],
            ['name' => 'Suspicious Network Activity', 'severity' => 'MEDIUM', 'status' => 'INVESTIGATING', 'location' => 'Command Network', 'time' => '32 min ago'],
            ['name' => 'Unknown Radar Signature', 'severity' => 'HIGH', 'status' => 'ACTIVE', 'location' => 'Sector 3', 'time' => '47 min ago'],
            ['name' => 'Communication Intercept', 'severity' => 'LOW', 'status' => 'MONITORING', 'location' => 'Eastern Region', 'time' => '1 hour ago'],
        ];
        
        foreach ($threats as $threat):
            $severity_class = strtolower($threat['severity']);
        ?>
        <div class="threat-row">
            <div><?= $threat['name'] ?></div>
            <div>
                <span class="severity-badge <?= $severity_class ?>" style="background: <?= $severity_class ?>20; color: var(--<?= $severity_class ?>);">
                    <?= $threat['severity'] ?>
                </span>
            </div>
            <div><?= $threat['status'] ?></div>
            <div><?= $threat['location'] ?></div>
            <div><?= $threat['time'] ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="float-ai" onclick="window.location.href='?module=ai-assistant'">
        <i class="fas fa-robot" style="color: white; font-size: 24px;"></i>
    </div>
</body>
</html>
