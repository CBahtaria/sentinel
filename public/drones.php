<?php
require_once 'session.php';
/**
 * UEDF SENTINEL v4.0 - Drone Fleet Management
 * UMBUTFO ESWATINI DEFENCE FORCE
 */


if (!isset($_SESSION['user_id'])) {
    header('Location: ?module=login');
    exit;
}

$role = $_SESSION['role'] ?? 'viewer';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UEDF SENTINEL - DRONE FLEET</title>
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
            border: 2px solid #00ff9d;
            padding: 20px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 8px;
        }
        .header h1 {
            font-family: 'Orbitron', sans-serif;
            color: #00ff9d;
        }
        .back-btn {
            padding: 10px 20px;
            background: transparent;
            border: 1px solid #ff006e;
            color: #ff006e;
            text-decoration: none;
            border-radius: 4px;
        }
        .stats-bar {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat {
            background: #151f2c;
            border: 1px solid #00ff9d;
            padding: 15px;
            text-align: center;
            border-radius: 8px;
        }
        .stat-value {
            font-size: 2rem;
            color: #00ff9d;
            font-family: 'Orbitron', sans-serif;
        }
        .drone-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        .drone-card {
            background: #151f2c;
            border: 1px solid #ff006e;
            padding: 20px;
            border-radius: 8px;
            transition: 0.3s;
        }
        .drone-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(255,0,110,0.3);
        }
        .drone-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .drone-name {
            font-family: 'Orbitron', sans-serif;
            color: #00ff9d;
        }
        .drone-status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
        }
        .status-active { background: #00ff9d20; color: #00ff9d; border: 1px solid #00ff9d; }
        .status-standby { background: #ffbe0b20; color: #ffbe0b; border: 1px solid #ffbe0b; }
        .status-maintenance { background: #ff006e20; color: #ff006e; border: 1px solid #ff006e; }
        .drone-detail {
            margin: 10px 0;
            display: flex;
            justify-content: space-between;
        }
        .battery-bar {
            width: 100%;
            height: 10px;
            background: #0a0f1c;
            border-radius: 5px;
            margin: 10px 0;
        }
        .battery-level {
            height: 100%;
            background: linear-gradient(90deg, #ff006e, #00ff9d);
            border-radius: 5px;
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
        <h1><i class="fas fa-drone"></i> DRONE FLEET COMMAND</h1>
        <a href="?module=home" class="back-btn"><i class="fas fa-arrow-left"></i> BACK</a>
    </div>

    <div class="stats-bar">
        <div class="stat">
            <div class="stat-value">15</div>
            <div>TOTAL DRONES</div>
        </div>
        <div class="stat">
            <div class="stat-value" style="color: #00ff9d;">10</div>
            <div>ACTIVE</div>
        </div>
        <div class="stat">
            <div class="stat-value" style="color: #ffbe0b;">3</div>
            <div>STANDBY</div>
        </div>
        <div class="stat">
            <div class="stat-value" style="color: #ff006e;">2</div>
            <div>MAINTENANCE</div>
        </div>
    </div>

    <div class="drone-grid">
        <?php
        $drones = [
            ['name' => 'EAGLE-1', 'status' => 'ACTIVE', 'battery' => 95, 'location' => 'Sector 7', 'last_seen' => '2 min ago'],
            ['name' => 'HAWK-2', 'status' => 'ACTIVE', 'battery' => 87, 'location' => 'Sector 3', 'last_seen' => '5 min ago'],
            ['name' => 'FALCON-3', 'status' => 'STANDBY', 'battery' => 100, 'location' => 'Base', 'last_seen' => '15 min ago'],
            ['name' => 'RAVEN-4', 'status' => 'ACTIVE', 'battery' => 72, 'location' => 'Sector 9', 'last_seen' => '1 min ago'],
            ['name' => 'PHOENIX-5', 'status' => 'MAINTENANCE', 'battery' => 45, 'location' => 'Hangar', 'last_seen' => '2 hours ago'],
            ['name' => 'VIPER-6', 'status' => 'ACTIVE', 'battery' => 91, 'location' => 'Sector 2', 'last_seen' => '3 min ago'],
        ];
        
        foreach ($drones as $drone):
            $status_class = 'status-' . strtolower($drone['status']);
        ?>
        <div class="drone-card">
            <div class="drone-header">
                <span class="drone-name"><i class="fas fa-drone"></i> <?= $drone['name'] ?></span>
                <span class="drone-status <?= $status_class ?>"><?= $drone['status'] ?></span>
            </div>
            <div class="drone-detail">
                <span>Location:</span>
                <span style="color: #00ff9d;"><?= $drone['location'] ?></span>
            </div>
            <div class="drone-detail">
                <span>Last Seen:</span>
                <span style="color: #a0aec0;"><?= $drone['last_seen'] ?></span>
            </div>
            <div class="drone-detail">
                <span>Battery:</span>
                <span><?= $drone['battery'] ?>%</span>
            </div>
            <div class="battery-bar">
                <div class="battery-level" style="width: <?= $drone['battery'] ?>%;"></div>
            </div>
            <?php if ($role === 'commander' || $role === 'operator'): ?>
            <div style="display: flex; gap: 10px; margin-top: 15px;">
                <button style="flex:1; padding:8px; background:#00ff9d; border:none; border-radius:4px; cursor:pointer;">LAUNCH</button>
                <button style="flex:1; padding:8px; background:#ff006e; border:none; border-radius:4px; cursor:pointer;">RECALL</button>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="float-ai" onclick="window.location.href='?module=ai-assistant'">
        <i class="fas fa-robot" style="color: white; font-size: 24px;"></i>
    </div>
</body>
</html>

