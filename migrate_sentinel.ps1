# Sentinel Precision Migration Script - FIXED
param(
    [switch]$WhatIf = $true,
    [switch]$Force = $false
)

$startTime = Get-Date
$projectRoot = "C:\xampp\htdocs\sentinel"

Write-Host "🚀 Sentinel Migration Tool" -ForegroundColor Cyan
Write-Host "==========================" -ForegroundColor Cyan
Write-Host "Project: $projectRoot"
Write-Host "Mode: $($(if($WhatIf){'PREVIEW'}else{'LIVE'}))" -ForegroundColor $(if($WhatIf){'Yellow'}else{'Red'})
Write-Host ""

# Create new structure
$structure = @(
    "public",
    "src/Controllers",
    "src/Models",
    "src/Services",
    "src/Core",
    "src/Middleware",
    "src/Helpers",
    "config",
    "database/migrations",
    "database/seeds",
    "database/factories",
    "tests/Unit",
    "tests/Feature",
    "scripts/archive",
    "storage/logs",
    "storage/cache",
    "storage/uploads",
    "storage/sessions",
    "storage/backups",
    "resources/views/layouts",
    "resources/views/partials",
    "resources/views/auth",
    "resources/views/dashboard",
    "resources/views/admin",
    "resources/assets/css",
    "resources/assets/js",
    "resources/assets/img",
    "resources/assets/vendor"
)

# Create directories
Write-Host "📁 Creating directory structure..." -ForegroundColor Cyan
foreach ($dir in $structure) {
    $fullPath = Join-Path $projectRoot $dir
    if (!(Test-Path $fullPath)) {
        if (!$WhatIf) {
            New-Item -ItemType Directory -Path $fullPath -Force | Out-Null
            Write-Host "✅ Created: $dir" -ForegroundColor Green
        } else {
            Write-Host "📁 Would create: $dir" -ForegroundColor Gray
        }
    }
}

# Define file migrations
$migrations = @{
    "dashboard.php" = "public/index.php"
    "admin_panel.php" = "public/admin.php"
    "api.php" = "public/api.php"
    "login.php" = "public/login.php"
    "logout.php" = "public/logout.php"
    "audit_log.php" = "src/Controllers/AuditController.php"
    "backup_manager.php" = "src/Controllers/BackupController.php"
    "drones.php" = "src/Controllers/DroneController.php"
    "drone-control.php" = "src/Controllers/DroneControlController.php"
    "map_view.php" = "src/Controllers/MapController.php"
    "notifications.php" = "src/Controllers/NotificationController.php"
    "recordings.php" = "src/Controllers/RecordingController.php"
    "reports.php" = "src/Controllers/ReportController.php"
    "system_monitor.php" = "src/Controllers/MonitorController.php"
    "threat-monitor.php" = "src/Controllers/ThreatController.php"
    "predictive.php" = "src/Controllers/PredictiveController.php"
    "concurrency.php" = "src/Controllers/ConcurrencyController.php"
    "analytics.php" = "src/Controllers/AnalyticsController.php"
    "websocket-server.php" = "src/Controllers/WebSocketController.php"
    "security.php" = "src/Services/SecurityService.php"
    "error_handler.php" = "src/Services/ErrorService.php"
    "rate-limiter.php" = "src/Services/RateLimiterService.php"
    "websocket-test.php" = "src/Services/WebSocketService.php"
    "check-system.php" = "src/Core/SystemCheck.php"
    "performance_check.php" = "src/Core/Performance.php"
    "maintenance.php" = "src/Core/Maintenance.php"
    ".htaccess" = "public/.htaccess"
    "robots.txt" = "public/robots.txt"
    "sw.js" = "public/sw.js"
    "database_schema.sql" = "database/schema.sql"
    "database_update.sql" = "database/migrations/2026_02_16_update.sql"
    "composer.json" = "composer.json"
    "composer.lock" = "composer.lock"
    "test-ios.html" = "public/test-ios.html"
    "generate_complete.php" = "scripts/generate_complete.php"
    "master_fix.php" = "scripts/master_fix.php"
    "stress_test.php" = "tests/stress_test.php"
    "stress_test_optimized.php" = "tests/stress_test_optimized.php"
    "api_get_stats.php" = "public/api/get_stats.php"
    "api_chart_data.php" = "public/api/chart_data.php"
}

# Process migrations
Write-Host "`n📦 Migrating core files..." -ForegroundColor Cyan
foreach ($source in $migrations.Keys) {
    $sourcePath = Join-Path $projectRoot $source
    $destPath = Join-Path $projectRoot $migrations[$source]
    
    if (Test-Path $sourcePath) {
        if (!$WhatIf) {
            # Read content
            $content = Get-Content $sourcePath -Raw -ErrorAction SilentlyContinue
            
            if ($content) {
                # Update include/require paths
                $content = $content -replace "require_once 'includes/", "require_once __DIR__ . '/../src/"
                $content = $content -replace "include 'config/", "include __DIR__ . '/../config/"
                $content = $content -replace "require 'vendor/", "require __DIR__ . '/../vendor/"
                
                # Add namespace for controllers
                if ($migrations[$source] -match "src/Controllers/") {
                    if ($content -notmatch "namespace") {
                        $content = "<?php`nnamespace Sentinel\Controllers;`n`n" + ($content -replace '^<\?php\s*', '')
                    }
                }
                
                # Save to new location
                $destDir = Split-Path $destPath -Parent
                if (!(Test-Path $destDir)) {
                    New-Item -ItemType Directory -Path $destDir -Force | Out-Null
                }
                
                $content | Set-Content $destPath -NoNewline
                
                # Rename original
                Rename-Item $sourcePath "$source.migrated"
                
                Write-Host "✅ Migrated: $source -> $($migrations[$source])" -ForegroundColor Green
            }
        } else {
            Write-Host "🔍 Would migrate: $source -> $($migrations[$source])" -ForegroundColor Yellow
        }
    }
}

# Create database config
$dbConfigPath = Join-Path $projectRoot "config/database.php"
if (!$WhatIf -and !(Test-Path $dbConfigPath)) {
    $dbConfig = @"
<?php
return [
    'default' => 'mysql',
    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => 'localhost',
            'database' => 'sentinel',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
        ]
    ]
];
"@
    $dbConfig | Set-Content $dbConfigPath
    Write-Host "✅ Created: config/database.php" -ForegroundColor Green
}

# Create index router
$indexPath = Join-Path $projectRoot "public/index.php"
if (!$WhatIf -and !(Test-Path $indexPath)) {
    $indexContent = @"
<?php
// Load configuration
\$config = require __DIR__ . '/../config/database.php';

// Autoloader
require __DIR__ . '/../vendor/autoload.php';

// Simple router
\$request = \$_SERVER['REQUEST_URI'];
\$base = '/sentinel';

// Remove base path and query string
\$path = str_replace(\$base, '', parse_url(\$request, PHP_URL_PATH));

// Route to appropriate handler
switch (\$path) {
    case '/':
    case '/dashboard':
        require __DIR__ . '/dashboard.php';
        break;
    case '/admin':
        require __DIR__ . '/admin.php';
        break;
    case '/login':
        require __DIR__ . '/login.php';
        break;
    case '/api':
        require __DIR__ . '/api.php';
        break;
    default:
        http_response_code(404);
        echo '404 - Page not found';
}
"@
    $indexContent | Set-Content $indexPath
    Write-Host "✅ Created: public/index.php" -ForegroundColor Green
}

Write-Host "`n✅ MIGRATION COMPLETE!" -ForegroundColor Green
