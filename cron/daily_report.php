<?php
/**
 * UEDF SENTINEL - Daily Report Generator
 * Run this script daily to email reports to all users
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=============================================\n";
echo "UEDF SENTINEL Daily Report Generator\n";
echo "=============================================\n\n";
echo "Start time: " . date('Y-m-d H:i:s') . "\n\n";

// Load required files
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/mailer.php';

try {
    $db = Database::getInstance()->getConnection();
    echo "✅ Database connected\n";
    
    // Get system uptime (simulated)
    $uptime = '15 days, 7 hours';
    
    // Get drone statistics
    $drone_stats = $db->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'ACTIVE' THEN 1 ELSE 0 END) as active,
            SUM(CASE WHEN status = 'STANDBY' THEN 1 ELSE 0 END) as standby,
            SUM(CASE WHEN status = 'MAINTENANCE' THEN 1 ELSE 0 END) as maintenance,
            AVG(battery_level) as avg_battery
        FROM drones
    ")->fetch(PDO::FETCH_ASSOC);
    
    // Get threat statistics
    $threat_stats = $db->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'ACTIVE' THEN 1 ELSE 0 END) as active,
            SUM(CASE WHEN severity = 'CRITICAL' AND status = 'ACTIVE' THEN 1 ELSE 0 END) as critical,
            SUM(CASE WHEN severity = 'HIGH' AND status = 'ACTIVE' THEN 1 ELSE 0 END) as high,
            SUM(CASE WHEN severity = 'MEDIUM' AND status = 'ACTIVE' THEN 1 ELSE 0 END) as medium,
            SUM(CASE WHEN severity = 'LOW' AND status = 'ACTIVE' THEN 1 ELSE 0 END) as low
        FROM threats
    ")->fetch(PDO::FETCH_ASSOC);
    
    // Get node statistics
    $node_count = $db->query("SELECT COUNT(*) FROM nodes WHERE status = 'ACTIVE'")->fetchColumn();
    
    // Get today's activity
    $today_activity = $db->query("
        SELECT COUNT(*) FROM audit_logs 
        WHERE DATE(created_at) = CURDATE()
    ")->fetchColumn();
    
    // Compile stats for report
    $stats = [
        'total_drones' => $drone_stats['total'] ?: 0,
        'active_drones' => $drone_stats['active'] ?: 0,
        'standby_drones' => $drone_stats['standby'] ?: 0,
        'maintenance_drones' => $drone_stats['maintenance'] ?: 0,
        'avg_battery' => round($drone_stats['avg_battery'] ?: 0),
        'total_threats' => $threat_stats['total'] ?: 0,
        'active_threats' => $threat_stats['active'] ?: 0,
        'critical_threats' => $threat_stats['critical'] ?: 0,
        'high_threats' => $threat_stats['high'] ?: 0,
        'medium_threats' => $threat_stats['medium'] ?: 0,
        'low_threats' => $threat_stats['low'] ?: 0,
        'active_nodes' => $node_count ?: 0,
        'today_activity' => $today_activity ?: 0,
        'uptime' => $uptime,
        'report_date' => date('Y-m-d'),
        'report_time' => date('H:i:s')
    ];
    
    echo "📊 Statistics gathered:\n";
    echo "   - Drones: {$stats['total_drones']} total, {$stats['active_drones']} active\n";
    echo "   - Threats: {$stats['total_threats']} total, {$stats['active_threats']} active\n";
    echo "   - Critical: {$stats['critical_threats']}\n\n";
    
    // Get all users with email addresses
    $users = $db->query("
        SELECT id, full_name, email, role 
        FROM users 
        WHERE email IS NOT NULL AND email != ''
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    echo "👥 Found " . count($users) . " users with email addresses\n\n";
    
    $success_count = 0;
    $fail_count = 0;
    
    // Send report to each user
    foreach ($users as $user) {
        echo "📧 Sending to {$user['full_name']} ({$user['email']})... ";
        
        $result = Mailer::sendDailyReport($user['email'], $user['full_name'], $stats);
        
        if ($result['success']) {
            echo "✅ Sent\n";
            $success_count++;
            
            // Log the report sent
            $stmt = $db->prepare("
                INSERT INTO audit_logs (user_id, action, details) 
                VALUES (?, 'DAILY_REPORT', 'Daily report sent')
            ");
            $stmt->execute([$user['id']]);
        } else {
            echo "❌ Failed: {$result['message']}\n";
            $fail_count++;
        }
        
        // Small delay to avoid rate limiting
        usleep(500000); // 0.5 seconds
    }
    
    echo "\n=============================================\n";
    echo "✅ REPORT SUMMARY\n";
    echo "=============================================\n";
    echo "Total users: " . count($users) . "\n";
    echo "Successful: $success_count\n";
    echo "Failed: $fail_count\n";
    echo "Completed: " . date('Y-m-d H:i:s') . "\n";
    echo "=============================================\n";
    
    // Log the batch job
    $stmt = $db->prepare("
        INSERT INTO audit_logs (user_id, action, details) 
        VALUES (1, 'DAILY_REPORT_BATCH', 'Daily reports sent to $success_count users')
    ");
    $stmt->execute();
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n";
?>
