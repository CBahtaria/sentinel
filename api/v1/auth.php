<?php 
header('Content-Type: application/json'); 
function getDB() { 
    try { 
        return new PDO('mysql:host=localhost;dbname=uedf_sentinel', 'root', ''); 
    } catch (Exception $e) { 
        return null; 
    } 
} 
function sendResponse($data, $status=200) { 
    http_response_code($status); 
    echo json_encode($data); 
} 
?> 
