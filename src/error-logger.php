<?php
/**
 * Error Logger Class
 */
class ErrorLogger {
    private $logFile;
    private $logDir;
    
    public function __construct() {
        $this->logDir = __DIR__ . '/logs';
        $this->logFile = $this->logDir . '/error.log';
        
        if (!file_exists($this->logDir)) {
            mkdir($this->logDir, 0777, true);
        }
    }
    
    public function log($message, $level = 'ERROR') {
        $timestamp = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $uri = $_SERVER['REQUEST_URI'] ?? 'unknown';
        $user = $_SESSION['username'] ?? 'guest';
        
        $logEntry = sprintf(
            "[%s] [%s] [%s] [%s] [%s] %s%s",
            $timestamp,
            $level,
            $ip,
            $user,
            $uri,
            $message,
            PHP_EOL
        );
        
        file_put_contents($this->logFile, $logEntry, FILE_APPEND);
    }
    
    public function getLogs($lines = 100) {
        if (!file_exists($this->logFile)) {
            return [];
        }
        
        $logs = file($this->logFile);
        return array_slice($logs, -$lines);
    }
    
    public function clearLogs() {
        if (file_exists($this->logFile)) {
            unlink($this->logFile);
        }
    }
}
?>
