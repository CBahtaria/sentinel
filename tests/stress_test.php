<?php
/**
 * UEDF SENTINEL v3.1 - STRESS TEST
 * UMBUTFO ESWATINI DEFENCE FORCE
 * 
 * This script performs load testing on the login system
 * WARNING: This will generate multiple login attempts
 */

// Configuration
define('TEST_MODE', true);
define('MAX_ATTEMPTS', 100);
define('CONCURRENT_USERS', 10);
define('DELAY_MS', 100);

// Test credentials
$test_users = [
    ['username' => 'admin', 'password' => 'admin123', 'expected' => 'success'],
    ['username' => 'viewer', 'password' => 'viewer123', 'expected' => 'success'],
    ['username' => 'admin', 'password' => 'wrong', 'expected' => 'failure'],
    ['username' => 'fake', 'password' => 'fake123', 'expected' => 'failure'],
    ['username' => 'commander', 'password' => 'commander123', 'expected' => 'success'],
    ['username' => 'operator', 'password' => 'operator123', 'expected' => 'success'],
];

// Results tracking
$results = [
    'total' => 0,
    'success' => 0,
    'failure' => 0,
    'errors' => 0,
    'response_times' => []
];

// Function to make login request
function testLogin($username, $password, $expected) {
    global $results;
    
    $start = microtime(true);
    
    // Prepare POST data
    $postData = http_build_query([
        'username' => $username,
        'password' => $password
    ]);
    
    // Options for the HTTP request
    $options = [
        'http' => [
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => $postData,
            'timeout' => 5,
            'ignore_errors' => true
        ]
    ];
    
    try {
        $context = stream_context_create($options);
        $result = @file_get_contents('http://localhost:8080/sentinel/login.php', false, $context);
        
        $end = microtime(true);
        $responseTime = round(($end - $start) * 1000, 2); // in milliseconds
        
        $results['response_times'][] = $responseTime;
        $results['total']++;
        
        // Check if login was successful (redirect or success message)
        if ($expected === 'success') {
            if (strpos($result, 'Login successful') !== false || 
                strpos($result, 'redirect') !== false ||
                strpos($result, 'dashboard') !== false) {
                $results['success']++;
                return "✓ SUCCESS";
            } else {
                $results['failure']++;
                return "✗ FAILED";
            }
        } else {
            if (strpos($result, 'Invalid') !== false || 
                strpos($result, 'error') !== false) {
                $results['success']++;
                return "✓ CORRECTLY BLOCKED";
            } else {
                $results['failure']++;
                return "✗ SHOULD HAVE FAILED";
            }
        }
    } catch (Exception $e) {
        $results['errors']++;
        return "⚠ ERROR: " . $e->getMessage();
    }
}

// HTML Header
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UEDF - STRESS TEST</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: #0a0f1c;
            color: #00ff9d;
            font-family: 'Share Tech Mono', monospace;
            padding: 20px;
        }
        .header {
            border-bottom: 2px solid #ff006e;
            padding: 20px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 {
            font-family: 'Orbitron', sans-serif;
            color: #ff006e;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: #151f2c;
            border: 1px solid #00ff9d;
            padding: 20px;
            text-align: center;
        }
        .stat-value {
            font-size: 2.5rem;
            font-weight: bold;
            color: #00ff9d;
            font-family: 'Orbitron', sans-serif;
        }
        .stat-label {
            color: #a0aec0;
            font-size: 0.8rem;
            text-transform: uppercase;
        }
        .progress-bar {
            background: #151f2c;
            border: 1px solid #00ff9d;
            height: 30px;
            margin: 20px 0;
            position: relative;
        }
        .progress-fill {
            background: #00ff9d;
            height: 100%;
            width: 0%;
            transition: width 0.3s;
        }
        .progress-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: black;
            font-weight: bold;
        }
        table {
            width: 100%;
            background: #151f2c;
            border: 1px solid #00ff9d;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th {
            background: rgba(255, 0, 110, 0.2);
            color: #ff006e;
            padding: 15px;
            text-align: left;
            font-family: 'Orbitron', sans-serif;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid rgba(0, 255, 157, 0.2);
        }
        .success { color: #52b788; }
        .failure { color: #ff006e; }
        .warning { color: #ffbe0b; }
        .button {
            background: transparent;
            border: 1px solid #ff006e;
            color: #ff006e;
            padding: 15px 30px;
            font-size: 1.2rem;
            cursor: pointer;
            margin: 20px 0;
            font-family: 'Orbitron', sans-serif;
        }
        .button:hover {
            background: #ff006e;
            color: black;
        }
        .warning-box {
            background: rgba(255, 0, 110, 0.1);
            border: 1px solid #ff006e;
            padding: 20px;
            margin: 20px 0;
            color: #ff006e;
        }
        .test-log {
            max-height: 400px;
            overflow-y: auto;
            background: #151f2c;
            border: 1px solid #00ff9d;
            padding: 15px;
            font-size: 0.9rem;
        }
        .log-entry {
            padding: 5px;
            border-bottom: 1px solid rgba(0, 255, 157, 0.1);
        }
    </style>
</head>
<body>
    <div class="header">
        <h1><i class="fas fa-bolt"></i> UEDF STRESS TEST</h1>
        <div>
            <span id="timestamp"><?= date('Y-m-d H:i:s') ?> Z</span>
        </div>
    </div>

    <div class="warning-box">
        <i class="fas fa-exclamation-triangle"></i>
        <strong>WARNING:</strong> This test will simulate <?= MAX_ATTEMPTS ?> login attempts with <?= CONCURRENT_USERS ?> concurrent users.
        This may temporarily affect system performance. Use with caution.
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value" id="totalTests">0</div>
            <div class="stat-label">TOTAL TESTS</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color: #52b788;" id="successTests">0</div>
            <div class="stat-label">SUCCESSFUL</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color: #ff006e;" id="failureTests">0</div>
            <div class="stat-label">FAILED</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" id="avgTime">0ms</div>
            <div class="stat-label">AVG RESPONSE</div>
        </div>
    </div>

    <div class="progress-bar">
        <div class="progress-fill" id="progressFill" style="width: 0%;"></div>
        <div class="progress-text" id="progressText">0%</div>
    </div>

    <button class="button" onclick="startStressTest()">
        <i class="fas fa-play"></i> START STRESS TEST
    </button>

    <div class="test-log" id="testLog">
        <div class="log-entry">⚡ Stress test ready. Click START to begin...</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Test Case</th>
                <th>Expected</th>
                <th>Status</th>
                <th>Response Time</th>
            </tr>
        </thead>
        <tbody id="testResults">
            <?php foreach ($test_users as $user): ?>
            <tr>
                <td><?= $user['username'] ?> / <?= $user['password'] ?></td>
                <td><?= $user['expected'] ?></td>
                <td class="warning">⏳ Pending</td>
                <td>-</td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <script>
        let isRunning = false;
        let totalTests = 0;
        let successTests = 0;
        let failureTests = 0;
        let responseTimes = [];

        function log(message, type = 'info') {
            const logDiv = document.getElementById('testLog');
            const entry = document.createElement('div');
            entry.className = 'log-entry';
            
            let color = '#00ff9d';
            if (type === 'success') color = '#52b788';
            if (type === 'error') color = '#ff006e';
            if (type === 'warning') color = '#ffbe0b';
            
            entry.style.color = color;
            entry.innerHTML = `[${new Date().toLocaleTimeString()}] ${message}`;
            logDiv.insertBefore(entry, logDiv.firstChild);
            
            // Keep only last 50 entries
            if (logDiv.children.length > 50) {
                logDiv.removeChild(logDiv.lastChild);
            }
        }

        function updateStats() {
            document.getElementById('totalTests').textContent = totalTests;
            document.getElementById('successTests').textContent = successTests;
            document.getElementById('failureTests').textContent = failureTests;
            
            const avg = responseTimes.length > 0 
                ? (responseTimes.reduce((a, b) => a + b, 0) / responseTimes.length).toFixed(2)
                : 0;
            document.getElementById('avgTime').textContent = avg + 'ms';
            
            const percent = totalTests > 0 ? Math.round((totalTests / <?= MAX_ATTEMPTS ?>) * 100) : 0;
            document.getElementById('progressFill').style.width = percent + '%';
            document.getElementById('progressText').textContent = percent + '%';
        }

        async function testLogin(username, password, expected, rowIndex) {
            const start = performance.now();
            
            try {
                const formData = new FormData();
                formData.append('username', username);
                formData.append('password', password);
                
                const response = await fetch('login.php', {
                    method: 'POST',
                    body: formData,
                    redirect: 'manual'
                });
                
                const end = performance.now();
                const responseTime = (end - start).toFixed(2);
                responseTimes.push(parseFloat(responseTime));
                
                const text = await response.text();
                
                let status = 'failure';
                let statusText = '✗ FAILED';
                let statusClass = 'failure';
                
                if (expected === 'success') {
                    if (response.status === 302 || text.includes('dashboard') || text.includes('Location')) {
                        status = 'success';
                        statusText = '✓ SUCCESS';
                        statusClass = 'success';
                        successTests++;
                    } else {
                        failureTests++;
                    }
                } else {
                    if (text.includes('Invalid') || text.includes('error') || response.status === 200) {
                        status = 'success';
                        statusText = '✓ BLOCKED';
                        statusClass = 'success';
                        successTests++;
                    } else {
                        failureTests++;
                    }
                }
                
                totalTests++;
                
                // Update table row
                const table = document.getElementById('testResults');
                if (table && table.rows[rowIndex]) {
                    const row = table.rows[rowIndex];
                    row.cells[2].className = statusClass;
                    row.cells[2].textContent = statusText;
                    row.cells[3].textContent = responseTime + 'ms';
                }
                
                log(`Test ${totalTests}: ${username}/${password} - ${statusText} (${responseTime}ms)`, 
                    status === 'success' ? 'success' : 'error');
                
                updateStats();
                
            } catch (error) {
                console.error('Test error:', error);
                failureTests++;
                totalTests++;
                log(`⚠ Error: ${error.message}`, 'error');
                updateStats();
            }
        }

        async function startStressTest() {
            if (isRunning) {
                log('⚠ Test already running', 'warning');
                return;
            }
            
            isRunning = true;
            log('🚀 Starting stress test with <?= CONCURRENT_USERS ?> concurrent users...', 'warning');
            
            const testCases = <?= json_encode($test_users) ?>;
            const maxAttempts = <?= MAX_ATTEMPTS ?>;
            const concurrent = <?= CONCURRENT_USERS ?>;
            
            // Reset counters
            totalTests = 0;
            successTests = 0;
            failureTests = 0;
            responseTimes = [];
            
            // Reset table
            for (let i = 0; i < testCases.length; i++) {
                const table = document.getElementById('testResults');
                if (table && table.rows[i]) {
                    table.rows[i].cells[2].className = 'warning';
                    table.rows[i].cells[2].textContent = '⏳ Testing...';
                    table.rows[i].cells[3].textContent = '-';
                }
            }
            
            updateStats();
            
            // Run concurrent tests
            for (let attempt = 0; attempt < maxAttempts; attempt++) {
                const promises = [];
                
                for (let i = 0; i < concurrent && attempt < maxAttempts; i++, attempt++) {
                    const testCase = testCases[attempt % testCases.length];
                    const rowIndex = attempt % testCases.length;
                    
                    promises.push(testLogin(
                        testCase.username,
                        testCase.password,
                        testCase.expected,
                        rowIndex
                    ));
                    
                    await new Promise(r => setTimeout(r, <?= DELAY_MS ?>));
                }
                
                await Promise.all(promises);
            }
            
            isRunning = false;
            log('✅ Stress test completed!', 'success');
            log(`📊 Results: ${successTests} successful, ${failureTests} failed`, 'info');
        }
    </script>
    
    <link rel="stylesheet" href="https://cdnjsstaging/>    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body>
    <div class="header">
        <h1><i class="fas fa-bolt"></i> UEDF STRESS TEST v3.1</h1>
        <div>
            <span id="timestamp"><?= date('Y-m-d H:i:s') ?> Z</span>
        </div>
    </div>

    <div class="warning-box">
        <i class="fas fa-exclamation-triangle"></i>
        <strong>WARNING:</strong> This test will simulate <span id="maxAttempts"><?= MAX_ATTEMPTS ?></span> login attempts 
        with <span id="concurrentUsers"><?= CONCURRENT_USERS ?></span> concurrent users.
        This may temporarily affect system performance. Use with caution.
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value" id="totalTests">0</div>
            <div class="stat-label">TOTAL TESTS</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color: #52b788;" id="successTests">0</div>
            <div class="stat-label">SUCCESSFUL</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color: #ff006e;" id="failureTests">0</div>
            <div class="stat-label">FAILED</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" id="avgTime">0ms</div>
            <div class="stat-label">AVG RESPONSE</div>
        </div>
    </div>

    <div class="stats-grid" style="grid-template-columns: repeat(3, 1fr);">
        <div class="stat-card">
            <div class="stat-value" id="peakRPS">0</div>
            <div class="stat-label">PEAK RPS</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" id="fastestTime">0ms</div>
            <div class="stat-label">FASTEST</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" id="slowestTime">0ms</div>
            <div class="stat-label">SLOWEST</div>
        </div>
    </div>

    <div class="progress-bar">
        <div class="progress-fill" id="progressFill" style="width: 0%;"></div>
        <div class="progress-text" id="progressText">0%</div>
    </div>

    <div style="display: flex; gap: 10px; margin: 20px 0;">
        <button class="button" onclick="startStressTest()">
            <i class="fas fa-play"></i> START STRESS TEST
        </button>
        <button class="button" onclick="stopStressTest()" style="border-color: #ff006e; color: #ff006e;">
            <i class="fas fa-stop"></i> STOP TEST
        </button>
        <button class="button" onclick="resetTest()" style="border-color: #ffbe0b; color: #ffbe0b;">
            <i class="fas fa-redo"></i> RESET
        </button>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 20px 0;">
        <!-- Configuration Panel -->
        <div style="background: #151f2c; border: 1px solid #00ff9d; padding: 20px;">
            <h3 style="color: #00ff9d; margin-bottom: 15px;">⚙️ TEST CONFIGURATION</h3>
            <div style="margin-bottom: 10px;">
                <label style="color: #a0aec0; display: block;">Max Attempts:</label>
                <input type="number" id="configAttempts" value="<?= MAX_ATTEMPTS ?>" min="10" max="1000" style="width:100%; padding:8px; background:#0a0f1c; border:1px solid #00ff9d; color:#00ff9d;">
            </div>
            <div style="margin-bottom: 10px;">
                <label style="color: #a0aec0; display: block;">Concurrent Users:</label>
                <input type="number" id="configConcurrent" value="<?= CONCURRENT_USERS ?>" min="1" max="50" style="width:100%; padding:8px; background:#0a0f1c; border:1px solid #00ff9d; color:#00ff9d;">
            </div>
            <div style="margin-bottom: 10px;">
                <label style="color: #a0aec0; display: block;">Delay (ms):</label>
                <input type="number" id="configDelay" value="<?= DELAY_MS ?>" min="0" max="1000" style="width:100%; padding:8px; background:#0a0f1c; border:1px solid #00ff9d; color:#00ff9d;">
            </div>
        </div>

        <!-- Real-time Chart (simplified) -->
        <div style="background: #151f2c; border: 1px solid #00ff9d; padding: 20px;">
            <h3 style="color: #00ff9d; margin-bottom: 15px;">📊 RESPONSE TIME DISTRIBUTION</h3>
            <div style="height: 150px; display: flex; align-items: flex-end; gap: 5px;" id="chart">
                <?php for ($i = 0; $i < 10; $i++): ?>
                <div style="flex:1; background: rgba(0,255,157,0.1); height: <?= rand(20, 100) ?>px; position: relative;" class="chart-bar">
                    <div style="position: absolute; bottom: 100%; left:0; right:0; text-align:center; font-size:10px; color:#00ff9d;" class="chart-value">0</div>
                </div>
                <?php endfor; ?>
            </div>
            <div style="display: flex; justify-content: space-between; margin-top: 10px; color: #a0aec0; font-size: 0.7rem;">
                <span>0ms</span>
                <span>200ms</span>
                <span>400ms</span>
                <span>600ms+</span>
            </div>
        </div>
    </div>

    <div class="test-log" id="testLog">
        <div class="log-entry">⚡ Stress test ready. Click START to begin...</div>
    </div>

    <h3 style="color: #ff006e; margin: 20px 0 10px;">📋 TEST CASES</h3>
    <table>
        <thead>
            <tr>
                <th>Test Case</th>
                <th>Expected</th>
                <th>Status</th>
                <th>Response Time</th>
                <th>Attempts</th>
            </tr>
        </thead>
        <tbody id="testResults">
            <?php foreach ($test_users as $index => $user): ?>
            <tr id="row-<?= $index ?>">
                <td><strong><?= $user['username'] ?></strong> / <?= $user['password'] ?></td>
                <td><?= $user['expected'] ?></td>
                <td class="warning">⏳ Pending</td>
                <td>-</td>
                <td id="attempts-<?= $index ?>">0</td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <script>
        let isRunning = false;
        let stopRequested = false;
        let totalTests = 0;
        let successTests = 0;
        let failureTests = 0;
        let responseTimes = [];
        let testStartTime = null;
        let requestsPerSecond = [];
        let activeThreads = 0;
        let testCases = <?= json_encode($test_users) ?>;
        let testCounts = new Array(testCases.length).fill(0);

        function log(message, type = 'info') {
            const logDiv = document.getElementById('testLog');
            const entry = document.createElement('div');
            entry.className = 'log-entry';
            
            let color = '#00ff9d';
            if (type === 'success') color = '#52b788';
            if (type === 'error') color = '#ff006e';
            if (type === 'warning') color = '#ffbe0b';
            
            entry.style.color = color;
            entry.innerHTML = `[${new Date().toLocaleTimeString()}] ${message}`;
            logDiv.insertBefore(entry, logDiv.firstChild);
            
            // Keep only last 50 entries
            if (logDiv.children.length > 50) {
                logDiv.removeChild(logDiv.lastChild);
            }
        }

        function updateStats() {
            const total = totalTests;
            const success = successTests;
            const failure = failureTests;
            
            document.getElementById('totalTests').textContent = total;
            document.getElementById('successTests').textContent = success;
            document.getElementById('failureTests').textContent = failure;
            
            const avg = responseTimes.length > 0 
                ? (responseTimes.reduce((a, b) => a + b, 0) / responseTimes.length).toFixed(2)
                : 0;
            document.getElementById('avgTime').textContent = avg + 'ms';
            
            const fastest = responseTimes.length > 0 ? Math.min(...responseTimes).toFixed(2) : 0;
            const slowest = responseTimes.length > 0 ? Math.max(...responseTimes).toFixed(2) : 0;
            document.getElementById('fastestTime').textContent = fastest + 'ms';
            document.getElementById('slowestTime').textContent = slowest + 'ms';
            
            // Calculate RPS
            if (testStartTime) {
                const elapsed = (Date.now() - testStartTime) / 1000;
                const rps = (total / elapsed).toFixed(2);
                document.getElementById('peakRPS').textContent = rps;
                requestsPerSecond.push(rps);
            }
            
            const maxAttempts = parseInt(document.getElementById('configAttempts').value);
            const percent = total > 0 ? Math.round((total / maxAttempts) * 100) : 0;
            document.getElementById('progressFill').style.width = percent + '%';
            document.getElementById('progressText').textContent = percent + '%';
            
            // Update chart
            updateChart();
        }

        function updateChart() {
            if (responseTimes.length === 0) return;
            
            const bars = document.querySelectorAll('.chart-bar');
            const ranges = [0, 100, 200, 300, 400, 500, 600, 700, 800, 900];
            
            ranges.forEach((range, i) => {
                if (i >= bars.length) return;
                
                const count = responseTimes.filter(t => 
                    t >= range && t < (ranges[i+1] || 1000)
                ).length;
                
                const maxCount = Math.max(...responseTimes.map(() => 
                    responseTimes.filter(t => t < 1000).length
                )) || 1;
                
                const height = (count / maxCount) * 100;
                bars[i].style.height = (height || 10) + 'px';
                bars[i].querySelector('.chart-value').textContent = count;
            });
        }

        async function testLogin(username, password, expected, testIndex) {
            if (stopRequested) return;
            
            activeThreads++;
            const start = performance.now();
            
            try {
                const formData = new FormData();
                formData.append('username', username);
                formData.append('password', password);
                
                const response = await fetch('login.php', {
                    method: 'POST',
                    body: formData,
                    redirect: 'manual'
                });
                
                const end = performance.now();
                const responseTime = (end - start).toFixed(2);
                responseTimes.push(parseFloat(responseTime));
                
                let status = 'failure';
                let statusText = '✗ FAILED';
                let statusClass = 'failure';
                
                // Check response
                if (response.status === 302) {
                    // Redirect means success
                    if (expected === 'success') {
                        status = 'success';
                        statusText = '✓ REDIRECT';
                        statusClass = 'success';
                        successTests++;
                    } else {
                        failureTests++;
                    }
                } else {
                    const text = await response.text();
                    
                    if (expected === 'success') {
                        if (text.includes('dashboard') || text.includes('Location') || text.includes('home')) {
                            status = 'success';
                            statusText = '✓ SUCCESS';
                            statusClass = 'success';
                            successTests++;
                        } else {
                            failureTests++;
                        }
                    } else {
                        if (text.includes('Invalid') || text.includes('error')) {
                            status = 'success';
                            statusText = '✓ BLOCKED';
                            statusClass = 'success';
                            successTests++;
                        } else {
                            failureTests++;
                        }
                    }
                }
                
                totalTests++;
                testCounts[testIndex]++;
                
                // Update table row
                const row = document.getElementById(`row-${testIndex}`);
                if (row) {
                    row.cells[2].className = statusClass;
                    row.cells[2].textContent = statusText;
                    row.cells[3].textContent = responseTime + 'ms';
                    document.getElementById(`attempts-${testIndex}`).textContent = testCounts[testIndex];
                }
                
                if (totalTests % 10 === 0) {
                    log(`Progress: ${totalTests}/${document.getElementById('configAttempts').value} tests - ` +
                        `${successTests} successful, ${failureTests} failed`, 'info');
                }
                
                updateStats();
                
            } catch (error) {
                console.error('Test error:', error);
                failureTests++;
                totalTests++;
                testCounts[testIndex]++;
                log(`⚠ Error: ${error.message}`, 'error');
                updateStats();
            } finally {
                activeThreads--;
            }
        }

        async function startStressTest() {
            if (isRunning) {
                log('⚠ Test already running', 'warning');
                return;
            }
            
            stopRequested = false;
            isRunning = true;
            testStartTime = Date.now();
            
            const maxAttempts = parseInt(document.getElementById('configAttempts').value);
            const concurrent = parseInt(document.getElementById('configConcurrent').value);
            const delay = parseInt(document.getElementById('configDelay').value);
            
            log(`🚀 Starting stress test with ${concurrent} concurrent users...`, 'warning');
            log(`📊 Target: ${maxAttempts} total attempts with ${delay}ms delay`, 'info');
            
            // Reset counters
            totalTests = 0;
            successTests = 0;
            failureTests = 0;
            responseTimes = [];
            requestsPerSecond = [];
            testCounts = new Array(testCases.length).fill(0);
            
            // Reset table
            for (let i = 0; i < testCases.length; i++) {
                const row = document.getElementById(`row-${i}`);
                if (row) {
                    row.cells[2].className = 'warning';
                    row.cells[2].textContent = '⏳ Testing...';
                    row.cells[3].textContent = '-';
                    document.getElementById(`attempts-${i}`).textContent = '0';
                }
            }
            
            updateStats();
            
            // Run concurrent tests
            for (let attempt = 0; attempt < maxAttempts && !stopRequested; attempt++) {
                const promises = [];
                
                for (let i = 0; i < concurrent && attempt < maxAttempts && !stopRequested; i++, attempt++) {
                    const testIndex = attempt % testCases.length;
                    const testCase = testCases[testIndex];
                    
                    promises.push(testLogin(
                        testCase.username,
                        testCase.password,
                        testCase.expected,
                        testIndex
                    ));
                    
                    if (delay > 0) {
                        await new Promise(r => setTimeout(r, delay));
                    }
                }
                
                await Promise.all(promises);
            }
            
            isRunning = false;
            
            if (stopRequested) {
                log('🛑 Stress test stopped by user!', 'warning');
            } else {
                log('✅ Stress test completed successfully!', 'success');
            }
            
            // Calculate statistics
            const avgTime = (responseTimes.reduce((a, b) => a + b, 0) / responseTimes.length).toFixed(2);
            const peakRPS = Math.max(...requestsPerSecond).toFixed(2);
            const successRate = ((successTests / totalTests) * 100).toFixed(2);
            
            log(`📊 FINAL RESULTS:`, 'info');
            log(`   • Total Tests: ${totalTests}`, 'info');
            log(`   • Successful: ${successTests} (${successRate}%)`, 'success');
            log(`   • Failed: ${failureTests}`, 'error');
            log(`   • Avg Response: ${avgTime}ms`, 'info');
            log(`   • Peak RPS: ${peakRPS}`, 'info');
        }

        function stopStressTest() {
            if (!isRunning) {
                log('⚠ No test running', 'warning');
                return;
            }
            stopRequested = true;
            log('🛑 Stopping stress test...', 'warning');
        }

        function resetTest() {
            if (isRunning) {
                log('⚠ Cannot reset while test is running', 'warning');
                return;
            }
            
            totalTests = 0;
            successTests = 0;
            failureTests = 0;
            responseTimes = [];
            testCounts = new Array(testCases.length).fill(0);
            
            for (let i = 0; i < testCases.length; i++) {
                const row = document.getElementById(`row-${i}`);
                if (row) {
                    row.cells[2].className = 'warning';
                    row.cells[2].textContent = '⏳ Pending';
                    row.cells[3].textContent = '-';
                    document.getElementById(`attempts-${i}`).textContent = '0';
                }
            }
            
            document.getElementById('testLog').innerHTML = '<div class="log-entry">⚡ Test reset. Ready to start...</div>';
            updateStats();
            log('🔄 Test reset complete', 'info');
        }

        // Update timestamp every second
        setInterval(() => {
            const now = new Date();
            document.getElementById('timestamp').textContent = 
                now.toISOString().replace('T', ' ').substr(0, 19) + ' Z';
        }, 1000);
    </script>
</body>
</html>
