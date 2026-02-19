<?php
// Simple API endpoint for nodes
header('Content-Type: application/json');

try {
    $pdo = new PDO("mysql:host=localhost;dbname=bartarian_defence", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get nodes from database (you can customize this query based on your schema)
    $stmt = $pdo->query("
        SELECT id, name, type, status, cpu, memory 
        FROM nodes 
        LIMIT 20
    ");
    
    $nodes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($nodes)) {
        // Return sample data if no nodes found
        echo json_encode([
            ['id' => 1, 'name' => 'CMD-NODE', 'type' => 'Command', 'status' => 'online', 'cpu' => 45, 'memory' => 62],
            ['id' => 2, 'name' => 'DRONE-CTRL', 'type' => 'Control', 'status' => 'online', 'cpu' => 32, 'memory' => 45],
            ['id' => 3, 'name' => 'THREAT-DB', 'type' => 'Database', 'status' => 'online', 'cpu' => 78, 'memory' => 85],
            ['id' => 4, 'name' => 'SURVEILLANCE', 'type' => 'Sensor', 'status' => 'warning', 'cpu' => 92, 'memory' => 76],
            ['id' => 5, 'name' => 'COMM-LINK', 'type' => 'Communication', 'status' => 'online', 'cpu' => 23, 'memory' => 34]
        ]);
    } else {
        echo json_encode($nodes);
    }
    
} catch (Exception $e) {
    // Return sample data on error
    echo json_encode([
        ['id' => 1, 'name' => 'CMD-NODE', 'type' => 'Command', 'status' => 'online', 'cpu' => 45, 'memory' => 62],
        ['id' => 2, 'name' => 'DRONE-CTRL', 'type' => 'Control', 'status' => 'online', 'cpu' => 32, 'memory' => 45],
        ['id' => 3, 'name' => 'THREAT-DB', 'type' => 'Database', 'status' => 'online', 'cpu' => 78, 'memory' => 85],
        ['id' => 4, 'name' => 'SURVEILLANCE', 'type' => 'Sensor', 'status' => 'warning', 'cpu' => 92, 'memory' => 76],
        ['id' => 5, 'name' => 'COMM-LINK', 'type' => 'Communication', 'status' => 'online', 'cpu' => 23, 'memory' => 34]
    ]);
}
