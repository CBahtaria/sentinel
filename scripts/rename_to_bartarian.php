<?php
/**
 * Bartarian Defence - Project Renaming Tool
 * Renames all instances of BARTARIAN to Bartarian Defence
 */

class BartarianRenamer {
    private $rootDir;
    private $changes = [];
    private $totalFiles = 0;
    private $totalChanges = 0;
    
    public function __construct($rootDir) {
        $this->rootDir = $rootDir;
    }
    
    /**
     * Run the renaming process
     */
    public function run() {
        echo "╔══════════════════════════════════════════════════════════╗\n";
        echo "║     🔄 BARTARIAN DEFENCE - PROJECT RENAMING TOOL         ║\n";
        echo "╚══════════════════════════════════════════════════════════╝\n\n";
        
        $this->renameFiles();
        $this->renameDatabase();
        $this->renameConfigs();
        $this->generateReport();
    }
    
    /**
     * Rename all PHP and text files
     */
    private function renameFiles() {
        echo "\n📁 Scanning files...\n";
        
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->rootDir)
        );
        
        $patterns = [
            'bartarian_defence' => 'bartarian_defence',
            'BARTARIAN DEFENCE' => 'BARTARIAN DEFENCE',
            'BARTARIAN_DEFENCE' => 'BARTARIAN_DEFENCE',
            'BARTARIAN-DEFENCE' => 'BARTARIAN-DEFENCE',
            'Bartarian Defence' => 'Bartarian Defence',
            'BARTARIAN' => 'BARTARIAN',
            'Bartarian' => 'Bartarian',
            'bartarian' => 'bartarian'
        ];
        
        foreach ($files as $file) {
            if ($file->isFile() && in_array($file->getExtension(), ['php', 'html', 'js', 'css', 'sql', 'txt', 'md', 'json'])) {
                $this->processFile($file->getPathname(), $patterns);
            }
        }
        
        echo "\n   ✅ Processed {$this->totalFiles} files\n";
        echo "   ✅ Made {$this->totalChanges} replacements\n";
    }
    
    /**
     * Process individual file
     */
    private function processFile($filepath, $patterns) {
        $content = file_get_contents($filepath);
        $original = $content;
        $fileChanges = 0;
        
        foreach ($patterns as $search => $replace) {
            $content = str_replace($search, $replace, $content, $count);
            $fileChanges += $count;
        }
        
        if ($content !== $original) {
            file_put_contents($filepath, $content);
            $this->changes[] = [
                'file' => str_replace($this->rootDir, '', $filepath),
                'changes' => $fileChanges
            ];
            $this->totalChanges += $fileChanges;
        }
        
        $this->totalFiles++;
    }
    
    /**
     * Rename database
     */
    private function renameDatabase() {
        echo "\n🗄️  Updating database...\n";
        
        try {
            $pdo = new PDO("mysql:host=localhost", "root", "");
            
            // Check if old database exists
            $stmt = $pdo->query("SHOW DATABASES LIKE 'bartarian_defence'");
            if ($stmt->fetch()) {
                // Create new database
                $pdo->exec("CREATE DATABASE IF NOT EXISTS bartarian_defence CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                
                // Copy all tables
                $pdo->exec("USE bartarian_defence");
                $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
                
                foreach ($tables as $table) {
                    // Rename table if it contains bartarian
                    if (strpos($table, 'bartarian') !== false) {
                        $newTable = str_replace('bartarian', 'bartarian', $table);
                        $pdo->exec("RENAME TABLE bartarian_defence.$table TO bartarian_defence.$newTable");
                    } else {
                        $pdo->exec("CREATE TABLE bartarian_defence.$table LIKE bartarian_defence.$table");
                        $pdo->exec("INSERT INTO bartarian_defence.$table SELECT * FROM bartarian_defence.$table");
                    }
                }
                
                echo "   ✅ Database copied to bartarian_defence\n";
            } else {
                echo "   ⚠️ Source database not found, creating new...\n";
                $pdo->exec("CREATE DATABASE IF NOT EXISTS bartarian_defence CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            }
            
        } catch (Exception $e) {
            echo "   ❌ Database error: " . $e->getMessage() . "\n";
        }
    }
    
    /**
     * Update configuration files
     */
    private function renameConfigs() {
        echo "\n⚙️  Updating configurations...\n";
        
        // Update database config
        $configFile = $this->rootDir . '/config/database.php';
        if (file_exists($configFile)) {
            $config = file_get_contents($configFile);
            $config = str_replace('bartarian_defence', 'bartarian_defence', $config);
            file_put_contents($configFile, $config);
            echo "   ✅ Updated database config\n";
        }
        
        // Update .env files
        $envFiles = glob($this->rootDir . '/.env*');
        foreach ($envFiles as $envFile) {
            $env = file_get_contents($envFile);
            $env = str_replace('bartarian_defence', 'bartarian_defence', $env);
            $env = str_replace('BARTARIAN', 'BARTARIAN', $env);
            file_put_contents($envFile, $env);
            echo "   ✅ Updated " . basename($envFile) . "\n";
        }
        
        // Update replication config
        $repConfig = $this->rootDir . '/config/redundancy/database.php';
        if (file_exists($repConfig)) {
            $config = file_get_contents($repConfig);
            $config = str_replace('bartarian_defence', 'bartarian_defence', $config);
            $config = str_replace('BARTARIAN', 'BARTARIAN', $config);
            file_put_contents($repConfig, $config);
            echo "   ✅ Updated replication config\n";
        }
    }
    
    /**
     * Generate renaming report
     */
    private function generateReport() {
        echo "\n📊 RENAMING REPORT\n";
        echo "==================\n\n";
        
        echo "Files Processed: {$this->totalFiles}\n";
        echo "Total Replacements: {$this->totalChanges}\n\n";
        
        echo "Top 10 Files Changed:\n";
        usort($this->changes, function($a, $b) {
            return $b['changes'] - $a['changes'];
        });
        
        $topFiles = array_slice($this->changes, 0, 10);
        foreach ($topFiles as $change) {
            echo "   • {$change['file']} ({$change['changes']} changes)\n";
        }
        
        // Save report
        $reportFile = $this->rootDir . '/logs/renaming_report_' . date('Ymd_His') . '.json';
        file_put_contents($reportFile, json_encode([
            'timestamp' => date('Y-m-d H:i:s'),
            'total_files' => $this->totalFiles,
            'total_changes' => $this->totalChanges,
            'changes' => $this->changes
        ], JSON_PRETTY_PRINT));
        
        echo "\n📄 Report saved to: $reportFile\n";
        
        echo "\n✅ RENAMING COMPLETE!\n";
        echo "\n";
        echo "╔══════════════════════════════════════════════════════════╗\n";
        echo "║     🎉 WELCOME TO BARTARIAN DEFENCE SYSTEM!              ║\n";
        echo "╚══════════════════════════════════════════════════════════╝\n";
    }
}

// Run the renamer
$renamer = new BartarianRenamer(__DIR__);
$renamer->run();
