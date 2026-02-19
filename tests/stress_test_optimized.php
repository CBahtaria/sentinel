<?php
/**
 * OPTIMIZED STRESS TEST - UEDF SENTINEL
 * Focus on performance metrics and latency
 */

// Configuration
define('TEST_ITERATIONS', 1000);
define('CONCURRENT', 50);
define('API_ENDPOINT', 'http://localhost:8080/sentinel/login.php?test=1');

// Performance metrics
$metrics = [
    'response_times' => [],
    'success_rate' => 0,
    'errors' => 0,
    'peak_rps' => 0,
    'memory_usage' => 0
];

// Test function
function testEndpoint() {
    $start = microtime(true);
    $ch = curl_init(API_ENDPOINT);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-Requested-With: XMLHttpRequest']);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $end = microtime(true);
    $time = round(($end - $start) * 1000, 2); // milliseconds
    
    return [
        'time' => $time,
        'success' => $httpCode === 200,
        'code' => $httpCode
    ];
}

// Run concurrent tests
echo "🚀 Starting optimized stress test...\n";
$startTime = microtime(true);

for ($i = 0; $i < TEST_ITERATIONS; $i += CONCURRENT) {
    $batch = [];
    for ($j = 0; $j < CONCURRENT && ($i + $j) < TEST_ITERATIONS; $j++) {
        $batch[] = testEndpoint();
    }
    
    foreach ($batch as $result) {
        $metrics['response_times'][] = $result['time'];
        if ($result['success']) {
            $metrics['success_rate']++;
        } else {
            $metrics['errors']++;
        }
    }
    
    // Calculate RPS
    $elapsed = microtime(true) - $startTime;
    $rps = round($i / $elapsed);
    if ($rps > $metrics['peak_rps']) {
        $metrics['peak_rps'] = $rps;
    }
}

$totalTime = microtime(true) - $startTime;

// Calculate statistics
$avgTime = array_sum($metrics['response_times']) / count($metrics['response_times']);
$minTime = min($metrics['response_times']);
$maxTime = max($metrics['response_times']);
$successRate = ($metrics['success_rate'] / TEST_ITERATIONS) * 100;

// Output results
echo "\n📊 PERFORMANCE RESULTS\n";
echo "════════════════════════════════════\n";
echo "Total Requests: " . TEST_ITERATIONS . "\n";
echo "Concurrency: " . CONCURRENT . "\n";
echo "Total Time: " . round($totalTime, 2) . " seconds\n";
echo "────────────────────────────────────\n";
echo "✅ Success Rate: " . round($successRate, 2) . "%\n";
echo "❌ Errors: " . $metrics['errors'] . "\n";
echo "────────────────────────────────────\n";
echo "⚡ Average Response: " . round($avgTime, 2) . "ms\n";
echo "⚡ Fastest Response: " . round($minTime, 2) . "ms\n";
echo "⚡ Slowest Response: " . round($maxTime, 2) . "ms\n";
echo "────────────────────────────────────\n";
echo "📈 Peak RPS: " . $metrics['peak_rps'] . " req/sec\n";
echo "📊 95th Percentile: " . round($metrics['response_times'][round(count($metrics['response_times']) * 0.95)], 2) . "ms\n";
echo "════════════════════════════════════\n";
