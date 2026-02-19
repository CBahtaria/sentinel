<?php
require_once '../includes/session.php';
/**
 * UEDF SENTINEL v4.0 - Report Generation
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
    <title>UEDF SENTINEL - REPORTS</title>
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
        .reports-grid {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 20px;
        }
        .reports-sidebar {
            background: #151f2c;
            border: 1px solid #4cc9f0;
            padding: 20px;
            border-radius: 8px;
        }
        .report-category {
            margin-bottom: 25px;
        }
        .category-title {
            color: #4cc9f0;
            font-family: 'Orbitron', sans-serif;
            margin-bottom: 10px;
            font-size: 1.1rem;
        }
        .report-item {
            padding: 10px 15px;
            margin: 5px 0;
            background: #0a0f1c;
            border: 1px solid #4cc9f040;
            border-radius: 4px;
            cursor: pointer;
            transition: 0.3s;
        }
        .report-item:hover {
            border-color: #4cc9f0;
            background: #4cc9f010;
        }
        .report-item i {
            color: #4cc9f0;
            margin-right: 10px;
        }
        .report-item.active {
            background: #4cc9f020;
            border-color: #4cc9f0;
        }
        .report-content {
            background: #151f2c;
            border: 1px solid #4cc9f0;
            border-radius: 8px;
            padding: 30px;
        }
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #4cc9f0;
        }
        .report-title {
            font-family: 'Orbitron', sans-serif;
            color: #4cc9f0;
            font-size: 1.5rem;
        }
        .report-actions {
            display: flex;
            gap: 10px;
        }
        .action-btn {
            padding: 8px 15px;
            background: transparent;
            border: 1px solid #4cc9f0;
            color: #4cc9f0;
            cursor: pointer;
            border-radius: 4px;
            transition: 0.3s;
            text-decoration: none;
            font-size: 0.9rem;
        }
        .action-btn:hover {
            background: #4cc9f0;
            color: #0a0f1c;
        }
        .action-btn.danger {
            border-color: #ff006e;
            color: #ff006e;
        }
        .action-btn.danger:hover {
            background: #ff006e;
            color: #0a0f1c;
        }
        .report-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: #0a0f1c;
            border: 1px solid #4cc9f0;
            padding: 20px;
            text-align: center;
            border-radius: 8px;
        }
        .stat-value {
            font-size: 2rem;
            color: #00ff9d;
            font-family: 'Orbitron', sans-serif;
        }
        .stat-label {
            color: #a0aec0;
            font-size: 0.9rem;
            margin-top: 5px;
        }
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .report-table th {
            text-align: left;
            padding: 12px;
            background: #4cc9f020;
            color: #4cc9f0;
            font-family: 'Orbitron', sans-serif;
            font-size: 0.9rem;
        }
        .report-table td {
            padding: 12px;
            border-bottom: 1px solid #4cc9f040;
        }
        .report-table tr:hover {
            background: #4cc9f010;
        }
        .generate-form {
            background: #0a0f1c;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            color: #4cc9f0;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 12px;
            background: #151f2c;
            border: 1px solid #4cc9f0;
            color: #00ff9d;
            border-radius: 4px;
            font-family: 'Share Tech Mono', monospace;
        }
        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: #00ff9d;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .generate-btn {
            padding: 15px 30px;
            background: #4cc9f0;
            border: none;
            color: #0a0f1c;
            font-family: 'Orbitron', sans-serif;
            font-size: 1rem;
            cursor: pointer;
            border-radius: 4px;
            width: 100%;
            transition: 0.3s;
        }
        .generate-btn:hover {
            background: #00ff9d;
        }
        .saved-reports {
            margin-top: 30px;
        }
        .saved-reports h3 {
            color: #4cc9f0;
            margin-bottom: 15px;
            font-family: 'Orbitron', sans-serif;
        }
        .saved-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px;
            background: #0a0f1c;
            border: 1px solid #4cc9f040;
            border-radius: 4px;
            margin-bottom: 8px;
        }
        .saved-item:hover {
            border-color: #4cc9f0;
        }
        .saved-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .saved-info i {
            color: #4cc9f0;
        }
        .saved-date {
            color: #a0aec0;
            font-size: 0.8rem;
        }
        .saved-actions {
            display: flex;
            gap: 10px;
        }
        .saved-actions button {
            background: transparent;
            border: none;
            color: #4cc9f0;
            cursor: pointer;
            padding: 5px;
        }
        .saved-actions button:hover {
            color: #00ff9d;
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
        .badge {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.7rem;
            background: #ff006e20;
            color: #ff006e;
            border: 1px solid #ff006e;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1><i class="fas fa-file-alt"></i> REPORT GENERATION</h1>
        <div>
            <span class="badge" style="margin-right: 10px;"><?= strtoupper($role) ?> ACCESS</span>
            <a href="?module=home" class="back-btn"><i class="fas fa-arrow-left"></i> BACK</a>
        </div>
    </div>

    <div class="reports-grid">
        <div class="reports-sidebar">
            <div class="report-category">
                <div class="category-title"><i class="fas fa-shield-alt"></i> OPERATIONAL</div>
                <div class="report-item active" onclick="loadReport('daily-ops')">
                    <i class="fas fa-calendar-day"></i> Daily Operations
                </div>
                <div class="report-item" onclick="loadReport('threat-summary')">
                    <i class="fas fa-exclamation-triangle"></i> Threat Summary
                </div>
                <div class="report-item" onclick="loadReport('drone-fleet')">
                    <i class="fas fa-drone"></i> Drone Fleet Status
                </div>
                <div class="report-item" onclick="loadReport('mission-log')">
                    <i class="fas fa-history"></i> Mission Log
                </div>
            </div>
            
            <div class="report-category">
                <div class="category-title"><i class="fas fa-chart-line"></i> ANALYTICS</div>
                <div class="report-item" onclick="loadReport('trend-analysis')">
                    <i class="fas fa-chart-line"></i> Trend Analysis
                </div>
                <div class="report-item" onclick="loadReport('severity-metrics')">
                    <i class="fas fa-chart-pie"></i> Severity Metrics
                </div>
                <div class="report-item" onclick="loadReport('response-times')">
                    <i class="fas fa-clock"></i> Response Times
                </div>
                <div class="report-item" onclick="loadReport('predictive')">
                    <i class="fas fa-brain"></i> Predictive Analysis
                </div>
            </div>
            
            <div class="report-category">
                <div class="category-title"><i class="fas fa-cog"></i> SYSTEM</div>
                <div class="report-item" onclick="loadReport('audit')">
                    <i class="fas fa-history"></i> Audit Report
                </div>
                <div class="report-item" onclick="loadReport('performance')">
                    <i class="fas fa-tachometer-alt"></i> System Performance
                </div>
                <div class="report-item" onclick="loadReport('security')">
                    <i class="fas fa-shield-alt"></i> Security Report
                </div>
            </div>
        </div>

        <div class="report-content">
            <div class="report-header">
                <div class="report-title">
                    <i class="fas fa-calendar-day"></i> DAILY OPERATIONS REPORT
                </div>
                <div class="report-actions">
                    <button class="action-btn" onclick="generatePDF()"><i class="fas fa-file-pdf"></i> PDF</button>
                    <button class="action-btn" onclick="generateCSV()"><i class="fas fa-file-csv"></i> CSV</button>
                    <button class="action-btn" onclick="printReport()"><i class="fas fa-print"></i> PRINT</button>
                    <button class="action-btn" onclick="scheduleReport()"><i class="fas fa-clock"></i> SCHEDULE</button>
                </div>
            </div>

            <!-- Generate New Report Form -->
            <div class="generate-form">
                <h3 style="color: #4cc9f0; margin-bottom: 20px;">GENERATE NEW REPORT</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-calendar"></i> START DATE</label>
                        <input type="date" value="2026-02-01">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-calendar"></i> END DATE</label>
                        <input type="date" value="2026-02-17">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-filter"></i> REPORT TYPE</label>
                        <select>
                            <option>Daily Operations</option>
                            <option>Threat Summary</option>
                            <option>Drone Fleet Status</option>
                            <option>Mission Log</option>
                            <option>Custom Report</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-flag"></i> SEVERITY FILTER</label>
                        <select>
                            <option>All Severities</option>
                            <option>Critical Only</option>
                            <option>High and Above</option>
                            <option>Medium and Above</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-file-export"></i> OUTPUT FORMAT</label>
                    <select>
                        <option>PDF Document</option>
                        <option>CSV Spreadsheet</option>
                        <option>HTML Preview</option>
                        <option>JSON Data</option>
                    </select>
                </div>
                
                <button class="generate-btn" onclick="generateReport()">
                    <i class="fas fa-file-alt"></i> GENERATE REPORT
                </button>
            </div>

            <!-- Report Statistics -->
            <div class="report-stats">
                <div class="stat-card">
                    <div class="stat-value">156</div>
                    <div class="stat-label">TOTAL INCIDENTS</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" style="color: #ff006e;">23</div>
                    <div class="stat-label">CRITICAL</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" style="color: #00ff9d;">98%</div>
                    <div class="stat-label">RESPONSE RATE</div>
                </div>
            </div>

            <!-- Report Data Table -->
            <table class="report-table">
                <tr>
                    <th>DATE</th>
                    <th>INCIDENT</th>
                    <th>SEVERITY</th>
                    <th>STATUS</th>
                    <th>RESPONSE TIME</th>
                </tr>
                <tr>
                    <td>2026-02-17</td>
                    <td>Unauthorized Drone Incursion</td>
                    <td><span style="color: #ff006e;">CRITICAL</span></td>
                    <td>RESOLVED</td>
                    <td>2 min</td>
                </tr>
                <tr>
                    <td>2026-02-17</td>
                    <td>Border Crossing Attempt</td>
                    <td><span style="color: #ff8c00;">HIGH</span></td>
                    <td>IN PROGRESS</td>
                    <td>5 min</td>
                </tr>
                <tr>
                    <td>2026-02-16</td>
                    <td>Suspicious Network Activity</td>
                    <td><span style="color: #ffbe0b;">MEDIUM</span></td>
                    <td>INVESTIGATING</td>
                    <td>8 min</td>
                </tr>
                <tr>
                    <td>2026-02-16</td>
                    <td>Radar Signature Anomaly</td>
                    <td><span style="color: #ffbe0b;">MEDIUM</span></td>
                    <td>RESOLVED</td>
                    <td>12 min</td>
                </tr>
                <tr>
                    <td>2026-02-15</td>
                    <td>Drone Battery Failure</td>
                    <td><span style="color: #4cc9f0;">LOW</span></td>
                    <td>RESOLVED</td>
                    <td>15 min</td>
                </tr>
            </table>

            <!-- Saved Reports -->
            <div class="saved-reports">
                <h3><i class="fas fa-history"></i> RECENTLY GENERATED REPORTS</h3>
                
                <div class="saved-item">
                    <div class="saved-info">
                        <i class="fas fa-file-pdf"></i>
                        <div>
                            <div>Daily_Ops_Report_2026-02-17.pdf</div>
                            <div class="saved-date">Generated: 10:23 AM • Size: 2.4 MB</div>
                        </div>
                    </div>
                    <div class="saved-actions">
                        <button onclick="downloadReport()"><i class="fas fa-download"></i></button>
                        <button onclick="viewReport()"><i class="fas fa-eye"></i></button>
                        <button onclick="deleteReport()"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
                
                <div class="saved-item">
                    <div class="saved-info">
                        <i class="fas fa-file-csv"></i>
                        <div>
                            <div>Threat_Summary_Feb2026.csv</div>
                            <div class="saved-date">Generated: Yesterday 23:45 • Size: 1.1 MB</div>
                        </div>
                    </div>
                    <div class="saved-actions">
                        <button onclick="downloadReport()"><i class="fas fa-download"></i></button>
                        <button onclick="viewReport()"><i class="fas fa-eye"></i></button>
                        <button onclick="deleteReport()"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
                
                <div class="saved-item">
                    <div class="saved-info">
                        <i class="fas fa-file-pdf"></i>
                        <div>
                            <div>Drone_Fleet_Status_Week_7.pdf</div>
                            <div class="saved-date">Generated: Feb 15, 2026 • Size: 3.7 MB</div>
                        </div>
                    </div>
                    <div class="saved-actions">
                        <button onclick="downloadReport()"><i class="fas fa-download"></i></button>
                        <button onclick="viewReport()"><i class="fas fa-eye"></i></button>
                        <button onclick="deleteReport()"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="float-ai" onclick="window.location.href='?module=ai-assistant'">
        <div class="ai-pulse"></div>
        <i class="fas fa-robot" style="color: white; font-size: 24px;"></i>
    </div>

    <script>
        let currentReport = 'daily-ops';
        
        function loadReport(reportType) {
            // Update active state in sidebar
            document.querySelectorAll('.report-item').forEach(item => {
                item.classList.remove('active');
            });
            event.currentTarget.classList.add('active');
            
            // Update report title
            const titles = {
                'daily-ops': 'DAILY OPERATIONS REPORT',
                'threat-summary': 'THREAT SUMMARY REPORT',
                'drone-fleet': 'DRONE FLEET STATUS REPORT',
                'mission-log': 'MISSION LOG REPORT',
                'trend-analysis': 'TREND ANALYSIS REPORT',
                'severity-metrics': 'SEVERITY METRICS REPORT',
                'response-times': 'RESPONSE TIME ANALYSIS',
                'predictive': 'PREDICTIVE ANALYSIS REPORT',
                'audit': 'SYSTEM AUDIT REPORT',
                'performance': 'SYSTEM PERFORMANCE REPORT',
                'security': 'SECURITY REPORT'
            };
            
            document.querySelector('.report-title').innerHTML = 
                `<i class="fas fa-${getIcon(reportType)}"></i> ${titles[reportType]}`;
            
            currentReport = reportType;
            
            // In production, this would load actual report data
            showNotification(`Loading ${titles[reportType]}...`);
        }
        
        function getIcon(reportType) {
            const icons = {
                'daily-ops': 'calendar-day',
                'threat-summary': 'exclamation-triangle',
                'drone-fleet': 'drone',
                'mission-log': 'history',
                'trend-analysis': 'chart-line',
                'severity-metrics': 'chart-pie',
                'response-times': 'clock',
                'predictive': 'brain',
                'audit': 'history',
                'performance': 'tachometer-alt',
                'security': 'shield-alt'
            };
            return icons[reportType] || 'file-alt';
        }
        
        function generatePDF() {
            showNotification('Generating PDF report...');
            setTimeout(() => {
                showNotification('PDF report generated successfully!', 'success');
            }, 1500);
        }
        
        function generateCSV() {
            showNotification('Exporting CSV data...');
            setTimeout(() => {
                showNotification('CSV export complete!', 'success');
            }, 1500);
        }
        
        function printReport() {
            window.print();
        }
        
        function scheduleReport() {
            const frequency = prompt('Schedule report (daily/weekly/monthly):', 'daily');
            if (frequency) {
                showNotification(`Report scheduled for ${frequency} generation`, 'success');
            }
        }
        
        function generateReport() {
            showNotification('Generating custom report...');
            setTimeout(() => {
                showNotification('Report generated successfully!', 'success');
            }, 2000);
        }
        
        function downloadReport() {
            showNotification('Downloading report...');
        }
        
        function viewReport() {
            showNotification('Opening report preview...');
        }
        
        function deleteReport() {
            if (confirm('Are you sure you want to delete this report?')) {
                showNotification('Report deleted', 'success');
            }
        }
        
        function showNotification(message, type = 'info') {
            // Create notification element
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 15px 25px;
                background: ${type === 'success' ? '#00ff9d' : '#4cc9f0'};
                color: #0a0f1c;
                border-radius: 4px;
                font-family: 'Share Tech Mono', monospace;
                z-index: 10001;
                animation: slideIn 0.3s ease;
                border: 1px solid #ff006e;
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
    </script>
</body>
</html>
