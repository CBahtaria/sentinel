<?php
// Force reset commander password
try {
    $pdo = new PDO("mysql:host=localhost;dbname=bartarian_defence", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "🔧 RESETTING COMMANDER PASSWORD\n";
    echo "================================\n\n";
    
    $newHash = password_hash('Password123!', PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE username = 'commander'");
    $stmt->execute([$newHash]);
    
    if ($stmt->rowCount() > 0) {
        echo "✅ Commander password reset successfully\n";
    } else {
        echo "❌ Commander user not found, creating...\n";
        
        $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, email, full_name, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(['commander', $newHash, 'commander@defence.bd', 'Gen. Bartaria', 'commander']);
        echo "✅ Commander created with password: Password123!\n";
    }
    
    // Verify
    $stmt = $pdo->prepare("SELECT username FROM users WHERE username = 'commander'");
    $stmt->execute();
    if ($stmt->fetch()) {
        echo "\n✅ Commander user exists in database\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
