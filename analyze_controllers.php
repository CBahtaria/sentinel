<?php
// Run this to analyze controller dependencies
require_once 'vendor/autoload.php';

$controllers = glob('src/Controllers/*.php');
echo "Analyzing controller dependencies...\n\n";

foreach ($controllers as $controller) {
    $content = file_get_contents($controller);
    $name = basename($controller);
    
    echo "?? $name\n";
    
    // Look for class instantiations that might need use statements
    preg_match_all('/new\s+(\w+)/', $content, $matches);
    if (!empty($matches[1])) {
        echo "  Instantiates: " . implode(', ', array_unique($matches[1])) . "\n";
    }
    
    // Look for static calls
    preg_match_all('/(\w+)::/', $content, $matches);
    if (!empty($matches[1])) {
        echo "  Static calls: " . implode(', ', array_unique($matches[1])) . "\n";
    }
    
    // Look for extends
    preg_match('/extends\s+(\w+)/', $content, $matches);
    if (isset($matches[1])) {
        echo "  Extends: {$matches[1]}\n";
    }
    
    // Look for implements
    preg_match('/implements\s+([\w,\s]+)/', $content, $matches);
    if (isset($matches[1])) {
        echo "  Implements: {$matches[1]}\n";
    }
    
    echo "\n";
}
