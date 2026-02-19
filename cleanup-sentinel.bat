@echo off
title UEDF SENTINEL v5.0 - SYSTEM CLEANUP
color 0C
cls
echo ============================================
echo    UEDF SENTINEL v5.0 - SYSTEM CLEANUP
echo ============================================
echo.
echo This script will clean up unnecessary files
echo and ensure your system is properly organized.
echo.
echo Press Ctrl+C to cancel or any key to continue...
pause >nul

:: ============================================
:: DELETE USELESS/DUPLICATE FILES
:: ============================================
echo.
echo [1/8] Removing useless and duplicate files...

:: Temporary/text files
del /q "1234.txt" 2>nul
del /q "fetchAll()" 2>nul
del /q "fetchColumn()" 2>nul
del /q "getConnection()" 2>nul
del /q "getMessage())" 2>nul
del /q "query(" 2>nul
del /q "mkdir" 2>nul
del /q "'.env" 2>nul
del /q "'.rnd" 2>nul
del /q "'.htaccess.new" 2>nul
del /q "sw-diagnostic.html" 2>nul
del /q "ios26-debug.html" 2>nul
del /q "minimal-sw.html" 2>nul
del /q "UEDF Sentinel - Login_ex.html" 2>nul

:: Backup files we don't need
del /q "*.backup" 2>nul
del /q "*.bak" 2>nul
del /q "*.old" 2>nul

:: Remove weird named files
del /q "'API" 2>nul

echo    ✅ Useless files removed

:: ============================================
:: ORGANIZE CONFIGURATION FILES
:: ============================================
echo.
echo [2/8] Organizing configuration files...

:: Move .env to config folder
if exist ".env" (
    move ".env" "config\.env" >nul 2>&1
    echo    ✅ Moved .env to config folder
)

:: Move composer files to config
if exist "composer.json" (
    move "composer.json" "config\" >nul 2>&1
)
if exist "composer.lock" (
    move "composer.lock" "config\" >nul 2>&1
)
if exist "composer.phar" (
    move "composer.phar" "config\" >nul 2>&1
)

:: Move manifest.json to config
if exist "manifest.json" (
    move "manifest.json" "config\" >nul 2>&1
)

echo    ✅ Configuration files organized

:: ============================================
:: CREATE MISSING DIRECTORIES
:: ============================================
echo.
echo [3/8] Creating missing directories...

mkdir "uploads" 2>nul
mkdir "temp" 2>nul
mkdir "sessions" 2>nul
mkdir "cache" 2>nul
mkdir "api/v1" 2>nul
mkdir "api/v2" 2>nul
mkdir "modules/auth" 2>nul
mkdir "modules/dashboard" 2>nul
mkdir "modules/drones" 2>nul
mkdir "modules/threats" 2>nul
mkdir "assets/css" 2>nul
mkdir "assets/js" 2>nul
mkdir "assets/images" 2>nul
mkdir "assets/fonts" 2>nul

echo    ✅ Missing directories created

:: ============================================
:: CREATE MISSING ESSENTIAL FILES
:: ============================================
echo.
echo [4/8] Creating missing essential files...

:: Create empty log files
echo. > logs\php_errors.log
echo. > logs\apache_access.log
echo. > logs\apache_error.log
echo. > logs\websocket.log
echo. > logs\audit.log

:: Create .htaccess in logs folder
echo "Deny from all" > logs\.htaccess

:: Create index.html in each directory to prevent directory listing
echo ^<html^>^<head^>^<title^>Access Denied^</title^>^<style^>body{background:#0a0f1c;color:#ff006e;font-family:monospace;text-align:center;padding:50px;}^</style^>^</head^>^<body^>^<h1^>403 - Access Denied^</h1^>^<p^>UEDF SENTINEL - Classified System^</p^>^</body^>^</html^> > uploads\index.html
copy uploads\index.html config\index.html >nul
copy uploads\index.html backups\index.html >nul
copy uploads\index.html cache\index.html >nul
copy uploads\index.html temp\index.html >nul
copy uploads\index.html logs\index.html >nul
copy uploads\index.html sessions\index.html >nul

echo    ✅ Essential files created

:: ============================================
:: FIX FILE PERMISSIONS
:: ============================================
echo.
echo [5/8] Setting correct file permissions...

:: Make batch files executable
attrib -r *.bat
attrib +r start-sentinel.bat
attrib +r cleanup-sentinel.bat

:: Make critical files read-only
attrib +r index.php
attrib +r login.php
attrib +r home.php
attrib +r .htaccess

echo    ✅ Permissions set

:: ============================================
:: CREATE MISSING MODULE FILES
:: ============================================
echo.
echo [6/8] Creating missing module stubs...

:: Create basic module files if they don't exist
if not exist "modules\login.php" (
    echo ^<?php // Login module - handled by main login.php ?^> > modules\login.php
)
if not exist "modules\home.php" (
    echo ^<?php // Home module - redirect to main home.php header('Location: ../home.php'); exit; ?^> > modules\home.php
)
if not exist "modules\dashboard.php" (
    echo ^<?php // Dashboard module include '../dashboard.php'; ?^> > modules\dashboard.php
)
if not exist "modules\drones.php" (
    echo ^<?php // Drones module include '../drones.php'; ?^> > modules\drones.php
)
if not exist "modules\concurrency.php" (
    echo ^<?php // Threats module include '../concurrency.php'; ?^> > modules\concurrency.php
)

echo    ✅ Module stubs created

:: ============================================
:: VERIFY DATABASE CONNECTION
:: ============================================
echo.
echo [7/8] Testing database connection...

php -r "
try {
    \$pdo = new PDO('mysql:host=localhost;dbname=uedf_sentinel', 'root', '');
    echo \"✅ Database connection successful\n\";
    
    // Create tables if they don't exist
    \$pdo->exec(\"
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            full_name VARCHAR(100),
            role ENUM('commander','operator','analyst','viewer') DEFAULT 'viewer',
            two_factor_enabled BOOLEAN DEFAULT FALSE,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            last_login DATETIME
        );
        
        CREATE TABLE IF NOT EXISTS drones (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            status ENUM('ACTIVE','STANDBY','MAINTENANCE','OFFLINE') DEFAULT 'STANDBY',
            battery_level INT DEFAULT 100,
            last_update DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        
        CREATE TABLE IF NOT EXISTS threats (
            id INT AUTO_INCREMENT PRIMARY KEY,
            type VARCHAR(100) NOT NULL,
            severity ENUM('CRITICAL','HIGH','MEDIUM','LOW') DEFAULT 'MEDIUM',
            status ENUM('ACTIVE','INVESTIGATING','RESOLVED') DEFAULT 'ACTIVE',
            detected_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
    \");
    echo \"✅ Database tables created/verified\n\";
    
    // Insert default users if not exists
    \$count = \$pdo->query(\"SELECT COUNT(*) FROM users\")->fetchColumn();
    if (\$count == 0) {
        \$pdo->prepare(\"INSERT INTO users (username, password, full_name, role) VALUES 
            ('commander', '\" . password_hash('uedf2026', PASSWORD_DEFAULT) . \"', 'Gen. Bartaria', 'commander'),
            ('operator', '\" . password_hash('operator2026', PASSWORD_DEFAULT) . \"', 'Maj. Dlamini', 'operator'),
            ('analyst', '\" . password_hash('analyst2026', PASSWORD_DEFAULT) . \"', 'Capt. Nkosi', 'analyst'),
            ('viewer', '\" . password_hash('viewer2026', PASSWORD_DEFAULT) . \"', 'Lt. Mamba', 'viewer')
        \")->execute();
        echo \"✅ Default users created\n\";
    }
    
} catch (Exception \$e) {
    echo \"❌ Database error: \" . \$e->getMessage() . \"\n\";
    exit(1);
}
" 2>nul

if errorlevel 1 (
    echo    ⚠️  Database issues detected - run setup_database.php
) else (
    echo    ✅ Database verified
)

:: ============================================
:: CREATE SYSTEM CHECK REPORT
:: ============================================
echo.
echo [8/8] Generating system check report...

(
echo ========================================
echo UEDF SENTINEL v5.0 - SYSTEM CHECK REPORT
echo ========================================
echo Generated: %date% %time%
echo.
echo Directory Structure:
dir /ad /b
echo.
echo Critical Files:
if exist "index.php" echo [OK] index.php
if exist "login.php" echo [OK] login.php
if exist "home.php" echo [OK] home.php
if exist ".htaccess" echo [OK] .htaccess
if exist "config\database.php" echo [OK] config\database.php
if exist "websocket-server.php" echo [OK] websocket-server.php
echo.
echo File Counts:
echo PHP files: dir /s *.php 2>nul | find "File(s)" | tail -1
echo Batch files: dir *.bat 2>nul | find "File(s)" | tail -1
) > system_report.txt

echo    ✅ System report generated: system_report.txt

:: ============================================
:: COMPLETION
:: ============================================
echo.
echo ============================================
echo          CLEANUP COMPLETE!
echo ============================================
echo.
echo Next steps:
echo 1. Run: start-sentinel.bat
echo 2. Login at: http://localhost:8080/sentinel/
echo 3. Check system: http://localhost:8080/sentinel/health.php
echo.
echo Default credentials:
echo   commander / uedf2026
echo   operator / operator2026
echo.
echo System report saved to: system_report.txt
echo.
pause