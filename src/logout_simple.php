<?php
require_once 'includes/session.php'; 
 
session_destroy(); 
header('Location: login_simple.php'); 
exit; 
?> 
