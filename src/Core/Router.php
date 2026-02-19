<?php
namespace UEDF\Core;

use UEDF\Config;

class Router {
    private $routes = [];
    private $params = [];
    private $config;
    
    public function __construct() {
        $this->config = Config::getInstance();
    }
    
    public function add($route, $controller, $action, $method = 'GET') {
        $this->routes[] = [
            'route' => $this->compileRoute($route),
            'controller' => $controller,
            'action' => $action,
            'method' => strtoupper($method)
        ];
    }
    
    private function compileRoute($route) {
        $route = preg_replace('/\//', '\\/', $route);
        $route = preg_replace('/\{([a-z]+)\}/', '(?P<\1>[a-zA-Z0-9-]+)', $route);
        $route = '/^' . $route . '$/';
        return $route;
    }
    
    public function dispatch($url, $method) {
        $url = $this->removeQueryString($url);
        $method = strtoupper($method);
        
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            
            if (preg_match($route['route'], $url, $matches)) {
                foreach ($matches as $key => $value) {
                    if (is_string($key)) {
                        $this->params[$key] = $value;
                    }
                }
                
                $controller = "UEDF\\Controllers\\{$route['controller']}";
                if (class_exists($controller)) {
                    $controllerObject = new $controller();
                    $action = $route['action'];
                    
                    if (is_callable([$controllerObject, $action])) {
                        call_user_func_array([$controllerObject, $action], $this->params);
                        return;
                    }
                }
            }
        }
        
        // 404 handling
        header("HTTP/1.0 404 Not Found");
        echo "404 - Page not found";
    }
    
    private function removeQueryString($url) {
        if ($url != '') {
            $parts = explode('?', $url, 2);
            $url = $parts[0];
        }
        return $url;
    }
}
