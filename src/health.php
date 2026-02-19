<?php 
header('Content-Type: application/json'); 
$status = [ 
    'status' =
    'timestamp' = H:i:s'), 
    'php' =
    'session' = == PHP_SESSION_ACTIVE ? 'active' : 'inactive' 
]; 
echo json_encode($status); 
?> 
