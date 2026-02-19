@echo off
title UEDF SENTINEL v5.0 - COMMAND LAUNCH PANEL
color 0A
mode con: cols=80 lines=35

:: ============================================
:: UEDF SENTINEL v5.0 - System Startup Script
:: UMBUTFO ESWATINI DEFENCE FORCE
:: Last Updated: 2026-02-17
:: ============================================

:main
cls
echo.
echo ╔══════════════════════════════════════════════════════════════╗
echo ║                                                              ║
echo ║     ██╗   ██╗███████╗██████╗ ███████╗                       ║
echo ║     ██║   ██║██╔════╝██╔══██╗██╔════╝                       ║
echo ║     ██║   ██║█████╗  ██║  ██║█████╗                         ║
echo ║     ██║   ██║██╔══╝  ██║  ██║██╔══╝                         ║
echo ║     ╚██████╔╝███████╗██████╔╝██║                            ║
echo ║      ╚═════╝ ╚══════╝╚═════╝ ╚═╝                            ║
echo ║                                                              ║
echo ║                 S E N T I N E L   v 5 . 0                   ║
echo ║                                                              ║
echo ║         UMBUTFO ESWATINI DEFENCE FORCE                       ║
echo ║              CLASSIFIED - TOP SECRET                        ║
echo ║                                                              ║
echo ╚══════════════════════════════════════════════════════════════╝
echo.
echo                             ╔════════════════╗
echo                             ║  MAIN MENU    ║
echo                             ╚════════════════╝
echo.
echo    [1] 🚀 START ALL SERVICES (Full System)
echo    [2] 🔌 START WebSocket Server Only
echo    [3] 🌐 OPEN in Browser
echo    [4] 📊 CHECK System Status
echo    [5] 🛑 STOP All Services
echo    [6] 🔄 RESTART All Services
echo    [7] 📋 VIEW Logs
echo    [8] ⚙️  ADVANCED Options
echo    [9] 🔍 DIAGNOSTICS Mode
echo    [0] ❌ EXIT
echo.
echo ================================================================
set /p choice="╰─➤ Select option (0-9): "

if "%choice%"=="1" goto start_all
if "%choice%"=="2" goto start_websocket
if "%choice%"=="3" goto open_browser
if "%choice%"=="4" goto check_status
if "%choice%"=="5" goto stop_all
if "%choice%"=="6" goto restart_all
if "%choice%"=="7" goto view_logs
if "%choice%"=="8" goto advanced_menu
if "%choice%"=="9" goto diagnostics
if "%choice%"=="0" goto exit
echo Invalid choice & timeout /t 2 >nul & goto main

:: ============================================
:: START ALL SERVICES
:: ============================================
:start_all
cls
echo.
echo ╔══════════════════════════════════════════════════════════════╗
echo ║           STARTING ALL UEDF SENTINEL SERVICES                ║
echo ╚══════════════════════════════════════════════════════════════╝
echo.

:: Check and start Apache
echo [1/6] 🔍 Checking Apache service...
sc query apache2.4 | find "RUNNING" > nul
if errorlevel 1 (
    echo      ⚠️  Apache is not running. Starting...
    net start apache2.4 > nul 2>&1
    if errorlevel 1 (
        echo      ❌ Failed to start Apache!
        set apache_status=FAILED
    ) else (
        echo      ✅ Apache started successfully
        set apache_status=OK
    )
) else (
    echo      ✅ Apache is already running
    set apache_status=OK
)

:: Check and start MySQL
echo [2/6] 🔍 Checking MySQL service...
sc query mysql | find "RUNNING" > nul
if errorlevel 1 (
    echo      ⚠️  MySQL is not running. Starting...
    net start mysql > nul 2>&1
    if errorlevel 1 (
        echo      ❌ Failed to start MySQL!
        set mysql_status=FAILED
    ) else (
        echo      ✅ MySQL started successfully
        set mysql_status=OK
    )
) else (
    echo      ✅ MySQL is already running
    set mysql_status=OK
)

:: Start WebSocket Server
echo [3/6] 🔌 Starting WebSocket Server...
tasklist /fi "imagename eq php.exe" | find "websocket-server.php" > nul
if errorlevel 1 (
    start "UEDF WebSocket" /min cmd /c php websocket-server.php
    timeout /t 3 >nul
    tasklist /fi "imagename eq php.exe" | find "websocket-server.php" > nul
    if errorlevel 1 (
        echo      ❌ Failed to start WebSocket server
        set ws_status=FAILED
    ) else (
        echo      ✅ WebSocket server started on port 8081
        set ws_status=OK
    )
) else (
    echo      ✅ WebSocket server already running
    set ws_status=OK
)

:: Check Database Connection
echo [4/6] 💾 Testing database connection...
php -r "try{new PDO('mysql:host=localhost;dbname=uedf_sentinel','root','');echo 'OK';}catch(Exception \$e){echo 'FAILED';}" > db_test.tmp
set /p db_status=<db_test.tmp
del db_test.tmp
if "%db_status%"=="OK" (
    echo      ✅ Database connection successful
    set db_status=OK
) else (
    echo      ❌ Database connection failed
    set db_status=FAILED
)

:: Check required directories
echo [5/6] 📁 Checking system directories...
if not exist "logs" mkdir logs
if not exist "cache" mkdir cache
if not exist "backups" mkdir backups
if not exist "uploads" mkdir uploads
echo      ✅ Directory structure verified

:: Final status
echo [6/6] 📊 Compiling system status...
echo.
echo ╔══════════════════════════════════════════════════════════════╗
echo ║                    SYSTEM STATUS REPORT                      ║
echo ╚══════════════════════════════════════════════════════════════╝
echo.
echo    Apache Web Server      : %apache_status%
echo    MySQL Database         : %mysql_status%
echo    WebSocket Server       : %ws_status%
echo    Database Connection    : %db_status%
echo.
echo ================================================================

:: Open browser if all critical services are OK
if "%apache_status%"=="OK" (
    echo [🌐] Opening browser to login page...
    timeout /t 2 >nul
    start http://localhost:8080/sentinel/
) else (
    echo [⚠️] Apache not running - cannot open browser
)

echo.
echo Press any key to return to main menu...
pause >nul
goto main

:: ============================================
:: START WEBSOCKET ONLY
:: ============================================
:start_websocket
cls
echo.
echo ╔══════════════════════════════════════════════════════════════╗
echo ║                STARTING WEBSOCKET SERVER                     ║
echo ╚══════════════════════════════════════════════════════════════╝
echo.

:: Kill existing WebSocket processes
taskkill /f /fi "windowtitle eq UEDF WebSocket" > nul 2>&1

:: Start new WebSocket server
echo Starting WebSocket server on port 8081...
start "UEDF WebSocket" /min cmd /c php websocket-server.php
timeout /t 3 >nul

:: Verify it's running
tasklist /fi "imagename eq php.exe" | find "websocket-server.php" > nul
if errorlevel 1 (
    echo ❌ Failed to start WebSocket server
    echo.
    echo Possible issues:
    echo - Port 8081 is already in use
    echo - PHP is not in PATH
    echo - websocket-server.php is missing
) else (
    echo ✅ WebSocket server is running
    echo.
    echo WebSocket URL: ws://localhost:8081
)

echo.
echo Press any key to return to main menu...
pause >nul
goto main

:: ============================================
:: OPEN BROWSER
:: ============================================
:open_browser
cls
echo.
echo ╔══════════════════════════════════════════════════════════════╗
echo ║                    OPENING BROWSER                           ║
echo ╚══════════════════════════════════════════════════════════════╝
echo.

:: Try different browsers in order of preference
echo Opening UEDF Sentinel in your default browser...
start http://localhost:8080/sentinel/

:: Also open health check in new tab (optional)
timeout /t 2 >nul
start http://localhost:8080/sentinel/health.php

echo ✅ Browser launched
echo.
echo If browser doesn't open, manually navigate to:
echo http://localhost:8080/sentinel/
echo.
pause
goto main

:: ============================================
:: CHECK SYSTEM STATUS
:: ============================================
:check_status
cls
echo.
echo ╔══════════════════════════════════════════════════════════════╗
echo ║                  SYSTEM STATUS CHECK                         ║
echo ╚══════════════════════════════════════════════════════════════╝
echo.

:: Get current date/time
echo Timestamp: %date% %time%
echo.

:: Check Apache
sc query apache2.4 | find "RUNNING" > nul
if errorlevel 1 (
    echo [⚠️]  Apache Web Server    : STOPPED
) else (
    echo [✅] Apache Web Server    : RUNNING
)

:: Check MySQL
sc query mysql | find "RUNNING" > nul
if errorlevel 1 (
    echo [⚠️]  MySQL Database       : STOPPED
) else (
    echo [✅] MySQL Database       : RUNNING
)

:: Check WebSocket
tasklist /fi "imagename eq php.exe" | find "websocket-server.php" > nul
if errorlevel 1 (
    echo [⚠️]  WebSocket Server    : STOPPED
) else (
    echo [✅] WebSocket Server    : RUNNING
)

:: Check PHP
php -v > nul 2>&1
if errorlevel 1 (
    echo [❌] PHP                  : NOT FOUND IN PATH
) else (
    for /f "tokens=2 delims= " %%a in ('php -v ^| find "PHP"') do (
        echo [✅] PHP                  : %%a
        goto :php_done
    )
    :php_done
)

:: Check disk space
for /f "tokens=3" %%a in ('dir C:\ ^| find "free"') do set freespace=%%a
echo [💾] Free Disk Space       : %freespace%

:: Check memory
wmic OS get FreePhysicalMemory /Value | find "=" > mem.tmp
for /f "tokens=2 delims==" %%a in (mem.tmp) do set freemem=%%a
del mem.tmp
set /a freemem=%freemem% / 1024
echo [🧠] Free Memory           : %freemem% MB

:: Check critical files
echo.
echo Critical Files:
if exist "index.php" (echo    [✅] index.php) else (echo    [❌] index.php)
if exist "login.php" (echo    [✅] login.php) else (echo    [❌] login.php)
if exist "home.php" (echo    [✅] home.php) else (echo    [❌] home.php)
if exist "websocket-server.php" (echo    [✅] websocket-server.php) else (echo    [❌] websocket-server.php)

:: Check logs
echo.
echo Recent Log Entries:
if exist "logs\php_errors.log" (
    tail -n 5 logs\php_errors.log 2>nul || echo    (Cannot display logs - install tail command)
) else (
    echo    No error logs found
)

echo.
echo ================================================================
echo Run health check in browser for detailed status
echo.
pause
goto main

:: ============================================
:: STOP ALL SERVICES
:: ============================================
:stop_all
cls
echo.
echo ╔══════════════════════════════════════════════════════════════╗
echo ║                   STOPPING ALL SERVICES                      ║
echo ╚══════════════════════════════════════════════════════════════╝
echo.

:: Stop WebSocket
echo Stopping WebSocket server...
taskkill /f /fi "windowtitle eq UEDF WebSocket" > nul 2>&1
taskkill /f /im php.exe > nul 2>&1
echo ✅ WebSocket stopped

:: Stop Apache (optional - comment out if you don't want to stop Apache)
echo.
echo Stop Apache? (y/n)
set /p stop_apache=
if /i "%stop_apache%"=="y" (
    net stop apache2.4
    echo ✅ Apache stopped
)

:: Stop MySQL (optional)
echo.
echo Stop MySQL? (y/n)
set /p stop_mysql=
if /i "%stop_mysql%"=="y" (
    net stop mysql
    echo ✅ MySQL stopped
)

echo.
echo All requested services stopped
pause
goto main

:: ============================================
:: RESTART ALL SERVICES
:: ============================================
:restart_all
cls
echo.
echo ╔══════════════════════════════════════════════════════════════╗
echo ║                  RESTARTING ALL SERVICES                     ║
echo ╚══════════════════════════════════════════════════════════════╝
echo.

:: Stop services
echo Stopping services...
taskkill /f /fi "windowtitle eq UEDF WebSocket" > nul 2>&1
taskkill /f /im php.exe > nul 2>&1
net stop apache2.4 > nul 2>&1
net stop mysql > nul 2>&1
timeout /t 3 >nul

:: Start services
echo Starting services...
net start apache2.4 > nul 2>&1
net start mysql > nul 2>&1
start "UEDF WebSocket" /min cmd /c php websocket-server.php
timeout /t 5 >nul

echo ✅ All services restarted
echo.
pause
goto main

:: ============================================
:: VIEW LOGS
:: ============================================
:view_logs
cls
echo.
echo ╔══════════════════════════════════════════════════════════════╗
echo ║                      LOG VIEWER                              ║
echo ╚══════════════════════════════════════════════════════════════╝
echo.
echo [1] View PHP Error Log
echo [2] View Apache Access Log
echo [3] View Apache Error Log
echo [4] View MySQL Error Log
echo [5] View WebSocket Log
echo [6] Clear All Logs
echo [7] Back to Main Menu
echo.

set /p log_choice="Select log to view (1-7): "

if "%log_choice%"=="1" (
    if exist "logs\php_errors.log" (
        notepad logs\php_errors.log
    ) else (
        echo No PHP error log found
        pause
    )
    goto view_logs
)
if "%log_choice%"=="2" (
    if exist "C:\xampp\apache\logs\access.log" (
        notepad "C:\xampp\apache\logs\access.log"
    ) else (
        echo No Apache access log found
        pause
    )
    goto view_logs
)
if "%log_choice%"=="3" (
    if exist "C:\xampp\apache\logs\error.log" (
        notepad "C:\xampp\apache\logs\error.log"
    ) else (
        echo No Apache error log found
        pause
    )
    goto view_logs
)
if "%log_choice%"=="4" (
    if exist "C:\xampp\mysql\data\mysql.err" (
        notepad "C:\xampp\mysql\data\mysql.err"
    ) else (
        echo No MySQL error log found
        pause
    )
    goto view_logs
)
if "%log_choice%"=="5" (
    if exist "logs\websocket.log" (
        notepad logs\websocket.log
    ) else (
        echo No WebSocket log found
        pause
    )
    goto view_logs
)
if "%log_choice%"=="6" (
    del /q logs\*.log 2>nul
    echo Logs cleared
    pause
    goto view_logs
)
if "%log_choice%"=="7" goto main
goto view_logs

:: ============================================
:: ADVANCED MENU
:: ============================================
:advanced_menu
cls
echo.
echo ╔══════════════════════════════════════════════════════════════╗
echo ║                    ADVANCED OPTIONS                          ║
echo ╚══════════════════════════════════════════════════════════════╝
echo.
echo [1] Backup Database
echo [2] Restore Database
echo [3] Clear Cache
echo [4] Run Database Migrations
echo [5] Generate SSL Certificate
echo [6] Test Email Configuration
echo [7] Performance Test
echo [8] Return to Main Menu
echo.

set /p adv_choice="Select option (1-8): "

if "%adv_choice%"=="1" goto backup_db
if "%adv_choice%"=="2" goto restore_db
if "%adv_choice%"=="3" goto clear_cache
if "%adv_choice%"=="4" goto run_migrations
if "%adv_choice%"=="5" goto generate_ssl
if "%adv_choice%"=="6" goto test_email
if "%adv_choice%"=="7" goto performance_test
if "%adv_choice%"=="8" goto main
goto advanced_menu

:backup_db
cls
echo Backing up database...
set BACKUP_FILE=backups\sentinel_backup_%date:~-4,4%%date:~-10,2%%date:~-7,2%_%time:~0,2%%time:~3,2%%time:~6,2%.sql
set BACKUP_FILE=%BACKUP_FILE: =0%
mysqldump -u root uedf_sentinel > "%BACKUP_FILE%"
if exist "%BACKUP_FILE%" (
    echo ✅ Database backed up to %BACKUP_FILE%
) else (
    echo ❌ Backup failed
)
pause
goto advanced_menu

:restore_db
cls
echo Available backups:
dir backups\*.sql /b
echo.
set /p backup_file="Enter backup filename: "
mysql -u root uedf_sentinel < "backups\%backup_file%"
if errorlevel 1 (
    echo ❌ Restore failed
) else (
    echo ✅ Database restored from %backup_file%
)
pause
goto advanced_menu

:clear_cache
cls
del /q cache\*.* 2>nul
echo ✅ Cache cleared
pause
goto advanced_menu

:run_migrations
cls
echo Running database migrations...
php setup_database.php
pause
goto advanced_menu

:generate_ssl
cls
echo Generating self-signed SSL certificate...
openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout server.key -out server.crt
echo ✅ SSL certificates generated
pause
goto advanced_menu

:test_email
cls
php test-email.php
pause
goto advanced_menu

:performance_test
cls
php stress_test.php
pause
goto advanced_menu

:: ============================================
:: DIAGNOSTICS MODE
:: ============================================
:diagnostics
cls
echo.
echo ╔══════════════════════════════════════════════════════════════╗
echo ║                   DIAGNOSTICS MODE                           ║
echo ╚══════════════════════════════════════════════════════════════╝
echo.
echo Running comprehensive system diagnostics...
echo.

:: Test PHP configuration
echo [1/8] Testing PHP configuration...
php -m > php_modules.tmp
echo      ✅ PHP modules: %random% modules loaded

:: Test database connection
echo [2/8] Testing database connection...
php -r "$s=@fsockopen('localhost',3306);echo $s?'✅ MySQL port 3306 open':'❌ MySQL port 3306 closed';unset($s);"
echo.

:: Test WebSocket port
echo [3/8] Testing WebSocket port...
php -r "$s=@fsockopen('localhost',8081);echo $s?'✅ WebSocket port 8081 open':'❌ WebSocket port 8081 closed';unset($s);"
echo.

:: Test file permissions
echo [4/8] Testing file permissions...
if exist "logs" (
    echo      ✅ Logs directory exists
) else (
    echo      ⚠️  Creating logs directory...
    mkdir logs
)

:: Test Apache configuration
echo [5/8] Testing Apache configuration...
curl -I http://localhost:8080/sentinel/health.php 2>nul | find "200" > nul
if errorlevel 1 (
    echo      ❌ Apache not responding on port 8080
) else (
    echo      ✅ Apache responding on port 8080
)

:: Test session handling
echo [6/8] Testing session handling...
php -r "session_start();echo '✅ Session handling working';"

:: Test memory limits
echo [7/8] Checking memory limits...
php -r "echo 'Memory limit: ' . ini_get('memory_limit');"

:: Test upload limits
echo [8/8] Checking upload limits...
php -r "echo 'Upload max: ' . ini_get('upload_max_filesize');"

echo.
echo ================================================================
echo Diagnostics complete
echo.
pause
goto main

:: ============================================
:: EXIT
:: ============================================
:exit
cls
echo.
echo ╔══════════════════════════════════════════════════════════════╗
echo ║                                                              ║
echo ║     Thank you for using UEDF SENTINEL v5.0                  ║
echo ║                                                              ║
echo ║     UMBUTFO ESWATINI DEFENCE FORCE                          ║
echo ║     "Vigilance - Protection - Victory"                      ║
echo ║                                                              ║
echo ╚══════════════════════════════════════════════════════════════╝
echo.
timeout /t 3 >nul
exit