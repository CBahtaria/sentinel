<?php
require_once '../includes/session.php';
/**
 * UEDF SENTINEL v4.0 - Threat Analytics
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
    <title>UEDF SENTINEL - ANALYTICS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&family=Share+Tech+Mono&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Share Tech Mono', monospace; }
        body {
            background: #0a0f1c;
            color: #e0e0e0;
            padding: 20px;
        }
        .header {
            background: #151f2c;
            border: 2px solid #ffbe0b;
            padding: 20px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 8px;
        }
        .header h1 {
            font-family: 'Orbitron', sans-serif;
            color: #ffbe0b;
        }
        .back-btn {
            padding: 10px 20px;
            background: transparent;
            border: 1px solid #ff006e;
            color: #ff006e;
            text-decoration: none;
            border-radius: 4px;
        }
        .analytics-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }
        .chart-card {
            background: #151f2c;
            border: 1px solid #ffbe0b;
            padding: 20px;
            border-radius: 8px;
        }
        .chart-title {
            color: #ffbe0b;
            font-family: 'Orbitron', sans-serif;
            margin-bottom: 15px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }
        .stat-card {
            background: #151f2c;
            border: 1px solid #ffbe0b;
            padding: 20px;
            text-align: center;
            border-radius: 8px;
        }
        .stat-value {
            font-size: 2rem;
            color: #00ff9d;
            font-family: 'Orbitron', sans-serif;
        }
        canvas {
            width: 100% !important;
            height: 300px !important;
        }
        .time-range {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        .range-btn {
            padding: 8px 15px;
            background: #0a0f1c;
            border: 1px solid #ffbe0b;
            color: #ffbe0b;
            cursor: pointer;
            border-radius: 4px;
        }
        .range-btn.active {
            background: #ffbe0b;
            color: #0a0f1c;
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
        <h1><i class="fas fa-chart-line"></i> THREAT ANALYTICS</h1>
        <a href="?module=home" class="back-btn"><i class="fas fa-arrow-left"></i> BACK</a>
    </div>

    <div class="time-range">
        <button class="range-btn active">24 HOURS</button>
        <button class="range-btn">7 DAYS</button>
        <button class="range-btn">30 DAYS</button>
        <button class="range-btn">3 MONTHS</button>
        <button class="range-btn">YEAR</button>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value">156</div>
            <div>TOTAL INCIDENTS</div>
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
            <div class="stat-value" style="color: #4cc9f0;">86</div>
            <div>RESOLVED</div>
        </div>
    </div>

    <div class="analytics-grid">
        <div class="chart-card">
            <div class="chart-title"><i class="fas fa-chart-bar"></i> THREATS BY SEVERITY</div>
            <canvas id="severityChart"></canvas>
        </div>
        <div class="chart-card">
            <div class="chart-title"><i class="fas fa-chart-pie"></i> THREAT CATEGORIES</div>
            <canvas id="categoryChart"></canvas>
        </div>
        <div class="chart-card">
            <div class="chart-title"><i class="fas fa-chart-line"></i> THREAT TREND (7 DAYS)</div>
            <canvas id="trendChart"></canvas>
        </div>
        <div class="chart-card">
            <div class="chart-title"><i class="fas fa-clock"></i> RESPONSE TIME</div>
            <canvas id="responseChart"></canvas>
        </div>
    </div>

    <div class="float-ai" onclick="window.location.href='?module=ai-assistant'">
        <div class="ai-pulse"></div>
        <i class="fas fa-robot" style="color: white; font-size: 24px;"></i>
    </div>

    <script>
        // Severity Chart
        new Chart(document.getElementById('severityChart'), {
            type: 'bar',
            data: {
                labels: ['CRITICAL', 'HIGH', 'MEDIUM', 'LOW'],
                datasets: [{
                    data: [23, 47, 52, 34],
                    backgroundColor: ['#ff006e', '#ff8c00', '#ffbe0b', '#4cc9f0']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { grid: { color: '#ffbe0b20' } }
                }
            }
        });

        // Category Chart
        new Chart(document.getElementById('categoryChart'), {
            type: 'doughnut',
            data: {
                labels: ['Cyber Attack', 'Drone Intrusion', 'Border Crossing', 'Unauthorized Access'],
                datasets: [{
                    data: [45, 38, 42, 31],
                    backgroundColor: ['#ff006e', '#ff8c00', '#ffbe0b', '#4cc9f0']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { labels: { color: '#e0e0e0' } }
                }
            }
        });

        // Trend Chart
        new Chart(document.getElementById('trendChart'), {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Threats',
                    data: [12, 19, 15, 17, 24, 23, 18],
                    borderColor: '#ff006e',
                    backgroundColor: '#ff006e20',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { grid: { color: '#ffbe0b20' } }
                }
            }
        });

        // Response Time Chart
        new Chart(document.getElementById('responseChart'), {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Response Time (min)',
                    data: [4.2, 3.8, 4.5, 3.2, 2.8, 3.1, 2.5],
                    borderColor: '#00ff9d',
                    backgroundColor: '#00ff9d20',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { grid: { color: '#ffbe0b20' } }
                }
            }
        });
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
