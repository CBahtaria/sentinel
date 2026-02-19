<?php
/**
 * BARTARIAN DEFENCE - System Review Tool
 * Opens each page sequentially for review
 */

echo "\n";
echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║     🚀 BARTARIAN DEFENCE - SYSTEM REVIEW TOOL            ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n\n";

echo "This tool will open each page one at a time for review.\n";
echo "Press Enter after reviewing each page to continue.\n\n";

$pages = [
    ['url' => 'login.php', 'name' => 'LOGIN PAGE', 'desc' => 'Authentication gateway'],
    ['url' => 'dashboard.php', 'name' => 'DASHBOARD', 'desc' => 'Main command center'],
    ['url' => 'drone-control.php', 'name' => 'DRONE CONTROL', 'desc' => 'Fleet management'],
    ['url' => 'threat-monitor.php', 'name' => 'THREAT MONITOR', 'desc' => 'Threat detection'],
    ['url' => 'node_control.php', 'name' => 'NODE CONTROL', 'desc' => 'Network visualization'],
    ['url' => 'analytics.php', 'name' => 'ANALYTICS', 'desc' => 'System metrics'],
    ['url' => 'admin.php', 'name' => 'ADMIN PANEL', 'desc' => 'User management'],
    ['url' => 'audit_log.php', 'name' => 'AUDIT LOGS', 'desc' => 'Activity logging'],
    ['url' => 'chaos_test.php', 'name' => 'CHAOS ENGINE', 'desc' => 'Resilience testing'],
    ['url' => 'api_docs.php', 'name' => 'API DOCS', 'desc' => 'API documentation'],
    ['url' => 'db_test.php', 'name' => 'DATABASE TEST', 'desc' => 'DB diagnostics']
];

foreach ($pages as $index => $page) {
    $number = $index + 1;
    $total = count($pages);
    
    echo "\n";
    echo "┌────────────────────────────────────────────────────────┐\n";
    echo "│  PAGE {$number} of {$total}                                            │\n";
    echo "├────────────────────────────────────────────────────────┤\n";
    echo "│  📌 {$page['name']}\n";
    echo "│  📝 {$page['desc']}\n";
    echo "│  🔗 http://localhost:8080/sentinel/public/{$page['url']}\n";
    echo "└────────────────────────────────────────────────────────┘\n";
    
    // Open the page
    shell_exec("start http://localhost:8080/sentinel/public/{$page['url']}");
    
    // Wait for user input
    echo "Press Enter after reviewing this page to continue...";
    trim(fgets(STDIN));
}

echo "\n";
echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║     ✅ REVIEW COMPLETE - ALL PAGES VERIFIED              ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n\n";
echo "What would you like to improve next?\n";
echo "1. Login Page\n";
echo "2. Dashboard\n";
echo "3. Drone Control\n";
echo "4. Threat Monitor\n";
echo "5. Node Control\n";
echo "6. Analytics\n";
echo "7. Admin Panel\n";
echo "8. Audit Logs\n";
echo "9. Chaos Engine\n";
echo "10. API Docs\n";
echo "11. Database\n";
echo "12. UI/UX Design\n";
echo "13. Security Features\n";
echo "14. Performance\n";
echo "15. Other (specify)\n\n";
echo "Enter number (1-15): ";

$choice = trim(fgets(STDIN));

$options = [
    1 => 'login.php',
    2 => 'dashboard.php',
    3 => 'drone-control.php',
    4 => 'threat-monitor.php',
    5 => 'node_control.php',
    6 => 'analytics.php',
    7 => 'admin.php',
    8 => 'audit_log.php',
    9 => 'chaos_test.php',
    10 => 'api_docs.php',
    11 => 'database',
    12 => 'ui-ux',
    13 => 'security',
    14 => 'performance',
    15 => 'other'
];

$next = $options[$choice] ?? 'ui-ux';

echo "\n📋 Next focus: " . strtoupper($next) . "\n";
echo "Run the appropriate enhancement command for this component.\n";
