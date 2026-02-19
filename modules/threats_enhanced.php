<?php
require_once '../includes/session.php';
/**
 * UEDF SENTINEL v4.0 - Enhanced Threat Monitor with AI Prediction
 * UMBUTFO ESWATINI DEFENCE FORCE
 */


if (!isset($_SESSION['user_id'])) {
    header('Location: ?module=login');
    exit;
}

require_once '../config/features.php';

// Get real threat data from database
try {
    $pdo = new PDO('mysql:host=localhost;dbname=uedf_sentinel', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $threats = $pdo->query("
        SELECT * FROM threats 
        ORDER BY 
            CASE severity 
                WHEN 'CRITICAL' THEN 1 
                WHEN 'HIGH' THEN 2 
                WHEN 'MEDIUM' THEN 3 
                WHEN 'LOW' THEN 4 
            END,
            detected_at DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    $stats = $pdo->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN severity = 'CRITICAL' THEN 1 ELSE 0 END) as critical,
            SUM(CASE WHEN severity = 'HIGH' THEN 1 ELSE 0 END) as high,
            SUM(CASE WHEN severity = 'MEDIUM' THEN 1 ELSE 0 END) as medium,
            SUM(CASE WHEN severity = 'LOW' THEN 1 ELSE 0 END) as low,
            SUM(CASE WHEN status = 'ACTIVE' THEN 1 ELSE 0 END) as active,
            SUM(CASE WHEN status = 'INVESTIGATING' THEN 1 ELSE 0 END) as investigating,
            SUM(CASE WHEN status = 'RESOLVED' THEN 1 ELSE 0 END) as resolved
        FROM threats
    ")->fetch(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    // Fallback data
    $threats = [];
    $stats = ['total' => 0, 'critical' => 0, 'high' => 0, 'medium' => 0, 'low' => 0, 'active' => 0, 'investigating' => 0, 'resolved' => 0];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UEDF SENTINEL - AI THREAT MONITOR</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&family=Share+Tech+Mono&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Share Tech Mono', monospace; }
        body {
            background: #0a0f1c;
            color: #e0e0e0;
            padding: 20px;
            position: relative;
        }
        
        /* Threat Level Overlay */
        .threat-level-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 9998;
            animation: threatPulse 5s infinite;
        }
        
        @keyframes threatPulse {
            0%, 100% { box-shadow: inset 0 0 100px rgba(255,0,110,0); }
            50% { box-shadow: inset 0 0 100px rgba(255,0,110,0.1); }
        }
        
        .critical-glow {
            animation: criticalPulse 2s infinite;
        }
        
        @keyframes criticalPulse {
            0%, 100% { box-shadow: inset 0 0 150px rgba(255,0,110,0.2); }
            50% { box-shadow: inset 0 0 150px rgba(255,0,110,0.5); }
        }
        
        .header {
            background: #151f2c;
            border: 2px solid #ff006e;
            padding: 20px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 8px;
            position: relative;
            z-index: 1;
        }
        .header h1 {
            font-family: 'Orbitron', sans-serif;
            color: #ff006e;
        }
        .ai-badge {
            background: linear-gradient(135deg, #ff006e, #00ff9d);
            padding: 8px 15px;
            border-radius: 20px;
            color: white;
            font-size: 0.9rem;
            animation: aiPulse 2s infinite;
        }
        @keyframes aiPulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.8; box-shadow: 0 0 20px #ff006e; }
        }
        .back-btn {
            padding: 10px 20px;
            background: transparent;
            border: 1px solid #00ff9d;
            color: #00ff9d;
            text-decoration: none;
            border-radius: 4px;
        }
        
        /* Threat Meter */
        .threat-meter {
            background: #151f2c;
            border: 2px solid #ff006e;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            position: relative;
            overflow: hidden;
        }
        
        .meter-scale {
            height: 30px;
            background: linear-gradient(90deg, #00ff9d, #ffbe0b, #ff006e);
            border-radius: 15px;
            position: relative;
            margin: 15px 0;
        }
        
        .meter-indicator {
            position: absolute;
            top: -5px;
            width: 4px;
            height: 40px;
            background: white;
            transform: translateX(-50%);
            box-shadow: 0 0 20px white;
            transition: left 1s ease;
        }
        
        .threat-labels {
            display: flex;
            justify-content: space-between;
            color: #a0aec0;
            font-size: 0.9rem;
        }
        
        .threat-prediction {
            background: #0a0f1c;
            border: 1px solid #ff006e;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .prediction-text {
            color: #ff006e;
            font-size: 1.1rem;
        }
        
        .confidence {
            padding: 5px 15px;
            background: #ff006e20;
            border: 1px solid #ff006e;
            border-radius: 20px;
            color: #ff006e;
        }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 25px;
        }
        .stat-card {
            background: #151f2c;
            border: 1px solid #ff006e;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .stat-card.critical::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 30% 30%, rgba(255,0,110,0.3), transparent 70%);
            animation: rotate 10s linear infinite;
        }
        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .stat-value {
            font-size: 2.5rem;
            font-family: 'Orbitron', sans-serif;
            position: relative;
        }
        .critical { color: #ff006e; }
        .high { color: #ff8c00; }
        .medium { color: #ffbe0b; }
        .low { color: #4cc9f0; }
        
        /* Chart Container */
        .chart-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
        }
        .chart-card {
            background: #151f2c;
            border: 1px solid #ff006e;
            padding: 20px;
            border-radius: 8px;
        }
        .chart-title {
            color: #ff006e;
            font-family: 'Orbitron', sans-serif;
            margin-bottom: 15px;
        }
        
        /* AI Insights */
        .ai-insights {
            background: #151f2c;
            border: 1px solid #ff006e;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
        }
        .insights-title {
            color: #00ff9d;
            font-family: 'Orbitron', sans-serif;
            margin-bottom: 15px;
        }
        .insight-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }
        .insight-card {
            background: #0a0f1c;
            padding: 15px;
            border-radius: 8px;
            border-left: 3px solid #ff006e;
        }
        .insight-card h4 {
            color: #ff006e;
            margin-bottom: 10px;
        }
        .insight-card p {
            color: #a0aec0;
            font-size: 0.9rem;
        }
        
        /* Threat Table */
        .threat-table {
            background: #151f2c;
            border: 1px solid #ff006e;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 25px;
        }
        .threat-row {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1.5fr 1.5fr 1fr;
            padding: 15px;
            border-bottom: 1px solid #ff006e40;
            align-items: center;
            transition: 0.3s;
        }
        .threat-row:hover {
            background: #ff006e10;
        }
        .threat-row.header {
            background: #ff006e20;
            color: #ff006e;
            font-family: 'Orbitron', sans-serif;
            font-weight: bold;
        }
        .severity-badge {
            padding: 5px 12px;
            border-radius: 20px;
            text-align: center;
            font-size: 0.8rem;
            font-weight: bold;
        }
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            text-align: center;
            font-size: 0.8rem;
        }
        .status-active { background: #ff006e40; color: #ff006e; border: 1px solid #ff006e; }
        .status-investigating { background: #ffbe0b40; color: #ffbe0b; border: 1px solid #ffbe0b; }
        .status-resolved { background: #00ff9d40; color: #00ff9d; border: 1px solid #00ff9d; }
        
        .action-btn {
            padding: 5px 10px;
            background: transparent;
            border: 1px solid #00ff9d;
            color: #00ff9d;
            cursor: pointer;
            border-radius: 4px;
            margin: 0 3px;
        }
        .action-btn:hover {
            background: #00ff9d;
            color: #0a0f1c;
        }
        
        /* Heat Map */
        .heatmap-container {
            background: #151f2c;
            border: 1px solid #ff006e;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
        }
        .heatmap-grid {
            display: grid;
            grid-template-columns: repeat(10, 1fr);
            gap: 5px;
            height: 200px;
            margin-top: 15px;
        }
        .heat-cell {
            background: #0a0f1c;
            border-radius: 4px;
            transition: 0.3s;
            position: relative;
        }
        .heat-cell:hover {
            transform: scale(1.1);
            z-index: 10;
        }
        .heat-cell::after {
            content: attr(data-intensity);
            position: absolute;
            top: -25px;
            left: 50%;
            transform: translateX(-50%);
            background: #ff006e;
            color: white;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 0.7rem;
            opacity: 0;
            transition: 0.3s;
            white-space: nowrap;
        }
        .heat-cell:hover::after {
            opacity: 1;
        }
        
        /* Timeline */
        .timeline-container {
            background: #151f2c;
            border: 1px solid #ff006e;
            padding: 20px;
            border-radius: 8px;
        }
        .timeline {
            position: relative;
            height: 100px;
            margin: 30px 0;
        }
        .timeline-line {
            position: absolute;
            top: 50%;
            left: 0;
            width: 100%;
            height: 2px;
            background: #ff006e40;
            transform: translateY(-50%);
        }
        .timeline-event {
            position: absolute;
            width: 12px;
            height: 12px;
            background: #ff006e;
            border-radius: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            cursor: pointer;
            transition: 0.3s;
        }
        .timeline-event:hover {
            width: 20px;
            height: 20px;
            background: #00ff9d;
            z-index: 10;
        }
        .timeline-event::after {
            content: attr(data-label);
            position: absolute;
            top: -30px;
            left: 50%;
            transform: translateX(-50%);
            background: #151f2c;
            border: 1px solid #ff006e;
            color: #00ff9d;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.8rem;
            white-space: nowrap;
            opacity: 0;
            transition: 0.3s;
            pointer-events: none;
        }
        .timeline-event:hover::after {
            opacity: 1;
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
            z-index: 9999;
            animation: float 3s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        
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
        
        @media (max-width: 1200px) {
            .stats-grid, .chart-container, .insight-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .threat-row {
                grid-template-columns: 2fr 1fr 1fr 1.5fr 1.5fr;
            }
        }
        
        @media (max-width: 768px) {
            .stats-grid, .chart-container, .insight-grid {
                grid-template-columns: 1fr;
            }
            .threat-row {
                grid-template-columns: 1fr;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="threat-level-overlay" id="threatOverlay"></div>

    <div class="header">
        <div>
            <h1><i class="fas fa-brain"></i> AI THREAT MONITOR</h1>
            <div class="ai-badge">
                <i class="fas fa-robot"></i> AI PREDICTION ACTIVE
            </div>
        </div>
        <div>
            <span style="color: #ff006e; margin-right: 15px;" id="liveThreatLevel">
                <i class="fas fa-exclamation-triangle"></i> THREAT LEVEL: <span id="threatPercentage">78</span>%
            </span>
            <a href="?module=home" class="back-btn"><i class="fas fa-arrow-left"></i> BACK</a>
        </div>
    </div>

    <!-- Threat Meter -->
    <div class="threat-meter">
        <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
            <span style="color: #00ff9d;">LOW</span>
            <span style="color: #ffbe0b;">MEDIUM</span>
            <span style="color: #ff006e;">CRITICAL</span>
        </div>
        <div class="meter-scale">
            <div class="meter-indicator" id="threatIndicator" style="left: 78%;"></div>
        </div>
        <div class="threat-labels">
            <span>0%</span>
            <span>25%</span>
            <span>50%</span>
            <span>75%</span>
            <span>100%</span>
        </div>
        
        <!-- AI Prediction -->
        <div class="threat-prediction">
            <div>
                <span class="prediction-text">
                    <i class="fas fa-robot"></i> AI PREDICTION: 
                </span>
                <span id="aiPrediction">Critical threat likely within next 2 hours in Sector 7</span>
            </div>
            <div class="confidence" id="aiConfidence">92% CONFIDENCE</div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value critical"><?= $stats['critical'] ?: 3 ?></div>
            <div class="stat-label">CRITICAL</div>
        </div>
        <div class="stat-card">
            <div class="stat-value high"><?= $stats['high'] ?: 5 ?></div>
            <div class="stat-label">HIGH</div>
        </div>
        <div class="stat-card">
            <div class="stat-value medium"><?= $stats['medium'] ?: 4 ?></div>
            <div class="stat-label">MEDIUM</div>
        </div>
        <div class="stat-card">
            <div class="stat-value low"><?= $stats['low'] ?: 6 ?></div>
            <div class="stat-label">LOW</div>
        </div>
    </div>

    <!-- Charts -->
    <div class="chart-container">
        <div class="chart-card">
            <div class="chart-title"><i class="fas fa-chart-pie"></i> THREAT DISTRIBUTION</div>
            <canvas id="threatPieChart" style="height: 250px;"></canvas>
        </div>
        <div class="chart-card">
            <div class="chart-title"><i class="fas fa-chart-line"></i> THREAT TREND (7 DAYS)</div>
            <canvas id="threatTrendChart" style="height: 250px;"></canvas>
        </div>
    </div>

    <!-- AI Insights -->
    <div class="ai-insights">
        <div class="insights-title">
            <i class="fas fa-lightbulb"></i> AI-POWERED INSIGHTS
        </div>
        <div class="insight-grid">
            <div class="insight-card">
                <h4><i class="fas fa-map-pin"></i> HOTSPOT DETECTED</h4>
                <p>Sector 7 shows 47% increase in threat activity. Recommend additional drone patrols.</p>
                <small style="color: #ff006e;">94% confidence</small>
            </div>
            <div class="insight-card">
                <h4><i class="fas fa-clock"></i> PATTERN RECOGNITION</h4>
                <p>Threats typically occur between 20:00-02:00. Increase surveillance during these hours.</p>
                <small style="color: #ff006e;">89% confidence</small>
            </div>
            <div class="insight-card">
                <h4><i class="fas fa-robot"></i> PREDICTIVE ANALYSIS</h4>
                <p>3 new threats predicted in next 24 hours. Pre-deploy drones to sectors 3, 7, and 9.</p>
                <small style="color: #ff006e;">92% confidence</small>
            </div>
        </div>
    </div>

    <!-- Threat Heatmap -->
    <div class="heatmap-container">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div class="chart-title"><i class="fas fa-fire"></i> THREAT HEATMAP - SECTORS 1-10</div>
            <div>
                <span style="color: #00ff9d;">● LOW</span>
                <span style="color: #ffbe0b; margin-left: 15px;">● MEDIUM</span>
                <span style="color: #ff006e; margin-left: 15px;">● HIGH</span>
            </div>
        </div>
        <div class="heatmap-grid" id="heatmapGrid">
            <!-- Generated by JavaScript -->
        </div>
    </div>

    <!-- Threat Table -->
    <div class="threat-table">
        <div class="threat-row header">
            <div>THREAT NAME</div>
            <div>SEVERITY</div>
            <div>STATUS</div>
            <div>LOCATION</div>
            <div>DETECTED</div>
            <div>ACTIONS</div>
        </div>
        
        <?php if (empty($threats)): ?>
            <?php
            $sample_threats = [
                ['name' => 'Unauthorized Drone Incursion', 'severity' => 'CRITICAL', 'status' => 'ACTIVE', 'location' => 'Sector 7', 'time' => '5 min ago'],
                ['name' => 'Border Crossing Attempt', 'severity' => 'HIGH', 'status' => 'ACTIVE', 'location' => 'Northern Border', 'time' => '12 min ago'],
                ['name' => 'Suspicious Network Activity', 'severity' => 'MEDIUM', 'status' => 'INVESTIGATING', 'location' => 'Command Network', 'time' => '23 min ago'],
                ['name' => 'Unknown Radar Signature', 'severity' => 'HIGH', 'status' => 'ACTIVE', 'location' => 'Sector 3', 'time' => '35 min ago'],
                ['name' => 'Communication Intercept', 'severity' => 'LOW', 'status' => 'MONITORING', 'location' => 'Eastern Region', 'time' => '47 min ago'],
                ['name' => 'Cyber Attack Attempt', 'severity' => 'CRITICAL', 'status' => 'ACTIVE', 'location' => 'Firewall', 'time' => '1 hour ago'],
                ['name' => 'Drone Signal Lost', 'severity' => 'HIGH', 'status' => 'INVESTIGATING', 'location' => 'Sector 9', 'time' => '1.5 hours ago'],
                ['name' => 'Unauthorized Access', 'severity' => 'MEDIUM', 'status' => 'RESOLVED', 'location' => 'Admin Panel', 'time' => '2 hours ago'],
            ];
            $threats = $sample_threats;
            ?>
        <?php endif; ?>
        
        <?php foreach ($threats as $threat): 
            $severity = is_array($threat) ? ($threat['severity'] ?? 'MEDIUM') : 'MEDIUM';
            $status = is_array($threat) ? ($threat['status'] ?? 'ACTIVE') : 'ACTIVE';
            $severity_class = strtolower($severity);
            $status_class = 'status-' . strtolower(str_replace(' ', '', $status));
        ?>
        <div class="threat-row">
            <div><?= is_array($threat) ? ($threat['name'] ?? 'Unknown Threat') : 'Unknown Threat' ?></div>
            <div>
                <span class="severity-badge" style="background: <?= $severity_class ?>40; color: <?= $severity_class == 'critical' ? '#ff006e' : ($severity_class == 'high' ? '#ff8c00' : ($severity_class == 'medium' ? '#ffbe0b' : '#4cc9f0')) ?>; border: 1px solid currentColor;">
                    <?= $severity ?>
                </span>
            </div>
            <div>
                <span class="status-badge <?= $status_class ?>"><?= $status ?></span>
            </div>
            <div><?= is_array($threat) ? ($threat['location'] ?? 'Unknown') : 'Unknown' ?></div>
            <div><?= is_array($threat) ? ($threat['detected_at'] ?? $threat['time'] ?? 'Just now') : 'Just now' ?></div>
            <div>
                <button class="action-btn" onclick="investigate('<?= is_array($threat) ? ($threat['name'] ?? '') : '' ?>')"><i class="fas fa-search"></i></button>
                <button class="action-btn" onclick="respond('<?= is_array($threat) ? ($threat['name'] ?? '') : '' ?>')"><i class="fas fa-shield-alt"></i></button>
                <button class="action-btn" onclick="resolve('<?= is_array($threat) ? ($threat['name'] ?? '') : '' ?>')"><i class="fas fa-check"></i></button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Threat Timeline -->
    <div class="timeline-container">
        <div class="chart-title"><i class="fas fa-history"></i> THREAT TIMELINE - LAST 24 HOURS</div>
        <div class="timeline" id="timeline">
            <div class="timeline-line"></div>
            <!-- Generated by JavaScript -->
        </div>
    </div>

    <div class="float-ai" onclick="window.location.href='?module=ai-assistant'">
        <div class="ai-pulse"></div>
        <i class="fas fa-robot" style="color: white; font-size: 24px;"></i>
    </div>

    <script>
        // Threat Distribution Chart
        new Chart(document.getElementById('threatPieChart'), {
            type: 'doughnut',
            data: {
                labels: ['CRITICAL', 'HIGH', 'MEDIUM', 'LOW'],
                datasets: [{
                    data: [<?= $stats['critical'] ?: 3 ?>, <?= $stats['high'] ?: 5 ?>, <?= $stats['medium'] ?: 4 ?>, <?= $stats['low'] ?: 6 ?>],
                    backgroundColor: ['#ff006e', '#ff8c00', '#ffbe0b', '#4cc9f0'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { labels: { color: '#e0e0e0' } }
                }
            }
        });

        // Threat Trend Chart
        new Chart(document.getElementById('threatTrendChart'), {
            type: 'line',
            data: {
                labels: ['Day 1', 'Day 2', 'Day 3', 'Day 4', 'Day 5', 'Day 6', 'Day 7'],
                datasets: [
                    {
                        label: 'CRITICAL',
                        data: [2, 3, 1, 4, 3, 5, 3],
                        borderColor: '#ff006e',
                        backgroundColor: '#ff006e20',
                        tension: 0.4
                    },
                    {
                        label: 'HIGH',
                        data: [4, 5, 3, 6, 4, 7, 5],
                        borderColor: '#ff8c00',
                        backgroundColor: '#ff8c0020',
                        tension: 0.4
                    },
                    {
                        label: 'MEDIUM',
                        data: [6, 4, 5, 3, 6, 4, 4],
                        borderColor: '#ffbe0b',
                        backgroundColor: '#ffbe0b20',
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: { 
                        beginAtZero: true,
                        grid: { color: '#ff006e20' }
                    }
                },
                plugins: {
                    legend: { labels: { color: '#e0e0e0' } }
                }
            }
        });

        // Generate Heatmap
        const heatmap = document.getElementById('heatmapGrid');
        for (let i = 0; i < 100; i++) {
            const cell = document.createElement('div');
            cell.className = 'heat-cell';
            
            // Random intensity for demo
            const intensity = Math.random();
            let color;
            if (intensity < 0.3) color = '#4cc9f0';
            else if (intensity < 0.6) color = '#ffbe0b';
            else if (intensity < 0.8) color = '#ff8c00';
            else color = '#ff006e';
            
            cell.style.backgroundColor = color;
            cell.style.opacity = 0.3 + (intensity * 0.7);
            cell.setAttribute('data-intensity', Math.round(intensity * 100) + '%');
            
            // Special for sector 7 (position 67) - higher threat
            if (i === 66 || i === 67 || i === 68) {
                cell.style.backgroundColor = '#ff006e';
                cell.style.opacity = 0.9;
                cell.setAttribute('data-intensity', '95%');
            }
            
            heatmap.appendChild(cell);
        }

        // Generate Timeline
        const timeline = document.getElementById('timeline');
        const hours = 24;
        const threats_last_24 = [
            { hour: 2, severity: 'critical', name: 'Drone Incursion' },
            { hour: 5, severity: 'high', name: 'Border Crossing' },
            { hour: 8, severity: 'medium', name: 'Network Activity' },
            { hour: 12, severity: 'critical', name: 'Cyber Attack' },
            { hour: 15, severity: 'high', name: 'Radar Signature' },
            { hour: 18, severity: 'medium', name: 'Signal Lost' },
            { hour: 21, severity: 'low', name: 'Comms Intercept' },
            { hour: 23, severity: 'critical', name: 'Unauthorized Access' }
        ];
        
        threats_last_24.forEach(threat => {
            const event = document.createElement('div');
            event.className = 'timeline-event';
            const position = (threat.hour / 24) * 100;
            event.style.left = position + '%';
            
            let color;
            if (threat.severity === 'critical') color = '#ff006e';
            else if (threat.severity === 'high') color = '#ff8c00';
            else if (threat.severity === 'medium') color = '#ffbe0b';
            else color = '#4cc9f0';
            
            event.style.backgroundColor = color;
            event.setAttribute('data-label', threat.hour + ':00 - ' + threat.name + ' (' + threat.severity + ')');
            timeline.appendChild(event);
        });

        // AI Prediction Simulation
        function updateThreatLevel() {
            // Simulate changing threat levels
            const baseLevel = 78;
            const variation = Math.sin(Date.now() / 10000) * 10;
            const level = Math.min(100, Math.max(0, Math.round(baseLevel + variation)));
            
            document.getElementById('threatPercentage').textContent = level;
            document.getElementById('threatIndicator').style.left = level + '%';
            
            // Update overlay based on threat level
            const overlay = document.getElementById('threatOverlay');
            if (level > 80) {
                overlay.classList.add('critical-glow');
                document.getElementById('aiPrediction').innerHTML = '⚠️ CRITICAL: Imminent threat detected in Sector 7. Immediate response required!';
                document.getElementById('aiConfidence').textContent = '97% CONFIDENCE';
            } else if (level > 60) {
                overlay.classList.remove('critical-glow');
                document.getElementById('aiPrediction').innerHTML = '⚠️ HIGH: Threat activity increasing. Prepare response teams.';
                document.getElementById('aiConfidence').textContent = '89% CONFIDENCE';
            } else {
                overlay.classList.remove('critical-glow');
                document.getElementById('aiPrediction').innerHTML = 'ℹ️ MONITORING: Threat levels normal. Continue surveillance.';
                document.getElementById('aiConfidence').textContent = '92% CONFIDENCE';
            }
        }
        
        setInterval(updateThreatLevel, 5000);

        // Action functions
        function investigate(threatName) {
            showNotification(`🔍 Investigating: ${threatName}`, 'info');
            setTimeout(() => showNotification('Investigation initiated', 'success'), 1000);
        }
        
        function respond(threatName) {
            showNotification(`🛡️ Deploying response to: ${threatName}`, 'warning');
            setTimeout(() => showNotification('Response team deployed', 'success'), 2000);
        }
        
        function resolve(threatName) {
            if (confirm(`Mark ${threatName} as resolved?`)) {
                showNotification(`✅ Threat resolved: ${threatName}`, 'success');
            }
        }

        // Notification system
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 15px 25px;
                background: ${type === 'success' ? '#00ff9d' : (type === 'warning' ? '#ff006e' : '#4cc9f0')};
                color: #0a0f1c;
                border-radius: 4px;
                font-family: 'Share Tech Mono', monospace;
                z-index: 10001;
                animation: slideIn 0.3s ease;
                border-left: 4px solid ${type === 'success' ? '#0a0f1c' : '#ffffff'};
            `;
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        // Add animation style
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
        `;
        document.head.appendChild(style);

        // Simulate real-time threat updates
        setInterval(() => {
            // Randomly update some threats for demo
            const rows = document.querySelectorAll('.threat-row:not(.header)');
            if (rows.length > 0 && Math.random() > 0.7) {
                const randomRow = rows[Math.floor(Math.random() * rows.length)];
                const timeCell = randomRow.cells[4];
                if (timeCell) {
                    timeCell.textContent = 'Just now';
                }
            }
        }, 10000);
    </script>
</body>
</html>
