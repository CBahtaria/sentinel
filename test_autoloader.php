<?php
require_once 'vendor/autoload.php';

echo "? Composer autoloader loaded\n";
echo "Looking for classes in UEDF\\ namespace...\n\n";

// List all classes in the src directory
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('src'));
foreach ($files as $file) {
    if ($file->isFile() && $file->getExtension() == 'php') {
        $relativePath = str_replace(['src/', '.php'], '', $file->getPathname());
        $className = 'UEDF\\' . str_replace('/', '\\', $relativePath);
        echo "Found potential class: $className\n";
    }
}

echo "\n? Autoloader is ready to use!\n";
