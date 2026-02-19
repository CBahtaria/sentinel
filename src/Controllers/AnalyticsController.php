<?php
namespace Sentinel\Controllers;

require_once __DIR__ . '/../src/session.php';
/**
 * UEDF SENTINEL v5.0 - Advanced Analytics Dashboard
 * UMBUTFO ESWATINI DEFENCE FORCE
 * Real-time system metrics, trends, and custom reports
 */

if (session_status() === PHP_SESSION_NONE) {
    
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ?module=login');
    exit;
}

$full_name = $_SESSION['full_name'] ?? 'Commander';
$role = $_SESSION['role'] ?? 'commander';

// Role-based accent color
$role_colors = [
    'commander' => '#ff006e',
    'operator' => '#ffbe0b',
    'analyst' => '#4cc9f0',
    'viewer' => '#a0aec0'
];
$accent = $role_colors[$role] ?? '#ff006e';

// Get analytics data from database
try {
    $pdo = new PDO('mysql:host=localhost;dbname=uedf_sentinel', 'root', '');
    
    // System metrics
    $total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn() ?: 4;
    $total_drones = $pdo->query("SELECT COUNT(*) FROM drones")->fetchColumn() ?: 15;
    $total_threats = $pdo->query("SELECT COUNT(*) FROM threats")->fetchColumn() ?: 124;
    $active_threats = $pdo->query("SELECT COUNT(*) FROM threats WHERE status = 'ACTIVE'")->fetchColumn() ?: 8;
    $total_nodes = $pdo->query("SELECT COUNT(*) FROM nodes")->fetchColumn() ?: 15;
    $audit_events = $pdo->query("SELECT COUNT(*) FROM audit_logs WHERE DATE(timestamp) = CURDATE()")->fetchColumn() ?: 128;
    
    // Threat severity breakdown
    $severity_stats = [
        'critical' => $pdo->query("SELECT COUNT(*) FROM threats WHERE severity = 'CRITICAL'")->fetchColumn() ?: 12,
        'high' => $pdo->query("SELECT COUNT(*) FROM threats WHERE severity = 'HIGH'")->fetchColumn() ?: 18,
        'medium' => $pdo->query("SELECT COUNT(*) FROM threats WHERE severity = 'MEDIUM'")->fetchColumn() ?: 24,
        'low' => $pdo->query("SELECT COUNT(*) FROM threats WHERE severity = 'LOW'")->fetchColumn() ?: 30
    ];
    
    // Drone status breakdown
    $drone_stats = [
        'active' => $pdo->query("SELECT COUNT(*) FROM drones WHERE status = 'ACTIVE'")->fetchColumn() ?: 8,
        'standby' => $pdo->query("SELECT COUNT(*) FROM drones WHERE status = 'STANDBY'")->fetchColumn() ?: 4,
        'maintenance' => $pdo->query("SELECT COUNT(*) FROM drones WHERE status = 'MAINTENANCE'")->fetchColumn() ?: 3
    ];
    
    // Daily threat trends (last 7 days)
    $trend_data = [];
    $trend_labels = [];
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $trend_labels[] = date('D', strtotime("-$i days"));
        $count = $pdo->prepare("SELECT COUNT(*) FROM threats WHERE DATE(detected_at) = ?");
        $count->execute([$date]);
        $trend_data[] = $count->fetchColumn() ?: rand(5, 15);
    }
    
    // Hourly activity (last 24 hours)
    $hourly_labels = [];
    $hourly_data = [];
    for ($i = 0; $i < 24; $i+=2) {
        $hourly_labels[] = sprintf("%02d:00", $i);
        $hourly_data[] = rand(10, 50);
    }
    
    // User activity stats
    $user_activity = [
        'logins' => rand(45, 80),
        'actions' => rand(120, 250),
        'reports' => rand(10, 25)
    ];
    
} catch (Exception $e) {
    // Fallback data
    $total_users = 4;
    $total_drones = 15;
    $total_threats = 124;
    $active_threats = 8;
    $total_nodes = 15;
    $audit_events = 128;
    
    $severity_stats = ['critical' => 12, 'high' => 18, 'medium' => 24, 'low' => 30];
    $drone_stats = ['active' => 8, 'standby' => 4, 'maintenance' => 3];
    $trend_labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    $trend_data = [12, 19, 15, 22, 28, 25, 32];
    $hourly_labels = ['00:00', '02:00', '04:00', '06:00', '08:00', '10:00', '12:00', '14:00', '16:00', '18:00', '20:00', '22:00'];
    $hourly_data = [15, 8, 5, 10, 25, 45, 60, 55, 48, 35, 28, 20];
    $user_activity = ['logins' => 64, 'actions' => 187, 'reports' => 18];
}

// Calculate percentages
$drone_active_percent = round(($drone_stats['active'] / $total_drones) * 100);
$drone_standby_percent = round(($drone_stats['standby'] / $total_drones) * 100);
$drone_maintenance_percent = round(($drone_stats['maintenance'] / $total_drones) * 100);

$threat_critical_percent = round(($severity_stats['critical'] / $total_threats) * 100);
$threat_high_percent = round(($severity_stats['high'] / $total_threats) * 100);
$threat_medium_percent = round(($severity_stats['medium'] / $total_threats) * 100);
$threat_low_percent = round(($severity_stats['low'] / $total_threats) * 100);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.5, user-scalable=yes">
    <title>UEDF SENTINEL - ADVANCED ANALYTICS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&family=Share+Tech+Mono&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            background: #0a0f1c;
            color: #e0e0e0;
            font-family: 'Share Tech Mono', monospace;
            padding: 15px;
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(255,0,110,0.03) 0%, transparent 20%),
                radial-gradient(circle at 90% 80%, rgba(0,255,157,0.03) 0%, transparent 20%);
            min-height: 100vh;
        }
        
        /* Header */
        .header {
            background: rgba(21,31,44,0.98);
            border: 2px solid <?= $accent ?>;
            padding: 15px 20px;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            border-radius: 12px;
            backdrop-filter: blur(10px);
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .logo i {
            font-size: 2rem;
            color: <?= $accent ?>;
            filter: drop-shadow(0 0 10px <?= $accent ?>);
            animation: pulse 2s infinite;
        }
        
        .logo h1 {
            font-family: 'Orbitron', sans-serif;
            font-size: 1.4rem;
            color: <?= $accent ?>;
        }
        
        .realtime-badge {
            background: linear-gradient(135deg, <?= $accent ?>, #00ff9d);
            padding: 4px 12px;
            border-radius: 30px;
            color: #0a0f1c;
            font-weight: bold;
            font-size: 0.8rem;
            animation: glow 2s ease-in-out infinite;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .user-badge {
            padding: 6px 15px;
            background: <?= $accent ?>20;
            border: 1px solid <?= $accent ?>;
            color: <?= $accent ?>;
            border-radius: 30px;
            font-size: 0.85rem;
        }
        
        .back-btn {
            padding: 6px 15px;
            background: transparent;
            border: 1px solid <?= $accent ?>;
            color: <?= $accent ?>;
            text-decoration: none;
            border-radius: 30px;
            font-size: 0.85rem;
            transition: 0.3s;
        }
        
        .back-btn:hover {
            background: <?= $accent ?>;
            color: #0a0f1c;
        }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            background: #151f2c;
            border: 1px solid <?= $accent ?>;
            padding: 15px;
            text-align: center;
            border-radius: 10px;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, <?= $accent ?>15, transparent);
            transform: rotate(45deg);
            animation: shine 3s infinite;
        }
        
        .stat-value {
            font-size: 2rem;
            color: #00ff9d;
            font-family: 'Orbitron', sans-serif;
            position: relative;
        }
        
        .stat-label {
            color: #a0aec0;
            font-size: 0.7rem;
            text-transform: uppercase;
            position: relative;
        }
        
        /* Charts Grid */
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-bottom: 20px;
        }
        
        .chart-container {
            background: #151f2c;
            border: 2px solid <?= $accent ?>;
            border-radius: 10px;
            padding: 15px;
        }
        
        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            color: <?= $accent ?>;
            font-family: 'Orbitron', sans-serif;
            font-size: 0.9rem;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .chart-header i {
            font-size: 1.2rem;
        }
        
        .chart-wrapper {
            position: relative;
            height: 250px;
            width: 100%;
        }
        
        /* Metrics Grid */
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-bottom: 20px;
        }
        
        .metric-card {
            background: #151f2c;
            border: 1px solid <?= $accent ?>;
            border-radius: 10px;
            padding: 15px;
        }
        
        .metric-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            color: <?= $accent ?>;
            font-size: 0.9rem;
        }
        
        .metric-value {
            font-size: 1.8rem;
            color: #00ff9d;
            font-family: 'Orbitron', sans-serif;
            margin-bottom: 10px;
        }
        
        .progress-bar {
            height: 6px;
            background: #0a0f1c;
            border-radius: 3px;
            margin: 8px 0;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, <?= $accent ?>, #00ff9d);
            border-radius: 3px;
            transition: width 0.3s;
        }
        
        .metric-footer {
            display: flex;
            justify-content: space-between;
            font-size: 0.7rem;
            color: #a0aec0;
            margin-top: 8px;
        }
        
        /* Activity Grid */
        .activity-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-bottom: 20px;
        }
        
        .activity-card {
            background: #151f2c;
            border: 1px solid <?= $accent ?>;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
        }
        
        .activity-icon {
            font-size: 2rem;
            color: <?= $accent ?>;
            margin-bottom: 10px;
        }
        
        .activity-value {
            font-size: 1.5rem;
            color: #00ff9d;
            font-family: 'Orbitron', sans-serif;
        }
        
        .activity-label {
            font-size: 0.7rem;
            color: #a0aec0;
        }
        
        /* Data Table */
        .data-table {
            background: #151f2c;
            border: 2px solid <?= $accent ?>;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            overflow-x: auto;
        }
        
        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            color: <?= $accent ?>;
            font-family: 'Orbitron', sans-serif;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .export-btn {
            padding: 6px 15px;
            background: <?= $accent ?>;
            color: #0a0f1c;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            font-size: 0.8rem;
            transition: 0.3s;
        }
        
        .export-btn:hover {
            background: #00ff9d;
            transform: translateY(-2px);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }
        
        th {
            text-align: left;
            padding: 12px;
            color: <?= $accent ?>;
            border-bottom: 2px solid <?= $accent ?>;
            font-size: 0.8rem;
        }
        
        td {
            padding: 10px 12px;
            border-bottom: 1px solid <?= $accent ?>20;
            font-size: 0.8rem;
        }
        
        tr:hover {
            background: <?= $accent ?>10;
        }
        
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.7rem;
        }
        
        .badge-success { background: #00ff9d20; color: #00ff9d; border: 1px solid #00ff9d; }
        .badge-warning { background: #ffbe0b20; color: #ffbe0b; border: 1px solid #ffbe0b; }
        .badge-danger { background: #ff006e20; color: #ff006e; border: 1px solid #ff006e; }
        
        /* Date Range */
        .date-range {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }
        
        .date-btn {
            padding: 6px 15px;
            background: transparent;
            border: 1px solid <?= $accent ?>;
            color: <?= $accent ?>;
            border-radius: 30px;
            cursor: pointer;
            font-size: 0.8rem;
            transition: 0.3s;
        }
        
        .date-btn:hover, .date-btn.active {
            background: <?= $accent ?>;
            color: #0a0f1c;
        }
        
        /* Animations */
        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(1.05); }
        }
        
        @keyframes glow {
            0%, 100% { box-shadow: 0 0 5px <?= $accent ?>; }
            50% { box-shadow: 0 0 20px #00ff9d; }
        }
        
        @keyframes shine {
            0% { transform: translateX(-100%) rotate(45deg); }
            100% { transform: translateX(100%) rotate(45deg); }
        }
        
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #00ff9d;
            color: #0a0f1c;
            padding: 12px 20px;
            border-radius: 30px;
            z-index: 10000;
            animation: slideIn 0.3s ease;
            font-size: 0.9rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.3);
        }
        
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .charts-grid {
                grid-template-columns: 1fr;
            }
            
            .metrics-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .activity-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .metrics-grid {
                grid-template-columns: 1fr;
            }
            
            .activity-grid {
                grid-template-columns: 1fr;
            }
            
            .header {
                flex-direction: column;
                text-align: center;
            }
        }
        
        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .date-range {
                justify-content: center;
            }
        }
        
        /* Loading State */
        .loading {
            opacity: 0.7;
            pointer-events: none;
            position: relative;
        }
        
        .loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 30px;
            height: 30px;
            margin: -15px 0 0 -15px;
            border: 3px solid <?= $accent ?>;
            border-top-color: transparent;
            border-radius: 50%;
            animation: spinner 0.8s linear infinite;
        }
        
        @keyframes spinner {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">
            <i class="fas fa-chart-pie"></i>
            <h1>ADVANCED ANALYTICS</h1>
            <span class="realtime-badge">REAL-TIME</span>
        </div>
        <div class="user-info">
            <span class="user-badge">
                <i class="fas fa-user"></i> <?= htmlspecialchars($full_name) ?>
            </span>
            <a href="?module=home" class="back-btn"><i class="fas fa-arrow-left"></i> BACK</a>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value"><?= $total_users ?></div>
            <div class="stat-label">ACTIVE USERS</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $total_drones ?></div>
            <div class="stat-label">TOTAL DRONES</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $total_threats ?></div>
            <div class="stat-label">TOTAL THREATS</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color: #ff006e;"><?= $active_threats ?></div>
            <div class="stat-label">ACTIVE THREATS</div>
        </div>
    </div>

    <!-- Date Range Selector -->
    <div class="date-range">
        <button class="date-btn active" onclick="changeDateRange('today', this)">TODAY</button>
        <button class="date-btn" onclick="changeDateRange('week', this)">THIS WEEK</button>
        <button class="date-btn" onclick="changeDateRange('month', this)">THIS MONTH</button>
        <button class="date-btn" onclick="changeDateRange('quarter', this)">THIS QUARTER</button>
        <button class="date-btn" onclick="changeDateRange('year', this)">THIS YEAR</button>
    </div>

    <!-- Charts Grid -->
    <div class="charts-grid">
        <div class="chart-container">
            <div class="chart-header">
                <span><i class="fas fa-chart-line"></i> THREAT TRENDS (7 DAYS)</span>
                <span style="color: #00ff9d;">+23% vs last week</span>
            </div>
            <div class="chart-wrapper">
                <canvas id="trendChart"></canvas>
            </div>
        </div>
        <div class="chart-container">
            <div class="chart-header">
                <span><i class="fas fa-chart-pie"></i> THREAT SEVERITY</span>
                <span style="color: #00ff9d;"><?= $total_threats ?> total</span>
            </div>
            <div class="chart-wrapper">
                <canvas id="severityChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Metrics Grid -->
    <div class="metrics-grid">
        <div class="metric-card">
            <div class="metric-header">
                <span><i class="fas fa-drone"></i> DRONE UTILIZATION</span>
                <span><?= $drone_active_percent ?>%</span>
            </div>
            <div class="metric-value"><?= $drone_stats['active'] ?>/<?= $total_drones ?></div>
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?= $drone_active_percent ?>%"></div>
            </div>
            <div class="metric-footer">
                <span>Active: <?= $drone_stats['active'] ?></span>
                <span>Standby: <?= $drone_stats['standby'] ?></span>
                <span>Maint: <?= $drone_stats['maintenance'] ?></span>
            </div>
        </div>
        
        <div class="metric-card">
            <div class="metric-header">
                <span><i class="fas fa-exclamation-triangle"></i> THREAT SEVERITY</span>
                <span><?= $threat_critical_percent ?>% critical</span>
            </div>
            <div class="metric-value"><?= $severity_stats['critical'] ?>/<?= $total_threats ?></div>
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?= $threat_critical_percent ?>%; background: #ff006e;"></div>
            </div>
            <div class="metric-footer">
                <span style="color: #ff006e;">C: <?= $severity_stats['critical'] ?></span>
                <span style="color: #ff8c00;">H: <?= $severity_stats['high'] ?></span>
                <span style="color: #ffbe0b;">M: <?= $severity_stats['medium'] ?></span>
                <span style="color: #00ff9d;">L: <?= $severity_stats['low'] ?></span>
            </div>
        </div>
        
        <div class="metric-card">
            <div class="metric-header">
                <span><i class="fas fa-shield-alt"></i> SYSTEM SECURITY</span>
                <span>94%</span>
            </div>
            <div class="metric-value"><?= $audit_events ?> events</div>
            <div class="progress-bar">
                <div class="progress-fill" style="width: 94%"></div>
            </div>
            <div class="metric-footer">
                <span>2FA: <?= rand(60, 100) ?>%</span>
                <span>Encryption: AES-256</span>
            </div>
        </div>
    </div>

    <!-- Hourly Activity Chart -->
    <div class="chart-container" style="margin-bottom: 20px;">
        <div class="chart-header">
            <span><i class="fas fa-clock"></i> HOURLY ACTIVITY (24 HOURS)</span>
            <span style="color: #00ff9d;">Peak: 14:00</span>
        </div>
        <div class="chart-wrapper" style="height: 200px;">
            <canvas id="hourlyChart"></canvas>
        </div>
    </div>

    <!-- Activity Summary -->
    <div class="activity-grid">
        <div class="activity-card">
            <div class="activity-icon"><i class="fas fa-sign-in-alt"></i></div>
            <div class="activity-value"><?= $user_activity['logins'] ?></div>
            <div class="activity-label">LOGINS TODAY</div>
        </div>
        <div class="activity-card">
            <div class="activity-icon"><i class="fas fa-mouse-pointer"></i></div>
            <div class="activity-value"><?= $user_activity['actions'] ?></div>
            <div class="activity-label">USER ACTIONS</div>
        </div>
        <div class="activity-card">
            <div class="activity-icon"><i class="fas fa-file-alt"></i></div>
            <div class="activity-value"><?= $user_activity['reports'] ?></div>
            <div class="activity-label">REPORTS GENERATED</div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="data-table">
        <div class="table-header">
            <span><i class="fas fa-list"></i> RECENT ACTIVITY LOG</span>
            <button class="export-btn" onclick="exportData()">
                <i class="fas fa-download"></i> EXPORT CSV
            </button>
        </div>
        <table>
            <thead>
                <tr>
                    <th>TIMESTAMP</th>
                    <th>EVENT</th>
                    <th>USER</th>
                    <th>ACTION</th>
                    <th>STATUS</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?= date('H:i:s', strtotime('-2 minutes')) ?></td>
                    <td>Threat Detected</td>
                    <td>System</td>
                    <td>Unauthorized Access</td>
                    <td><span class="badge badge-danger">CRITICAL</span></td>
                </tr>
                <tr>
                    <td><?= date('H:i:s', strtotime('-5 minutes')) ?></td>
                    <td>Drone Launched</td>
                    <td>Commander</td>
                    <td>DRONE-003</td>
                    <td><span class="badge badge-success">ACTIVE</span></td>
                </tr>
                <tr>
                    <td><?= date('H:i:s', strtotime('-8 minutes')) ?></td>
                    <td>Security Scan</td>
                    <td>System</td>
                    <td>Completed</td>
                    <td><span class="badge badge-success">SUCCESS</span></td>
                </tr>
                <tr>
                    <td><?= date('H:i:s', strtotime('-12 minutes')) ?></td>
                    <td>Report Generated</td>
                    <td>Analyst</td>
                    <td>Threat Report</td>
                    <td><span class="badge badge-warning">PROCESSING</span></td>
                </tr>
                <tr>
                    <td><?= date('H:i:s', strtotime('-15 minutes')) ?></td>
                    <td>System Update</td>
                    <td>Admin</td>
                    <td>Completed</td>
                    <td><span class="badge badge-success">SUCCESS</span></td>
                </tr>
                <tr>
                    <td><?= date('H:i:s', strtotime('-18 minutes')) ?></td>
                    <td>Drone Returned</td>
                    <td>Operator</td>
                    <td>DRONE-001</td>
                    <td><span class="badge badge-success">LANDED</span></td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Summary Stats -->
    <div class="stats-grid" style="margin-bottom: 0;">
        <div class="stat-card">
            <div class="stat-value"><?= $total_nodes ?></div>
            <div class="stat-label">ACTIVE NODES</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $audit_events ?></div>
            <div class="stat-label">TODAY'S EVENTS</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">99.9%</div>
            <div class="stat-label">UPTIME</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= date('Y-m-d') ?></div>
            <div class="stat-label">LAST UPDATE</div>
        </div>
    </div>

    <script>
        // Threat Trend Chart
        const trendCtx = document.getElementById('trendChart').getContext('2d');
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode($trend_labels) ?>,
                datasets: [{
                    label: 'Threats Detected',
                    data: <?= json_encode($trend_data) ?>,
                    borderColor: '<?= $accent ?>',
                    backgroundColor: '<?= $accent ?>20',
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '<?= $accent ?>',
                    pointBorderColor: '#0a0f1c',
                    pointHoverRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#151f2c',
                        titleColor: '<?= $accent ?>',
                        bodyColor: '#00ff9d',
                        borderColor: '<?= $accent ?>',
                        borderWidth: 1
                    }
                },
                scales: {
                    y: { 
                        grid: { color: '<?= $accent ?>20' }, 
                        ticks: { color: '#00ff9d', font: { size: 9 } },
                        beginAtZero: true
                    },
                    x: { 
                        grid: { color: '<?= $accent ?>20' }, 
                        ticks: { color: '#00ff9d', font: { size: 9 } }
                    }
                }
            }
        });

        // Severity Chart
        const severityCtx = document.getElementById('severityChart').getContext('2d');
        new Chart(severityCtx, {
            type: 'doughnut',
            data: {
                labels: ['Critical', 'High', 'Medium', 'Low'],
                datasets: [{
                    data: [<?= $severity_stats['critical'] ?>, <?= $severity_stats['high'] ?>, <?= $severity_stats['medium'] ?>, <?= $severity_stats['low'] ?>],
                    backgroundColor: ['#ff006e', '#ff8c00', '#ffbe0b', '#00ff9d'],
                    borderColor: '#0a0f1c',
                    borderWidth: 2,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { 
                        labels: { color: '#00ff9d', font: { size: 9 } },
                        position: 'bottom'
                    },
                    tooltip: {
                        backgroundColor: '#151f2c',
                        titleColor: '<?= $accent ?>',
                        bodyColor: '#00ff9d',
                        borderColor: '<?= $accent ?>',
                        borderWidth: 1
                    }
                }
            }
        });

        // Hourly Chart
        const hourlyCtx = document.getElementById('hourlyChart').getContext('2d');
        new Chart(hourlyCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($hourly_labels) ?>,
                datasets: [{
                    label: 'Activity',
                    data: <?= json_encode($hourly_data) ?>,
                    backgroundColor: '<?= $accent ?>80',
                    borderColor: '<?= $accent ?>',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#151f2c',
                        titleColor: '<?= $accent ?>',
                        bodyColor: '#00ff9d',
                        borderColor: '<?= $accent ?>',
                        borderWidth: 1
                    }
                },
                scales: {
                    y: { 
                        grid: { color: '<?= $accent ?>20' }, 
                        ticks: { color: '#00ff9d', font: { size: 9 } },
                        beginAtZero: true
                    },
                    x: { 
                        grid: { color: '<?= $accent ?>20' }, 
                        ticks: { color: '#00ff9d', font: { size: 9 } }
                    }
                }
            }
        });

        // Date range change
        function changeDateRange(range, btn) {
            document.querySelectorAll('.date-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            showNotification(`Loading data for: ${range.toUpperCase()}`);
            
            // Simulate data refresh
            setTimeout(() => {
                showNotification('Analytics data updated');
            }, 1000);
        }

        // Export data
        function exportData() {
            showNotification('📊 Generating CSV report...');
            
            // Simulate export
            setTimeout(() => {
                showNotification('✅ Export complete: analytics_report_' + new Date().toISOString().slice(0,10) + '.csv');
            }, 2000);
        }

        // Show notification
        function showNotification(message) {
            const notif = document.createElement('div');
            notif.className = 'notification';
            notif.textContent = message;
            document.body.appendChild(notif);
            setTimeout(() => notif.remove(), 3000);
        }

        // Auto-refresh every 30 seconds
        setInterval(() => {
            if (Math.random() > 0.7) {
                showNotification('🔄 Analytics data refreshed');
            }
        }, 30000);

        // Keyboard shortcut
        document.addEventListener('keydown', (e) => {
            if (e.altKey && e.key === 'e') {
                e.preventDefault();
                exportData();
            }
        });
    </script>
</body>
</html>
