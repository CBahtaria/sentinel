<?php
$pdo = new PDO("mysql:host=localhost;dbname=bartarian_defence", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Check if users table exists
$result = $pdo->query("SHOW TABLES LIKE 'users'");
if ($result->rowCount() == 0) {
    echo "Creating users table...\n";
    
    $pdo->exec("
        CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            role VARCHAR(50) DEFAULT 'viewer',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    // Add default users
    $defaultUsers = [
        ['commander', password_hash('Password123!', PASSWORD_DEFAULT), 'commander'],
        ['operator', password_hash('Password123!', PASSWORD_DEFAULT), 'operator'],
        ['analyst', password_hash('Password123!', PASSWORD_DEFAULT), 'analyst'],
        ['viewer', password_hash('Password123!', PASSWORD_DEFAULT), 'viewer']
    ];
    
    $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)");
    foreach ($defaultUsers as $user) {
        $stmt->execute($user);
    }
    
    echo "✅ Users table created with default users\n";
} else {
    echo "✅ Users table already exists\n";
    
    // Count users
    $count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    echo "   Users in database: $count\n";
}
