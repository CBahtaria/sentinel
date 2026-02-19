<?php
use PHPUnit\Framework\TestCase;
use UEDF\Database\Connection;

class DatabaseConnectionTest extends TestCase {
    public function testConnection() {
        $db = Connection::getInstance();
        $this->assertInstanceOf(PDO::class, $db);
    }
}
