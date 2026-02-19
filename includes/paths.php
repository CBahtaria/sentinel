<?php
/**
 * Universal path handler for UEDF Sentinel
 */

// Define root path based on execution context
if (!defined('UEDF_ROOT')) {
    if (defined('__DIR__')) {
        // Try to determine root based on current file
        $current_dir = str_replace('\\', '/', __DIR__);
        if (strpos($current_dir, '/modules/') !== false) {
            // We're in a module
            define('UEDF_ROOT', dirname(dirname($current_dir)));
        } elseif (strpos($current_dir, '/includes/') !== false) {
            // We're in includes
            define('UEDF_ROOT', dirname($current_dir));
        } else {
            // We're in root or unknown
            define('UEDF_ROOT', dirname($current_dir));
        }
    } else {
        // Fallback
        define('UEDF_ROOT', 'C:/xampp/htdocs/sentinel');
    }
}

// Helper function to get absolute path
function uedf_path($relative_path) {
    return UEDF_ROOT . '/' . ltrim($relative_path, '/');
}

// Auto-include session if needed
if (!isset($NO_SESSION) || !$NO_SESSION) {
    $session_file = uedf_path('includes/session.php');
    if (file_exists($session_file)) {
        require_once $session_file;
    }
}
?>
