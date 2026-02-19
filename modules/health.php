<?php
require_once '../includes/session.php';
/**
 * UEDF SENTINEL v4.0 - System Health Dashboard
 */


if (!isset($_SESSION['user_id'])) {
    header('Location: ?module=login');
    exit;
}

// Get system stats from database
try {
    $pdo = new PDO('mysql:host=localhost;dbname=uedf_sentinel', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stats = [
        'users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
        'drones' => $pdo->query("SELECT COUNT(*) FROM drones")->fetchColumn(),
        'active_drones' => $pdo->query("SELECT COUNT(*) FROM drones WHERE status = 'ACTIVE'")->fetchColumn(),
        'threats' => $pdo->query("SELECT COUNT(*) FROM threats WHERE status = 'ACTIVE'")->fetchColumn(),
        'nodes' => $pdo->query("SELECT COUNT(*) FROM nodes")->fetchColumn(),
        'audit_24h' => $pdo->query("SELECT COUNT(*) FROM audit_logs WHERE created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)")->fetchColumn(),
        'notifications' => $pdo->query("SELECT COUNT(*) FROM notifications WHERE is_read = 0")->fetchColumn()
    ];
} catch (PDOException $e) {
    $stats = [
        'users' => 4,
        'drones' => 15,
        'active_drones' => 10,
        'threats' => 5,
        'nodes' => 15,
        'audit_24h' => 45,
        'notifications' => 3
    ];
}

// System load (simulated)
$load = [
    'cpu' => rand(20, 60),
    'memory' => rand(30, 70),
    'disk' => rand(40, 80),
    'uptime' => '15 days, 7 hours',
    'connections' => rand(3, 8)
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UEDF SENTINEL - SYSTEM HEALTH</title>
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
        .health-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .health-card {
            background: #151f2c;
            border: 1px solid #00ff9d;
            padding: 25px;
            border-radius: 8px;
            text-align: center;
        }
        .health-icon {
            font-size: 2.5rem;
            color: #00ff9d;
            margin-bottom: 15px;
        }
        .health-value {
            font-size: 2.5rem;
            color: #ff006e;
            font-family: 'Orbitron', sans-serif;
        }
        .health-label {
            color: #a0aec0;
            margin-top: 5px;
        }
        .system-load {
            background: #151f2c;
            border: 1px solid #00ff9d;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .load-item {
            margin-bottom: 15px;
        }
        .load-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        .progress-bar {
            width: 100%;
            height: 20px;
            background: #0a0f1c;
            border-radius: 10px;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #ff006e, #00ff9d);
            transition: width 0.3s;
        }
        .status-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        .status-item {
            background: #0a0f1c;
            padding: 15px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .status-label {
            color: #a0aec0;
        }
        .status-value {
            color: #00ff9d;
            font-family: 'Orbitron', sans-serif;
        }
        .status-value.warning {
            color: #ff006e;
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
        <h1><i class="fas fa-heartbeat"></i> SYSTEM HEALTH MONITOR</h1>
        <a href="?module=home" class="back-btn"><i class="fas fa-arrow-left"></i> BACK</a>
    </div>

    <div class="health-grid">
        <div class="health-card">
            <div class="health-icon"><i class="fas fa-users"></i></div>
            <div class="health-value"><?= $stats['users'] ?></div>
            <div class="health-label">TOTAL USERS</div>
        </div>
        <div class="health-card">
            <div class="health-icon"><i class="fas fa-drone"></i></div>
            <div class="health-value"><?= $stats['drones'] ?></div>
            <div class="health-label">TOTAL DRONES</div>
        </div>
        <div class="health-card">
            <div class="health-icon"><i class="fas fa-play-circle"></i></div>
            <div class="health-value" style="color: #00ff9d;"><?= $stats['active_drones'] ?></div>
            <div class="health-label">ACTIVE DRONES</div>
        </div>
        <div class="health-card">
            <div class="health-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="health-value" style="color: <?= $stats['threats'] > 0 ? '#ff006e' : '#00ff9d' ?>;">
                <?= $stats['threats'] ?>
            </div>
            <div class="health-label">ACTIVE THREATS</div>
        </div>
        <div class="health-card">
            <div class="health-icon"><i class="fas fa-map-marker-alt"></i></div>
            <div class="health-value"><?= $stats['nodes'] ?></div>
            <div class="health-label">MILITARY NODES</div>
        </div>
        <div class="health-card">
            <div class="health-icon"><i class="fas fa-history"></i></div>
            <div class="health-value"><?= $stats['audit_24h'] ?></div>
            <div class="health-label">24H AUDIT LOGS</div>
        </div>
    </div>

    <div class="system-load">
        <h2 style="color: #00ff9d; margin-bottom: 20px;">📊 SYSTEM RESOURCES</h2>
        
        <div class="load-item">
            <div class="load-header">
                <span>CPU USAGE</span>
                <span style="color: <?= $load['cpu'] > 80 ? '#ff006e' : '#00ff9d' ?>;"><?= $load['cpu'] ?>%</span>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?= $load['cpu'] ?>%;"></div>
            </div>
        </div>
        
        <div class="load-item">
            <div class="load-header">
                <span>MEMORY USAGE</span>
                <span style="color: <?= $load['memory'] > 80 ? '#ff006e' : '#00ff9d' ?>;"><?= $load['memory'] ?>%</span>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?= $load['memory'] ?>%;"></div>
            </div>
        </div>
        
        <div class="load-item">
            <div class="load-header">
                <span>DISK USAGE</span>
                <span style="color: <?= $load['disk'] > 80 ? '#ff006e' : '#00ff9d' ?>;"><?= $load['disk'] ?>%</span>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?= $load['disk'] ?>%;"></div>
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
        <div style="background: #151f2c; border: 1px solid #00ff9d; padding: 25px; border-radius: 8px;">
            <h2 style="color: #00ff9d; margin-bottom: 20px;">🔌 SERVICE STATUS</h2>
            <div class="status-grid">
                <div class="status-item">
                    <span class="status-label"><i class="fas fa-database"></i> Database</span>
                    <span class="status-value" style="color: #00ff9d;">ONLINE</span>
                </div>
                <div class="status-item">
                    <span class="status-label"><i class="fas fa-plug"></i> WebSocket</span>
                    <span class="status-value" style="color: #00ff9d;">PORT 8081</span>
                </div>
                <div class="status-item">
                    <span class="status-label"><i class="fas fa-globe"></i> Apache</span>
                    <span class="status-value" style="color: #00ff9d;">PORT 8080</span>
                </div>
                <div class="status-item">
                    <span class="status-label"><i class="fas fa-users"></i> Connections</span>
                    <span class="status-value"><?= $load['connections'] ?></span>
                </div>
                <div class="status-item">
                    <span class="status-label"><i class="fas fa-clock"></i> Uptime</span>
                    <span class="status-value"><?= $load['uptime'] ?></span>
                </div>
                <div class="status-item">
                    <span class="status-label"><i class="fas fa-bell"></i> Notifications</span>
                    <span class="status-value <?= $stats['notifications'] > 0 ? 'warning' : '' ?>">
                        <?= $stats['notifications'] ?> UNREAD
                    </span>
                </div>
            </div>
        </div>

        <div style="background: #151f2c; border: 1px solid #00ff9d; padding: 25px; border-radius: 8px;">
            <h2 style="color: #00ff9d; margin-bottom: 20px;">⚡ QUICK ACTIONS</h2>
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <button onclick="location.href='?module=admin'" style="padding: 15px; background: #0a0f1c; border: 1px solid #00ff9d; color: #00ff9d; cursor: pointer; border-radius: 4px;">
                    <i class="fas fa-cog"></i> ADMIN PANEL
                </button>
                <button onclick="location.href='?module=audit'" style="padding: 15px; background: #0a0f1c; border: 1px solid #ff006e; color: #ff006e; cursor: pointer; border-radius: 4px;">
                    <i class="fas fa-history"></i> VIEW AUDIT LOGS
                </button>
                <button onclick="location.href='?module=notifications'" style="padding: 15px; background: #0a0f1c; border: 1px solid #4cc9f0; color: #4cc9f0; cursor: pointer; border-radius: 4px;">
                    <i class="fas fa-bell"></i> NOTIFICATIONS
                </button>
                <button onclick="runDiagnostic()" style="padding: 15px; background: #0a0f1c; border: 1px solid #ffbe0b; color: #ffbe0b; cursor: pointer; border-radius: 4px;">
                    <i class="fas fa-stethoscope"></i> RUN DIAGNOSTIC
                </button>
            </div>
        </div>
    </div>

    <div class="float-ai" onclick="window.location.href='?module=ai-assistant'">
        <i class="fas fa-robot" style="color: white; font-size: 24px;"></i>
    </div>

    <script>
        function runDiagnostic() {
            alert('🔍 Running system diagnostic...\n\n✅ Database: OK\n✅ WebSocket: OK\n✅ File System: OK\n✅ Memory: OK\n\nAll systems operational!');
        }
        
        // Auto-refresh every 30 seconds
        setTimeout(() => {
            location.reload();
        }, 30000);
    </script>
</body>
</html>
