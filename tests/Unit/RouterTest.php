<?php
use PHPUnit\Framework\TestCase;
use UEDF\Core\Router;

class RouterTest extends TestCase {
    private $router;
    
    protected function setUp(): void {
        parent::setUp();
        $this->router = new Router();
    }
    
    public function testAddRoute() {
        $this->router->add('/test', 'TestController', 'index', 'GET');
        // If no exception, test passes
        $this->assertTrue(true);
    }
    
    public function testRouteCompilation() {
        $reflection = new ReflectionClass($this->router);
        $method = $reflection->getMethod('compileRoute');
        $method->setAccessible(true);
        
        $compiled = $method->invoke($this->router, '/users/{id}');
        $this->assertStringContainsString('(?P<id>', $compiled);
    }
}
