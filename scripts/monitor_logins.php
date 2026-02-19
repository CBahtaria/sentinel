<?php
// Monitor login attempts
while (true) {
    system('cls');
    echo "╔══════════════════════════════════════════════════════════╗\n";
    echo "║     👁️  LOGIN ATTEMPT MONITOR - REAL TIME                ║\n";
    echo "╚══════════════════════════════════════════════════════════╝\n\n";
    
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=bartarian_defence", "root", "");
        $logs = $pdo->query("
            SELECT * FROM audit_logs 
            WHERE action LIKE '%Login%' 
            ORDER BY created_at DESC 
            LIMIT 10
        ")->fetchAll();
        
        if (count($logs) > 0) {
            foreach ($logs as $log) {
                $statusColor = $log['status'] == 'success' ? '✅' : ($log['status'] == 'danger' ? '❌' : '⚠️');
                echo "$statusColor [{$log['created_at']}] {$log['action']} - {$log['username']} ({$log['ip_address']})\n";
            }
        } else {
            echo "No login attempts yet. Try logging in!\n";
        }
    } catch (Exception $e) {
        echo "Waiting for audit logs table...\n";
    }
    
    echo "\nPress Ctrl+C to exit\n";
    sleep(2);
}
