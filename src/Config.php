<?php
namespace UEDF\Config;

use Dotenv\Dotenv;

class Config {
    private static $instance = null;
    private $config = [];
    
    private function __construct() {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
        $dotenv->load();
        
        $this->config = [
            'db' => [
                'host' => $_ENV['DB_HOST'] ?? 'localhost',
                'name' => $_ENV['DB_NAME'] ?? 'uedf_sentinel',
                'user' => $_ENV['DB_USER'] ?? 'root',
                'pass' => $_ENV['DB_PASS'] ?? '',
            ],
            'app' => [
                'name' => $_ENV['APP_NAME'] ?? 'UEDF Sentinel',
                'env' => $_ENV['APP_ENV'] ?? 'development',
                'debug' => filter_var($_ENV['APP_DEBUG'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'url' => $_ENV['APP_URL'] ?? 'http://localhost:8080',
            ],
            'security' => [
                'session_lifetime' => (int)($_ENV['SESSION_LIFETIME'] ?? 120),
                'hash_cost' => (int)($_ENV['HASH_COST'] ?? 10),
                'jwt_secret' => $_ENV['JWT_SECRET'] ?? '',
            ]
        ];
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function get($key, $default = null) {
        $keys = explode('.', $key);
        $value = $this->config;
        
        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }
        
        return $value;
    }
}
