<?php
require_once __DIR__ . '/vendor/autoload.php';

use UEDF\Config;
use UEDF\Core\Router;

// Initialize config
$config = Config::getInstance();

// Set error reporting based on environment
if ($config->get('app.debug')) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize router
$router = new Router();

// Define routes
$router->add('/', 'DashboardController', 'index', 'GET');
$router->add('/login', 'AuthController', 'login', 'GET');
$router->add('/login', 'AuthController', 'authenticate', 'POST');
$router->add('/logout', 'AuthController', 'logout', 'GET');

// Drone routes
$router->add('/drones', 'DroneController', 'index', 'GET');
$router->add('/drones/{id}', 'DroneController', 'show', 'GET');
$router->add('/api/drones', 'DroneController', 'apiStatus', 'GET');

// Threat routes
$router->add('/threats', 'ThreatController', 'index', 'GET');
$router->add('/threats/{id}', 'ThreatController', 'show', 'GET');

// Analytics
$router->add('/analytics', 'AnalyticsController', 'index', 'GET');
$router->add('/reports', 'ReportController', 'index', 'GET');

// System
$router->add('/system/monitor', 'MonitorController', 'index', 'GET');
$router->add('/system/audit', 'AuditController', 'index', 'GET');

// Dispatch the request
$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
