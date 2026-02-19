<?php
require_once '../includes/session.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    header('Location: ?module=login');
    exit;
}

$username = $_SESSION['username'] ?? 'OPERATOR';
$role = $_SESSION['role'] ?? 'commander';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UEDF SENTINEL - ANALYTICS DASHBOARD</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&family=Share+Tech+Mono&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            background: #0a0f1c;
            color: #fff;
            font-family: 'Share Tech Mono', monospace;
            padding: 20px;
            min-height: 100vh;
        }
        
        .header {
            background: rgba(26, 31, 46, 0.95);
            border: 2px solid #00ff9d;
            padding: 20px 30px;
            margin-bottom: 30px;
            border-radius: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 0 30px rgba(0,255,157,0.2);
        }
        
        .header h1 {
            font-family: 'Orbitron', sans-serif;
            color: #00ff9d;
            font-size: 2rem;
            text-shadow: 0 0 10px rgba(0,255,157,0.5);
        }
        
        .nav {
            display: flex;
            gap: 15px;
        }
        
        .nav a {
            color: #00ff9d;
            text-decoration: none;
            padding: 8px 20px;
            border: 1px solid #00ff9d;
            border-radius: 30px;
            transition: all 0.3s;
        }
        
        .nav a:hover {
            background: #00ff9d;
            color: #0a0f1c;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: #1a1f2e;
            border: 1px solid #00ff9d;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }
        
        .stat-value {
            font-size: 2.5rem;
            color: #00ff9d;
            font-family: 'Orbitron', sans-serif;
        }
        
        .stat-label {
            color: #888;
            font-size: 0.9rem;
            text-transform: uppercase;
        }
        
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .chart-card {
            background: #1a1f2e;
            border: 1px solid #00ff9d;
            border-radius: 12px;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }
        
        .chart-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(0,255,157,0.05), transparent);
            transform: rotate(45deg);
            animation: shine 3s infinite;
        }
        
        @keyframes shine {
            0% { transform: translateX(-100%) rotate(45deg); }
            100% { transform: translateX(100%) rotate(45deg); }
        }
        
        .chart-title {
            font-family: 'Orbitron', sans-serif;
            color: #00ff9d;
            margin-bottom: 20px;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .chart-container {
            position: relative;
            height: 300px;
            margin: 20px 0;
        }
        
        .full-width {
            grid-column: span 2;
        }
        
        .update-time {
            position: absolute;
            top: 20px;
            right: 20px;
            color: #4a5568;
            font-size: 0.8rem;
        }
        
        .refresh-btn {
            background: transparent;
            border: 1px solid #00ff9d;
            color: #00ff9d;
            padding: 8px 20px;
            border-radius: 30px;
            cursor: pointer;
            font-family: 'Share Tech Mono', monospace;
            transition: all 0.3s;
            margin-left: 20px;
        }
        
        .refresh-btn:hover {
            background: #00ff9d;
            color: #0a0f1c;
        }
        
        .refresh-btn i {
            margin-right: 5px;
        }
        
        .footer {
            text-align: center;
            color: #4a5568;
            margin-top: 40px;
            padding: 20px;
            border-top: 1px solid #00ff9d40;
        }
        
        @media (max-width: 1200px) {
            .charts-grid {
                grid-template-columns: 1fr;
            }
            .full-width {
                grid-column: span 1;
            }
        }
        
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        .loading {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: #00ff9d;
            font-size: 1.2rem;
        }
        
        .loading i {
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1><i class="fas fa-chart-pie"></i> UEDF ANALYTICS DASHBOARD</h1>
        <div class="nav">
            <a href="?module=home"><i class="fas fa-home"></i> HOME</a>
            <a href="?module=drones"><i class="fas fa-drone"></i> DRONES</a>
            <a href="?module=concurrency"><i class="fas fa-brain"></i> THREATS</a>
            <button class="refresh-btn" onclick="refreshAllCharts()">
                <i class="fas fa-sync-alt"></i> REFRESH
            </button>
        </div>
    </div>

    <!-- Stats Summary -->
    <div class="stats-grid" id="stats-summary">
        <div class="stat-card">
            <div class="stat-value" id="total-drones">-</div>
            <div class="stat-label">TOTAL DRONES</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" id="active-threats">-</div>
            <div class="stat-label">ACTIVE THREATS</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" id="active-missions">-</div>
            <div class="stat-label">ACTIVE MISSIONS</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" id="avg-battery">-</div>
            <div class="stat-label">AVG BATTERY</div>
        </div>
    </div>

    <!-- Charts Grid -->
    <div class="charts-grid">
        <!-- Drone Status Pie Chart -->
        <div class="chart-card">
            <div class="chart-title">
                <i class="fas fa-drone"></i> DRONE STATUS DISTRIBUTION
            </div>
            <div class="chart-container">
                <canvas id="droneChart"></canvas>
                <div class="loading" id="drone-loading"><i class="fas fa-spinner"></i> LOADING...</div>
            </div>
        </div>

        <!-- Threat Severity Bar Chart -->
        <div class="chart-card">
            <div class="chart-title">
                <i class="fas fa-exclamation-triangle"></i> THREAT SEVERITY
            </div>
            <div class="chart-container">
                <canvas id="threatChart"></canvas>
                <div class="loading" id="threat-loading"><i class="fas fa-spinner"></i> LOADING...</div>
            </div>
        </div>

        <!-- Mission Status Doughnut Chart -->
        <div class="chart-card">
            <div class="chart-title">
                <i class="fas fa-tasks"></i> MISSION STATUS
            </div>
            <div class="chart-container">
                <canvas id="missionChart"></canvas>
                <div class="loading" id="mission-loading"><i class="fas fa-spinner"></i> LOADING...</div>
            </div>
        </div>

        <!-- Battery Levels Horizontal Bar -->
        <div class="chart-card">
            <div class="chart-title">
                <i class="fas fa-battery-three-quarters"></i> DRONE BATTERY LEVELS
            </div>
            <div class="chart-container">
                <canvas id="batteryChart"></canvas>
                <div class="loading" id="battery-loading"><i class="fas fa-spinner"></i> LOADING...</div>
            </div>
        </div>

        <!-- Timeline Chart (Full Width) -->
        <div class="chart-card full-width">
            <div class="chart-title">
                <i class="fas fa-chart-line"></i> THREAT ACTIVITY (LAST 24 HOURS)
            </div>
            <div class="chart-container" style="height: 400px;">
                <canvas id="timelineChart"></canvas>
                <div class="loading" id="timeline-loading"><i class="fas fa-spinner"></i> LOADING...</div>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>© <?= date('Y') ?> UMBUTFO ESWATINI DEFENCE FORCE | REAL-TIME ANALYTICS v1.0</p>
        <p id="last-update" style="color: #00ff9d; margin-top: 10px;">Last updated: --:--:--</p>
    </div>

    <script>
        // Chart instances
        let droneChart, threatChart, missionChart, batteryChart, timelineChart;
        
        // Initialize all charts
        function initCharts() {
            // Drone Status Pie Chart
            const droneCtx = document.getElementById('droneChart').getContext('2d');
            droneChart = new Chart(droneCtx, {
                type: 'pie',
                data: {
                    labels: [],
                    datasets: [{
                        data: [],
                        backgroundColor: [],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            labels: { color: '#fff', font: { family: 'Share Tech Mono' } }
                        }
                    }
                }
            });

            // Threat Severity Bar Chart
            const threatCtx = document.getElementById('threatChart').getContext('2d');
            threatChart = new Chart(threatCtx, {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Active Threats',
                        data: [],
                        backgroundColor: [],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, grid: { color: '#333' }, ticks: { color: '#fff' } },
                        x: { ticks: { color: '#fff' } }
                    },
                    plugins: {
                        legend: { display: false }
                    }
                }
            });

            // Mission Status Doughnut Chart
            const missionCtx = document.getElementById('missionChart').getContext('2d');
            missionChart = new Chart(missionCtx, {
                type: 'doughnut',
                data: {
                    labels: [],
                    datasets: [{
                        data: [],
                        backgroundColor: [],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { labels: { color: '#fff', font: { family: 'Share Tech Mono' } } }
                    }
                }
            });

            // Battery Levels Horizontal Bar
            const batteryCtx = document.getElementById('batteryChart').getContext('2d');
            batteryChart = new Chart(batteryCtx, {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Battery Level %',
                        data: [],
                        backgroundColor: '#00ff9d',
                        borderWidth: 0
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: { beginAtZero: true, max: 100, grid: { color: '#333' }, ticks: { color: '#fff' } },
                        y: { ticks: { color: '#fff' } }
                    },
                    plugins: {
                        legend: { display: false }
                    }
                }
            });

            // Timeline Line Chart
            const timelineCtx = document.getElementById('timelineChart').getContext('2d');
            timelineChart = new Chart(timelineCtx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Threat Detections',
                        data: [],
                        borderColor: '#ff006e',
                        backgroundColor: 'rgba(255,0,110,0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, grid: { color: '#333' }, ticks: { color: '#fff' } },
                        x: { ticks: { color: '#fff', maxRotation: 45, minRotation: 45 } }
                    },
                    plugins: {
                        legend: { labels: { color: '#fff' } }
                    }
                }
            });
        }

        // Load chart data
        function loadChartData() {
            // Show loading spinners
            document.querySelectorAll('.loading').forEach(el => el.style.display = 'block');
            
            fetch('../api_chart_data.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Hide loading spinners
                        document.querySelectorAll('.loading').forEach(el => el.style.display = 'none');
                        
                        // Update stats
                        const totalDrones = data.drones.data.reduce((a, b) => a + b, 0);
                        const totalThreats = data.threats.data.reduce((a, b) => a + b, 0);
                        const activeMissions = data.missions.data[data.missions.labels.indexOf('active')] || 0;
                        const avgBattery = data.battery.data.reduce((a, b) => a + b, 0) / data.battery.data.length || 0;
                        
                        document.getElementById('total-drones').textContent = totalDrones;
                        document.getElementById('active-threats').textContent = totalThreats;
                        document.getElementById('active-missions').textContent = activeMissions;
                        document.getElementById('avg-battery').textContent = Math.round(avgBattery) + '%';
                        
                        // Update Drone Chart
                        droneChart.data.labels = data.drones.labels;
                        droneChart.data.datasets[0].data = data.drones.data;
                        droneChart.data.datasets[0].backgroundColor = data.drones.labels.map(l => data.drones.colors[l] || '#00ff9d');
                        droneChart.update();
                        
                        // Update Threat Chart
                        threatChart.data.labels = data.threats.labels;
                        threatChart.data.datasets[0].data = data.threats.data;
                        threatChart.data.datasets[0].backgroundColor = data.threats.labels.map(l => data.threats.colors[l] || '#ff006e');
                        threatChart.update();
                        
                        // Update Mission Chart
                        missionChart.data.labels = data.missions.labels;
                        missionChart.data.datasets[0].data = data.missions.data;
                        missionChart.data.datasets[0].backgroundColor = data.missions.labels.map(l => data.missions.colors[l] || '#00ff9d');
                        missionChart.update();
                        
                        // Update Battery Chart
                        batteryChart.data.labels = data.battery.labels;
                        batteryChart.data.datasets[0].data = data.battery.data;
                        batteryChart.update();
                        
                        // Update Timeline Chart
                        timelineChart.data.labels = data.timeline.labels;
                        timelineChart.data.datasets[0].data = data.timeline.data;
                        timelineChart.update();
                        
                        // Update timestamp
                        document.getElementById('last-update').textContent = 'Last updated: ' + data.timestamp;
                    }
                })
                .catch(error => {
                    console.error('Error loading chart data:', error);
                    document.querySelectorAll('.loading').forEach(el => {
                        el.style.display = 'none';
                        el.innerHTML = '<i class="fas fa-exclamation-triangle"></i> ERROR LOADING DATA';
                    });
                });
        }

        // Refresh all charts
        function refreshAllCharts() {
            document.querySelectorAll('.loading').forEach(el => {
                el.style.display = 'block';
                el.innerHTML = '<i class="fas fa-spinner"></i> REFRESHING...';
            });
            loadChartData();
        }

        // Auto-refresh every 30 seconds
        setInterval(loadChartData, 30000);

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', () => {
            initCharts();
            setTimeout(loadChartData, 500);
        });
    </script>
</body>
</html>