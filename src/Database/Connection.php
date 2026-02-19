<?php
namespace UEDF\Database;

use PDO;
use PDOException;
use UEDF\Config;

class Connection {
    private static $instance = null;
    private $pdo;
    
    private function __construct() {
        $config = Config::getInstance();
        
        try {
            $this->pdo = new PDO(
                "mysql:host={$config->get('db.host')};dbname={$config->get('db.name')};charset=utf8mb4",
                $config->get('db.user'),
                $config->get('db.pass')
            );
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->pdo;
    }
}
