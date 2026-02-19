<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UEDF Sentinel Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: #0a0f1c;
            color: #00ff9d;
            font-family: 'Segoe UI', monospace;
            padding: 20px;
        }
        .header {
            background: #151f2c;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 2px solid #ff006e;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        .stat-card {
            background: #151f2c;
            padding: 20px;
            border-radius: 10px;
            border: 1px solid #ff006e;
        }
        .stat-title {
            color: #ff006e;
            font-size: 14px;
            text-transform: uppercase;
            margin-bottom: 10px;
        }
        .stat-value {
            font-size: 32px;
            font-weight: bold;
            color: #00ff9d;
        }
        .stat-detail {
            margin-top: 10px;
            font-size: 12px;
            color: #4a5568;
        }
        .charts-container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        .chart-card {
            background: #151f2c;
            padding: 20px;
            border-radius: 10px;
            border: 1px solid #ff006e;
        }
        .recent-activity {
            background: #151f2c;
            padding: 20px;
            border-radius: 10px;
            border: 1px solid #ff006e;
        }
        .activity-item {
            padding: 10px;
            border-bottom: 1px solid #ff006e40;
            display: flex;
            justify-content: space-between;
        }
        .activity-item:last-child {
            border-bottom: none;
        }
        .badge {
            background: #ff006e;
            color: #0a0f1c;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
        }
        .menu {
            display: flex;
            gap: 20px;
            margin-top: 10px;
        }
        .menu a {
            color: #ff006e;
            text-decoration: none;
            padding: 5px 10px;
            border: 1px solid #ff006e;
            border-radius: 5px;
        }
        .menu a:hover {
            background: #ff006e;
            color: #0a0f1c;
        }
    </style>
</head>
<body>
    <div class="header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h1>?? UEDF SENTINEL DASHBOARD</h1>
            <div class="menu">
                <a href="/drones">Drones</a>
                <a href="/threats">Threats</a>
                <a href="/analytics">Analytics</a>
                <a href="/logout">Logout</a>
            </div>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-title">Total Drones</div>
            <div class="stat-value"><?= $droneStats['total'] ?? 0 ?></div>
            <div class="stat-detail">
                Active: <?= $droneStats['active'] ?? 0 ?> | 
                Maintenance: <?= $droneStats['maintenance'] ?? 0 ?> | 
                Offline: <?= $droneStats['offline'] ?? 0 ?>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-title">Active Threats</div>
            <div class="stat-value"><?= $threatStats['total'] ?? 0 ?></div>
            <div class="stat-detail">
                Critical: <?= $threatStats['critical'] ?? 0 ?> | 
                High: <?= $threatStats['high'] ?? 0 ?> | 
                Medium: <?= $threatStats['medium'] ?? 0 ?>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-title">System Health</div>
            <div class="stat-value"><?= $systemHealth['cpu'] ?></div>
            <div class="stat-detail">
                Memory: <?= $systemHealth['memory'] ?> | 
                Disk: <?= $systemHealth['disk'] ?> | 
                Uptime: <?= $systemHealth['uptime'] ?>
            </div>
        </div>
    </div>

    <div class="charts-container">
        <div class="chart-card">
            <canvas id="droneChart"></canvas>
        </div>
        <div class="chart-card">
            <canvas id="threatChart"></canvas>
        </div>
    </div>

    <div class="recent-activity">
        <h3 style="color: #ff006e; margin-bottom: 15px;">Recent Activity</h3>
        <div class="activity-item">
            <span>?? Drone D-07 completed mission</span>
            <span class="badge">2 min ago</span>
        </div>
        <div class="activity-item">
            <span>?? New threat detected in Sector 7</span>
            <span class="badge">5 min ago</span>
        </div>
        <div class="activity-item">
            <span>?? System update completed</span>
            <span class="badge">15 min ago</span>
        </div>
        <div class="activity-item">
            <span>?? Report generated</span>
            <span class="badge">1 hour ago</span>
        </div>
    </div>

    <script>
        // Drone Status Chart
        new Chart(document.getElementById('droneChart'), {
            type: 'doughnut',
            data: {
                labels: ['Active', 'Maintenance', 'Offline'],
                datasets: [{
                    data: [
                        <?= $droneStats['active'] ?? 0 ?>,
                        <?= $droneStats['maintenance'] ?? 0 ?>,
                        <?= $droneStats['offline'] ?? 0 ?>
                    ],
                    backgroundColor: ['#00ff9d', '#ff006e', '#4a5568']
                }]
            },
            options: {
                plugins: {
                    title: {
                        display: true,
                        text: 'Drone Status',
                        color: '#00ff9d'
                    }
                }
            }
        });

        // Threat Severity Chart
        new Chart(document.getElementById('threatChart'), {
            type: 'bar',
            data: {
                labels: ['Critical', 'High', 'Medium', 'Low'],
                datasets: [{
                    data: [
                        <?= $threatStats['critical'] ?? 0 ?>,
                        <?= $threatStats['high'] ?? 0 ?>,
                        <?= $threatStats['medium'] ?? 0 ?>,
                        <?= $threatStats['low'] ?? 0 ?>
                    ],
                    backgroundColor: ['#ff006e', '#ff6b6b', '#ffd93d', '#00ff9d']
                }]
            },
            options: {
                plugins: {
                    title: {
                        display: true,
                        text: 'Threat Severity',
                        color: '#00ff9d'
                    }
                }
            }
        });
    </script>
</body>
</html>
