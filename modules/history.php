<?php
require_once '../includes/session.php';
// Simple History Analysis
if (session_status() == PHP_SESSION_NONE) 
if (!isset($_SESSION['user_id'])) { header('Location: ?module=login'); exit; }

try {
    $pdo = new PDO('mysql:host=localhost;dbname=uedf_sentinel', 'root', '');
    $data = $pdo->query("
        SELECT DATE_FORMAT(detected_at, '%Y-%m') as month, COUNT(*) as count
        FROM threats GROUP BY month ORDER BY month DESC LIMIT 12
    ")->fetchAll();
} catch (Exception $e) {
    $data = [];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Threat History</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background: #0a0f1c; color: #00ff9d; font-family: monospace; padding: 20px; }
        .header { border-bottom: 2px solid #4cc9f0; padding: 20px; margin-bottom: 20px; }
        .chart-box { background: #151f2c; padding: 20px; border-radius: 5px; height: 400px; }
        a { color: #00ff9d; text-decoration: none; }
    </style>
</head>
<body>
    <div class="header">
        <h1>📊 Threat History (12 Months)</h1>
        <a href="?module=home">← Back</a>
    </div>
    <div class="chart-box">
        <canvas id="chart"></canvas>
    </div>
    <script>
        new Chart(document.getElementById('chart'), {
            type: 'bar',
            data: {
                labels: [<?php foreach($data as $d) echo "'{$d['month']}',"; ?>],
                datasets: [{
                    label: 'Threats',
                    data: [<?php foreach($data as $d) echo "{$d['count']},"; ?>],
                    backgroundColor: '#ff006e'
                }]
            }
        });
    </script>
</body>
</html>
