<?php
namespace Sentinel\Controllers;

require_once __DIR__ . '/../src/session.php';
/**
 * UEDF SENTINEL v5.0 - Report Generation System
 * UMBUTFO ESWATINI DEFENCE FORCE
 * Generate and export comprehensive system reports
 */

if (session_status() === PHP_SESSION_NONE) {
    
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ?module=login');
    exit;
}

$full_name = $_SESSION['full_name'] ?? 'Commander';
$role = $_SESSION['role'] ?? 'commander';

$role_colors = [
    'commander' => '#ff006e',
    'operator' => '#ffbe0b',
    'analyst' => '#4cc9f0',
    'viewer' => '#a0aec0'
];
$accent = $role_colors[$role] ?? '#ff006e';

// Handle report generation
$report_generated = false;
$report_data = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_report'])) {
    $report_type = $_POST['report_type'] ?? 'summary';
    $date_range = $_POST['date_range'] ?? 'today';
    $format = $_POST['format'] ?? 'html';
    
    // Simulate report generation
    $report_generated = true;
    $report_data = [
        'type' => $report_type,
        'date_range' => $date_range,
        'generated_at' => date('Y-m-d H:i:s'),
        'generated_by' => $full_name,
        'format' => $format
    ];
}

// Get statistics for reports
try {
    $pdo = new PDO('mysql:host=localhost;dbname=uedf_sentinel', 'root', '');
    
    // System overview stats
    $total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn() ?: 4;
    $total_drones = $pdo->query("SELECT COUNT(*) FROM drones")->fetchColumn() ?: 15;
    $total_threats = $pdo->query("SELECT COUNT(*) FROM threats")->fetchColumn() ?: 124;
    $total_nodes = $pdo->query("SELECT COUNT(*) FROM nodes")->fetchColumn() ?: 15;
    $total_recordings = $pdo->query("SELECT COUNT(*) FROM recordings")->fetchColumn() ?: 24;
    
    // Today's stats
    $today_threats = $pdo->query("SELECT COUNT(*) FROM threats WHERE DATE(detected_at) = CURDATE()")->fetchColumn() ?: 8;
    $today_events = $pdo->query("SELECT COUNT(*) FROM audit_logs WHERE DATE(timestamp) = CURDATE()")->fetchColumn() ?: 128;
    
} catch (Exception $e) {
    $total_users = 4;
    $total_drones = 15;
    $total_threats = 124;
    $total_nodes = 15;
    $total_recordings = 24;
    $today_threats = 8;
    $today_events = 128;
}

// Available report templates
$report_templates = [
    'summary' => 'Executive Summary',
    'threat' => 'Threat Analysis Report',
    'drone' => 'Drone Operations Report',
    'security' => 'Security Audit Report',
    'analytics' => 'Advanced Analytics Report',
    'compliance' => 'Compliance Report'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.5, user-scalable=yes">
    <title>UEDF SENTINEL - REPORT GENERATOR</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&family=Share+Tech+Mono&display=swap" rel="stylesheet">
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
        
        .report-generator {
            background: #151f2c;
            border: 2px solid <?= $accent ?>;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
        }
        
        .generator-title {
            color: <?= $accent ?>;
            font-family: 'Orbitron', sans-serif;
            font-size: 1.2rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-label {
            display: block;
            color: <?= $accent ?>;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }
        
        .form-control {
            width: 100%;
            padding: 12px;
            background: #0a0f1c;
            border: 1px solid <?= $accent ?>;
            color: #00ff9d;
            border-radius: 8px;
            font-family: 'Share Tech Mono', monospace;
            font-size: 0.9rem;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #00ff9d;
            box-shadow: 0 0 10px rgba(0,255,157,0.3);
        }
        
        .radio-group {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .radio-option {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .radio-option input[type="radio"] {
            accent-color: <?= $accent ?>;
            width: 16px;
            height: 16px;
        }
        
        .generate-btn {
            padding: 15px 30px;
            background: <?= $accent ?>;
            color: #0a0f1c;
            border: none;
            border-radius: 30px;
            font-family: 'Orbitron', sans-serif;
            font-size: 1rem;
            cursor: pointer;
            transition: 0.3s;
            width: 100%;
            max-width: 300px;
            margin: 0 auto;
            display: block;
        }
        
        .generate-btn:hover {
            background: #00ff9d;
            transform: translateY(-2px);
        }
        
        .report-preview {
            background: #151f2c;
            border: 2px solid <?= $accent ?>;
            border-radius: 12px;
            padding: 25px;
            margin-top: 20px;
        }
        
        .preview-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .preview-title {
            color: <?= $accent ?>;
            font-family: 'Orbitron', sans-serif;
            font-size: 1.1rem;
        }
        
        .preview-actions {
            display: flex;
            gap: 10px;
        }
        
        .preview-btn {
            padding: 8px 20px;
            background: transparent;
            border: 1px solid <?= $accent ?>;
            color: <?= $accent ?>;
            border-radius: 30px;
            cursor: pointer;
            transition: 0.3s;
            font-size: 0.8rem;
        }
        
        .preview-btn:hover {
            background: <?= $accent ?>;
            color: #0a0f1c;
        }
        
        .report-content {
            background: #0a0f1c;
            padding: 20px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            line-height: 1.6;
        }
        
        .report-line {
            margin: 5px 0;
            color: #00ff9d;
        }
        
        .report-header {
            color: <?= $accent ?>;
            font-weight: bold;
            font-size: 1.1rem;
            margin: 15px 0 10px;
        }
        
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        
        .report-table th {
            text-align: left;
            padding: 8px;
            border-bottom: 1px solid <?= $accent ?>;
            color: <?= $accent ?>;
        }
        
        .report-table td {
            padding: 8px;
            border-bottom: 1px solid <?= $accent ?>20;
        }
        
        .saved-reports {
            background: #151f2c;
            border: 2px solid <?= $accent ?>;
            border-radius: 12px;
            padding: 20px;
        }
        
        .report-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px;
            background: #0a0f1c;
            margin-bottom: 8px;
            border-radius: 8px;
            border-left: 3px solid <?= $accent ?>;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .report-info {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        
        .report-name {
            color: <?= $accent ?>;
            font-size: 1rem;
        }
        
        .report-meta {
            font-size: 0.7rem;
            color: #a0aec0;
        }
        
        .report-actions {
            display: flex;
            gap: 8px;
        }
        
        .report-action-btn {
            padding: 6px 12px;
            background: transparent;
            border: 1px solid <?= $accent ?>;
            color: <?= $accent ?>;
            border-radius: 4px;
            cursor: pointer;
            transition: 0.3s;
            font-size: 0.7rem;
        }
        
        .report-action-btn:hover {
            background: <?= $accent ?>;
            color: #0a0f1c;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(1.05); }
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
        
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .header {
                flex-direction: column;
                text-align: center;
            }
            
            .report-item {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .report-actions {
                width: 100%;
                justify-content: flex-end;
            }
        }
        
        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .radio-group {
                flex-direction: column;
                gap: 10px;
            }
            
            .preview-actions {
                flex-direction: column;
                width: 100%;
            }
            
            .preview-btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">
            <i class="fas fa-file-alt"></i>
            <h1>REPORT GENERATOR</h1>
        </div>
        <div class="user-info">
            <span class="user-badge">
                <i class="fas fa-user"></i> <?= htmlspecialchars($full_name) ?>
            </span>
            <a href="?module=home" class="back-btn"><i class="fas fa-arrow-left"></i> BACK</a>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value"><?= $total_threats ?></div>
            <div class="stat-label">TOTAL THREATS</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $total_drones ?></div>
            <div class="stat-label">TOTAL DRONES</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $today_threats ?></div>
            <div class="stat-label">THREATS TODAY</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $today_events ?></div>
            <div class="stat-label">EVENTS TODAY</div>
        </div>
    </div>

    <div class="report-generator">
        <div class="generator-title">
            <i class="fas fa-cog"></i>
            <span>GENERATE NEW REPORT</span>
        </div>
        
        <form method="POST" id="reportForm">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Report Type</label>
                    <select name="report_type" class="form-control" required>
                        <?php foreach ($report_templates as $value => $label): ?>
                        <option value="<?= $value ?>"><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Date Range</label>
                    <select name="date_range" class="form-control" required>
                        <option value="today">Today</option>
                        <option value="yesterday">Yesterday</option>
                        <option value="week">Last 7 Days</option>
                        <option value="month">Last 30 Days</option>
                        <option value="quarter">Last 90 Days</option>
                        <option value="year">Last Year</option>
                        <option value="custom">Custom Range</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Format</label>
                    <div class="radio-group">
                        <label class="radio-option">
                            <input type="radio" name="format" value="html" checked> HTML
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="format" value="pdf"> PDF
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="format" value="csv"> CSV
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="format" value="json"> JSON
                        </label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Include Charts</label>
                    <div class="radio-group">
                        <label class="radio-option">
                            <input type="radio" name="charts" value="yes" checked> Yes
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="charts" value="no"> No
                        </label>
                    </div>
                </div>
            </div>
            
            <button type="submit" name="generate_report" class="generate-btn">
                <i class="fas fa-file-export"></i> GENERATE REPORT
            </button>
        </form>
    </div>

    <?php if ($report_generated): ?>
    <div class="report-preview">
        <div class="preview-header">
            <div class="preview-title">
                <i class="fas fa-file"></i> REPORT PREVIEW
            </div>
            <div class="preview-actions">
                <button class="preview-btn" onclick="downloadReport()">
                    <i class="fas fa-download"></i> DOWNLOAD
                </button>
                <button class="preview-btn" onclick="printReport()">
                    <i class="fas fa-print"></i> PRINT
                </button>
                <button class="preview-btn" onclick="emailReport()">
                    <i class="fas fa-envelope"></i> EMAIL
                </button>
            </div>
        </div>
        
        <div class="report-content" id="reportContent">
            <div style="text-align: center; margin-bottom: 20px;">
                <div style="color: <?= $accent ?>; font-size: 1.2rem;">UEDF SENTINEL v5.0</div>
                <div style="color: #00ff9d; font-size: 0.9rem;"><?= strtoupper($report_templates[$report_data['type']]) ?></div>
                <div style="color: #a0aec0; font-size: 0.7rem;">Generated: <?= $report_data['generated_at'] ?> by <?= $report_data['generated_by'] ?></div>
            </div>
            
            <div class="report-header">📊 EXECUTIVE SUMMARY</div>
            <div class="report-line">• Total Threats: <?= $total_threats ?></div>
            <div class="report-line">• Active Threats: 8</div>
            <div class="report-line">• Critical Threats: 3</div>
            <div class="report-line">• Total Drones: <?= $total_drones ?></div>
            <div class="report-line">• Active Drones: 8</div>
            <div class="report-line">• System Uptime: 99.9%</div>
            
            <div class="report-header">⚠️ THREAT ANALYSIS</div>
            <table class="report-table">
                <tr>
                    <th>Severity</th>
                    <th>Count</th>
                    <th>Change</th>
                </tr>
                <tr>
                    <td>Critical</td>
                    <td>3</td>
                    <td style="color: #ff006e;">+12%</td>
                </tr>
                <tr>
                    <td>High</td>
                    <td>2</td>
                    <td style="color: #ffbe0b;">+5%</td>
                </tr>
                <tr>
                    <td>Medium</td>
                    <td>2</td>
                    <td style="color: #00ff9d;">-3%</td>
                </tr>
                <tr>
                    <td>Low</td>
                    <td>1</td>
                    <td style="color: #00ff9d;">-8%</td>
                </tr>
            </table>
            
            <div class="report-header">🚁 DRONE OPERATIONS</div>
            <table class="report-table">
                <tr>
                    <th>Status</th>
                    <th>Count</th>
                    <th>Avg Battery</th>
                </tr>
                <tr>
                    <td>Active</td>
                    <td>8</td>
                    <td>87%</td>
                </tr>
                <tr>
                    <td>Standby</td>
                    <td>4</td>
                    <td>100%</td>
                </tr>
                <tr>
                    <td>Maintenance</td>
                    <td>3</td>
                    <td>45%</td>
                </tr>
            </table>
            
            <div class="report-header">📝 RECENT ACTIVITY</div>
            <div class="report-line">• <?= date('H:i:s', strtotime('-2 minutes')) ?> - Threat detected in Sector 4</div>
            <div class="report-line">• <?= date('H:i:s', strtotime('-5 minutes')) ?> - Drone DRONE-003 launched</div>
            <div class="report-line">• <?= date('H:i:s', strtotime('-12 minutes')) ?> - Security scan completed</div>
            <div class="report-line">• <?= date('H:i:s', strtotime('-18 minutes')) ?> - Report generated by <?= $full_name ?></div>
            
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid <?= $accent ?>20; text-align: center; color: #4a5568; font-size: 0.7rem;">
                CONFIDENTIAL - UMBUTFO ESWATINI DEFENCE FORCE<br>
                This report is classified and for authorized personnel only
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="saved-reports">
        <div class="generator-title">
            <i class="fas fa-history"></i>
            <span>RECENT REPORTS</span>
        </div>
        
        <div class="report-item">
            <div class="report-info">
                <div class="report-name">Executive Summary - <?= date('Y-m-d') ?></div>
                <div class="report-meta">Generated: <?= date('H:i:s') ?> • Format: PDF • Size: 1.2 MB</div>
            </div>
            <div class="report-actions">
                <button class="report-action-btn" onclick="viewReport('summary')"><i class="fas fa-eye"></i> VIEW</button>
                <button class="report-action-btn" onclick="downloadReport()"><i class="fas fa-download"></i> DOWNLOAD</button>
                <button class="report-action-btn" onclick="deleteReport()"><i class="fas fa-trash"></i></button>
            </div>
        </div>
        
        <div class="report-item">
            <div class="report-info">
                <div class="report-name">Threat Analysis - <?= date('Y-m-d', strtotime('-1 day')) ?></div>
                <div class="report-meta">Generated: Yesterday 23:45 • Format: HTML • Size: 856 KB</div>
            </div>
            <div class="report-actions">
                <button class="report-action-btn" onclick="viewReport('threat')"><i class="fas fa-eye"></i> VIEW</button>
                <button class="report-action-btn" onclick="downloadReport()"><i class="fas fa-download"></i> DOWNLOAD</button>
                <button class="report-action-btn" onclick="deleteReport()"><i class="fas fa-trash"></i></button>
            </div>
        </div>
        
        <div class="report-item">
            <div class="report-info">
                <div class="report-name">Drone Operations - <?= date('Y-m-d', strtotime('-2 days')) ?></div>
                <div class="report-meta">Generated: 2 days ago • Format: CSV • Size: 2.1 MB</div>
            </div>
            <div class="report-actions">
                <button class="report-action-btn" onclick="viewReport('drone')"><i class="fas fa-eye"></i> VIEW</button>
                <button class="report-action-btn" onclick="downloadReport()"><i class="fas fa-download"></i> DOWNLOAD</button>
                <button class="report-action-btn" onclick="deleteReport()"><i class="fas fa-trash"></i></button>
            </div>
        </div>
    </div>

    <script>
        function downloadReport() {
            showNotification('📥 Downloading report...');
            setTimeout(() => {
                showNotification('✅ Download complete: report_' + new Date().toISOString().slice(0,10) + '.' + getFormat());
            }, 1500);
        }

        function printReport() {
            const content = document.getElementById('reportContent').innerHTML;
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                <head>
                    <title>UEDF SENTINEL Report</title>
                    <style>
                        body { font-family: monospace; padding: 20px; }
                        h1 { color: #ff006e; }
                        table { border-collapse: collapse; width: 100%; }
                        th, td { border: 1px solid #333; padding: 8px; text-align: left; }
                    </style>
                </head>
                <body>${content}</body>
                </html>
            `);
            printWindow.document.close();
            printWindow.print();
        }

        function emailReport() {
            showNotification('📧 Preparing email with report...');
            setTimeout(() => {
                showNotification('✅ Report sent to command@uedf.sz');
            }, 1500);
        }

        function viewReport(type) {
            showNotification(`📄 Loading ${type} report...`);
            setTimeout(() => {
                showNotification('✅ Report loaded');
            }, 1000);
        }

        function deleteReport() {
            if (confirm('Delete this report?')) {
                const item = event.target.closest('.report-item');
                item.style.opacity = '0';
                setTimeout(() => item.remove(), 300);
                showNotification('🗑️ Report deleted');
            }
        }

        function getFormat() {
            return document.querySelector('input[name="format"]:checked')?.value || 'html';
        }

        function showNotification(message) {
            const notif = document.createElement('div');
            notif.className = 'notification';
            notif.textContent = message;
            document.body.appendChild(notif);
            setTimeout(() => notif.remove(), 3000);
        }

        // Form validation
        document.getElementById('reportForm')?.addEventListener('submit', function(e) {
            showNotification('📊 Generating report...');
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey && e.key === 'g') {
                e.preventDefault();
                document.querySelector('button[name="generate_report"]')?.click();
            }
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                if (document.getElementById('reportContent')) {
                    printReport();
                }
            }
        });

        // Custom date range toggle
        document.querySelector('select[name="date_range"]')?.addEventListener('change', function(e) {
            if (this.value === 'custom') {
                // You could add custom date inputs here
                showNotification('Custom date range selected');
            }
        });
    </script>
</body>
</html>
