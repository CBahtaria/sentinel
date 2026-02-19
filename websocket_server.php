<?php
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use UEDF\WebSocket\RealTimeServer;
use UEDF\Config\Config;

require_once __DIR__ . '/vendor/autoload.php';

$config = Config::getInstance();
$port = $config->get('ws.port', 8081);

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new RealTimeServer()
        )
    ),
    $port
);

echo "WebSocket server started on port $port\n";
$server->run();
