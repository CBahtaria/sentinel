<?php
use PHPUnit\Framework\TestCase;
use UEDF\Controllers\DroneController;

class DroneControllerFeatureTest extends TestCase {
    private $controller;
    
    protected function setUp(): void {
        parent::setUp();
        $this->controller = new DroneController();
    }
    
    public function testControllerExists() {
        $this->assertInstanceOf(DroneController::class, $this->controller);
    }
    
    public function testControllerHasRequiredMethods() {
        $this->assertTrue(method_exists($this->controller, 'index'));
        $this->assertTrue(method_exists($this->controller, 'show'));
        $this->assertTrue(method_exists($this->controller, 'apiStatus'));
        $this->assertTrue(method_exists($this->controller, 'requireAuth'));
    }
}
