<?php
require_once '../includes/session.php';
/**
 * UEDF SENTINEL v4.0 - System Audit Logs
 * UMBUTFO ESWATINI DEFENCE FORCE
 */


if (!isset($_SESSION['user_id'])) {
    header('Location: ?module=login');
    exit;
}

$role = $_SESSION['role'] ?? 'viewer';
// Only commander and analyst can view audit logs
if (!in_array($role, ['commander', 'analyst'])) {
    header('Location: ?module=home');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UEDF SENTINEL - AUDIT LOGS</title>
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
            border: 2px solid #4cc9f0;
            padding: 20px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 8px;
        }
        .header h1 {
            font-family: 'Orbitron', sans-serif;
            color: #4cc9f0;
        }
        .back-btn {
            padding: 10px 20px;
            background: transparent;
            border: 1px solid #ff006e;
            color: #ff006e;
            text-decoration: none;
            border-radius: 4px;
        }
        .filters {
            background: #151f2c;
            border: 1px solid #4cc9f0;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        .filter-group {
            flex: 1;
            min-width: 200px;
        }
        .filter-group label {
            display: block;
            color: #4cc9f0;
            margin-bottom: 5px;
            font-size: 0.9rem;
        }
        .filter-group select, .filter-group input {
            width: 100%;
            padding: 10px;
            background: #0a0f1c;
            border: 1px solid #4cc9f0;
            color: #00ff9d;
            border-radius: 4px;
        }
        .stats-bar {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }
        .stat-card {
            background: #151f2c;
            border: 1px solid #4cc9f0;
            padding: 15px;
            text-align: center;
            border-radius: 8px;
        }
        .stat-value {
            font-size: 1.8rem;
            color: #4cc9f0;
            font-family: 'Orbitron', sans-serif;
        }
        .audit-table {
            background: #151f2c;
            border: 1px solid #4cc9f0;
            border-radius: 8px;
            overflow: hidden;
        }
        .audit-row {
            display: grid;
            grid-template-columns: 1fr 2fr 1.5fr 2fr 1.5fr;
            padding: 15px;
            border-bottom: 1px solid #4cc9f040;
            font-size: 0.9rem;
        }
        .audit-row.header {
            background: #4cc9f020;
            color: #4cc9f0;
            font-family: 'Orbitron', sans-serif;
            font-weight: bold;
        }
        .audit-row:hover {
            background: #4cc9f010;
        }
        .log-level {
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            text-align: center;
            display: inline-block;
            width: fit-content;
        }
        .level-critical { background: #ff006e40; color: #ff006e; border: 1px solid #ff006e; }
        .level-high { background: #ff8c0040; color: #ff8c00; border: 1px solid #ff8c00; }
        .level-medium { background: #ffbe0b40; color: #ffbe0b; border: 1px solid #ffbe0b; }
        .level-low { background: #4cc9f040; color: #4cc9f0; border: 1px solid #4cc9f0; }
        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }
        .page-btn {
            padding: 8px 15px;
            background: #0a0f1c;
            border: 1px solid #4cc9f0;
            color: #4cc9f0;
            cursor: pointer;
            border-radius: 4px;
        }
        .page-btn:hover {
            background: #4cc9f0;
            color: #0a0f1c;
        }
        .export-btn {
            padding: 10px 20px;
            background: #00ff9d;
            border: none;
            color: #0a0f1c;
            cursor: pointer;
            border-radius: 4px;
            font-family: 'Orbitron', sans-serif;
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
            z-index: 1000;
        }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <h1><i class="fas fa-history"></i> SYSTEM AUDIT LOGS</h1>
            <span style="color: #4cc9f0; font-size: 0.9rem;">CLASSIFIED - EYES ONLY</span>
        </div>
        <div>
            <button class="export-btn" onclick="exportLogs()"><i class="fas fa-download"></i> EXPORT</button>
            <a href="?module=home" class="back-btn"><i class="fas fa-arrow-left"></i> BACK</a>
        </div>
    </div>

    <div class="stats-bar">
        <div class="stat-card">
            <div class="stat-value">1,247</div>
            <div>TOTAL EVENTS</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color: #ff006e;">23</div>
            <div>CRITICAL</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color: #ff8c00;">47</div>
            <div>HIGH</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color: #00ff9d;">156</div>
            <div>TODAY</div>
        </div>
    </div>

    <div class="filters">
        <div class="filter-group">
            <label><i class="fas fa-calendar"></i> DATE RANGE</label>
            <select>
                <option>Last 24 Hours</option>
                <option>Last 7 Days</option>
                <option>Last 30 Days</option>
                <option>Custom Range</option>
            </select>
        </div>
        <div class="filter-group">
            <label><i class="fas fa-exclamation-triangle"></i> LOG LEVEL</label>
            <select>
                <option>All Levels</option>
                <option>Critical</option>
                <option>High</option>
                <option>Medium</option>
                <option>Low</option>
            </select>
        </div>
        <div class="filter-group">
            <label><i class="fas fa-user"></i> USER</label>
            <select>
                <option>All Users</option>
                <option>Gen. Bartaria</option>
                <option>Sgt. Mamba</option>
                <option>Capt. Nkosi</option>
            </select>
        </div>
        <div class="filter-group">
            <label><i class="fas fa-search"></i> SEARCH</label>
            <input type="text" placeholder="Search logs...">
        </div>
    </div>

    <div class="audit-table">
        <div class="audit-row header">
            <div>TIMESTAMP</div>
            <div>EVENT</div>
            <div>USER</div>
            <div>DETAILS</div>
            <div>LEVEL</div>
        </div>
        
        <?php
        $logs = [
            ['time' => '2026-02-17 10:23:45', 'event' => 'User Login', 'user' => 'Gen. Bartaria', 'details' => 'Successful login from 192.168.1.100', 'level' => 'LOW'],
            ['time' => '2026-02-17 09:55:12', 'event' => 'Drone Launch', 'user' => 'Sgt. Mamba', 'details' => 'EAGLE-1 deployed to Sector 7', 'level' => 'MEDIUM'],
            ['time' => '2026-02-17 09:30:01', 'event' => 'Threat Detected', 'user' => 'SYSTEM', 'details' => 'Unauthorized drone incursion - Sector 3', 'level' => 'CRITICAL'],
            ['time' => '2026-02-17 08:45:33', 'event' => 'Configuration Change', 'user' => 'Capt. Nkosi', 'details' => 'Updated threat detection parameters', 'level' => 'HIGH'],
            ['time' => '2026-02-17 08:12:19', 'event' => 'Database Backup', 'user' => 'SYSTEM', 'details' => 'Automated backup completed', 'level' => 'LOW'],
            ['time' => '2026-02-17 07:55:47', 'event' => 'User Logout', 'user' => 'Lt. Dlamini', 'details' => 'Session terminated', 'level' => 'LOW'],
            ['time' => '2026-02-17 07:30:22', 'event' => 'Failed Login', 'user' => 'Unknown', 'details' => '3 failed attempts from 10.0.0.56', 'level' => 'HIGH'],
            ['time' => '2026-02-17 06:15:08', 'event' => 'System Update', 'user' => 'SYSTEM', 'details' => 'Security patches applied', 'level' => 'MEDIUM'],
        ];
        
        foreach ($logs as $log):
            $level_class = 'level-' . strtolower($log['level']);
        ?>
        <div class="audit-row">
            <div><?= $log['time'] ?></div>
            <div><?= $log['event'] ?></div>
            <div><?= $log['user'] ?></div>
            <div><?= $log['details'] ?></div>
            <div><span class="log-level <?= $level_class ?>"><?= $log['level'] ?></span></div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="pagination">
        <button class="page-btn"><i class="fas fa-chevron-left"></i></button>
        <button class="page-btn" style="background: #4cc9f0; color: #0a0f1c;">1</button>
        <button class="page-btn">2</button>
        <button class="page-btn">3</button>
        <button class="page-btn">4</button>
        <button class="page-btn">5</button>
        <button class="page-btn"><i class="fas fa-chevron-right"></i></button>
    </div>

    <div class="float-ai" onclick="window.location.href='?module=ai-assistant'">
        <div class="ai-pulse"></div>
        <i class="fas fa-robot" style="color: white; font-size: 24px;"></i>
    </div>

    <script>
        function exportLogs() {
            alert('Exporting audit logs...');
            // In production, this would generate CSV/PDF
        }
    </script>

    <style>
        .ai-pulse {
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: rgba(255,0,110,0.4);
            animation: pulse 2s infinite;
            z-index: -1;
        }
        @keyframes pulse {
            0% { transform: scale(1); opacity: 0.5; }
            100% { transform: scale(1.5); opacity: 0; }
        }
    </style>
</body>
</html>
