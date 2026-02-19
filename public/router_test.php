<?php
// Simple router test
require_once __DIR__ . '/../vendor/autoload.php';

use UEDF\Core\Router;

$router = new Router();

// Simple test route
$router->add('/test', function() {
    echo "Router is working!";
}, 'GET');

$router->dispatch('/test', 'GET');
