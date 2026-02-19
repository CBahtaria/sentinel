<?php
use PHPUnit\Framework\TestCase;
use UEDF\Session;

class SessionTest extends TestCase {
    private $session;
    
    protected function setUp(): void {
        parent::setUp();
        $this->session = new Session();
    }
    
    public function testSessionStart() {
        $this->session->start();
        $this->assertEquals(PHP_SESSION_ACTIVE, session_status());
    }
    
    public function testSetAndGet() {
        $this->session->set('test_key', 'test_value');
        $this->assertEquals('test_value', $this->session->get('test_key'));
    }
    
    public function testGetNonExistentReturnsDefault() {
        $this->assertNull($this->session->get('nonexistent'));
        $this->assertEquals('default', $this->session->get('nonexistent', 'default'));
    }
    
    public function testDelete() {
        $this->session->set('test_key', 'test_value');
        $this->session->delete('test_key');
        $this->assertNull($this->session->get('test_key'));
    }
    
    public function testIsLoggedIn() {
        // Should be false initially
        $this->assertFalse($this->session->isLoggedIn());
        
        // Set user_id
        $_SESSION['user_id'] = 1;
        $this->assertTrue($this->session->isLoggedIn());
        
        // Clean up
        unset($_SESSION['user_id']);
    }
}
