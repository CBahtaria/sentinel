<?php
use PHPUnit\Framework\TestCase;
use UEDF\Config\Config;

class ConfigTest extends TestCase {
    private $config;
    
    protected function setUp(): void {
        parent::setUp();
        $this->config = Config::getInstance();
    }
    
    public function testConfigInstance() {
        $this->assertInstanceOf(Config::class, $this->config);
    }
    
    public function testGetDatabaseConfig() {
        $this->assertIsString($this->config->get('db.host'));
        $this->assertIsString($this->config->get('db.name'));
        $this->assertIsString($this->config->get('db.user'));
    }
    
    public function testGetAppConfig() {
        $this->assertIsString($this->config->get('app.name'));
        $this->assertIsBool($this->config->get('app.debug'));
        $this->assertIsString($this->config->get('app.url'));
    }
    
    public function testDefaultValues() {
        $this->assertEquals('localhost', $this->config->get('db.host', 'localhost'));
        $this->assertNotNull($this->config->get('security.jwt_secret'));
    }
    
    public function testNonExistentKeyReturnsDefault() {
        $this->assertEquals('default', $this->config->get('nonexistent.key', 'default'));
    }
}
