<#
.SYNOPSIS
    CLEANUP - Sentinel Project Restructure
.DESCRIPTION
    This script will reorganize your project structure.
    BACKUP IS CREATED FIRST.
#>

# Set strict mode
Set-StrictMode -Version Latest
$ErrorActionPreference = "Stop"

# Configuration
$root = Get-Location
$backupRoot = "C:\sentinel-backup-$(Get-Date -Format 'yyyyMMdd-HHmmss')"

Write-Host "============================================"
Write-Host "CLEANUP DEPLOYED - BACKUP FIRST"
Write-Host "============================================"

Write-Host "[1/7] CREATING FULL BACKUP AT: $backupRoot" -ForegroundColor Yellow

# Create backup
New-Item -ItemType Directory -Path $backupRoot -Force | Out-Null
Copy-Item -Path "$root\*" -Destination $backupRoot -Recurse -Force -ErrorAction SilentlyContinue
Write-Host "  - Full backup created" -ForegroundColor Green

Write-Host "[2/7] REMOVING ZERO-BYTE DEBUG FILES" -ForegroundColor Yellow
$debugFiles = @(
    "close()", "connect_error", "fetch_array())", 
    "fetch_assoc()", "query(SHOW TABLES)", ".rnd"
)

foreach ($file in $debugFiles) {
    $path = Join-Path $root $file
    if (Test-Path $path) {
        Remove-Item -Path $path -Force
        Write-Host "  - Removed: $file" -ForegroundColor Green
    }
}

Write-Host "[3/7] ORGANIZING SCRIPTS" -ForegroundColor Yellow

# Create scripts directory
New-Item -ItemType Directory -Path "$root/scripts/tools" -Force -ErrorAction SilentlyContinue | Out-Null
New-Item -ItemType Directory -Path "$root/scripts/db" -Force -ErrorAction SilentlyContinue | Out-Null

# Move generator scripts
Get-ChildItem -Path $root -Filter "generate-*.php" | ForEach-Object {
    Move-Item -Path $_.FullName -Destination "$root/scripts/tools/" -Force
    Write-Host "  - Moved: $($_.Name) -> scripts/tools/"
}

# Handle import scripts
$importScripts = Get-ChildItem -Path $root -Filter "import*.php"
if ($importScripts) {
    $latestImport = $importScripts | Sort-Object LastWriteTime -Descending | Select-Object -First 1
    Copy-Item -Path $latestImport.FullName -Destination "$root/scripts/db/import-current.php" -Force
    Write-Host "  - Latest import saved as: import-current.php"
    
    # Archive others
    foreach ($script in $importScripts) {
        if ($script.FullName -ne $latestImport.FullName) {
            Move-Item -Path $script.FullName -Destination "$root/scripts/db/" -Force
            Write-Host "  - Archived: $($script.Name)"
        }
    }
}

Write-Host "[4/7] CREATING DIRECTORY STRUCTURE" -ForegroundColor Yellow

# Create new structure
$structure = @(
    "public/assets/css", "public/assets/js", "public/assets/images",
    "public/uploads", "src/Controllers", "src/Models", 
    "src/Services", "config", "database/migrations",
    "storage/logs", "storage/cache", "storage/uploads",
    "tests"
)

foreach ($dir in $structure) {
    New-Item -ItemType Directory -Path "$root/$dir" -Force -ErrorAction SilentlyContinue | Out-Null
}
Write-Host "  - Directory structure created" -ForegroundColor Green

Write-Host "[5/7] MOVING WEB FILES" -ForegroundColor Yellow

# Move web files to public/assets
$webFiles = @("*.css", "*.js", "*.png", "*.jpg", "*.gif")
foreach ($pattern in $webFiles) {
    Get-ChildItem -Path $root -Filter $pattern -Recurse -ErrorAction SilentlyContinue | 
    Where-Object { $_.Directory.Name -ne "public" -and $_.Directory.Name -ne "assets" } |
    ForEach-Object {
        $ext = $_.Extension.TrimStart('.')
        $dest = "$root/public/assets/$ext/"
        New-Item -ItemType Directory -Path $dest -Force | Out-Null
        Move-Item -Path $_.FullName -Destination $dest -Force
        Write-Host "  - Moved: $($_.Name) -> public/assets/$ext/"
    }
}

Write-Host "[6/7] CREATING SECURITY FILES" -ForegroundColor Yellow

# Create public .htaccess
@"
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^ index.php [QSA,L]
</IfModule>
Options -Indexes
"@ | Set-Content "$root/public/.htaccess" -Force

# Create root .htaccess
@"
RewriteEngine On
RewriteRule ^$ public/ [L]
RewriteRule (.*) public/$1 [L]
"@ | Set-Content "$root/.htaccess" -Force

Write-Host "  - Security files created" -ForegroundColor Green

Write-Host "[7/7] CLEANING UP" -ForegroundColor Yellow

# Archive old directories
$oldDirs = @("chaos", "evidence", "backup_main", "error", "cache", "temp")
foreach ($dir in $oldDirs) {
    $path = Join-Path $root $dir
    if (Test-Path $path) {
        $archivePath = "$root/archive/$dir"
        New-Item -ItemType Directory -Path $archivePath -Force | Out-Null
        Copy-Item -Path "$path\*" -Destination $archivePath -Recurse -Force -ErrorAction SilentlyContinue
        Remove-Item -Path $path -Recurse -Force -ErrorAction SilentlyContinue
        Write-Host "  - Archived: $dir"
    }
}

# Remove backup files
Get-ChildItem -Path $root -Filter "*.bak" -Recurse -ErrorAction SilentlyContinue |
ForEach-Object {
    Remove-Item -Path $_.FullName -Force
    Write-Host "  - Removed backup: $($_.Name)"
}

# Create .gitignore
@"
/vendor/
/node_modules/
.env
.DS_Store
Thumbs.db
/storage/logs/*
/storage/cache/*
/archive/
"@ | Set-Content "$root/.gitignore" -Force

Write-Host "============================================"
Write-Host "CLEANUP COMPLETE" -ForegroundColor Green
Write-Host "============================================"
Write-Host ""
Write-Host "SUMMARY:" -ForegroundColor Cyan
Write-Host "  Backup: $backupRoot"
Write-Host "  Web root: /public"
Write-Host "  Archives: /archive/"
Write-Host ""
Write-Host "NEXT STEPS:" -ForegroundColor Yellow
Write-Host "  1. Check /archive/ for any needed files"
Write-Host "  2. Update your code to use new paths"
Write-Host "  3. Test your application"
Write-Host "  4. Run: git init"