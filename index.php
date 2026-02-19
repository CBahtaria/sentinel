<?php
// Get the module from URL
$module = $_GET['module'] ?? 'home';

// Security check
$module = preg_replace('/[^a-zA-Z0-9_-]/', '', $module);

// Try to include from modules folder
if (file_exists('modules/' . $module . '.php')) {
    include 'modules/' . $module . '.php';
} else {
    echo "Module not found: " . htmlspecialchars($module);
}
?>