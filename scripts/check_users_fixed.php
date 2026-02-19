<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=bartarian_defence", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "🔍 BARTARIAN DEFENCE - USER VERIFICATION\n";
    echo "========================================\n\n";
    
    // Show table structure
    $result = $pdo->query("DESCRIBE users");
    echo "📋 Users table structure:\n";
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "   • {$row['Field']} - {$row['Type']}\n";
    }
    echo "\n";
    
    // Count users
    $count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    echo "📊 Total users: $count\n\n";
    
    // List users
    $users = $pdo->query("SELECT id, username, role, is_active FROM users")->fetchAll();
    echo "📋 User list:\n";
    foreach ($users as $user) {
        echo "   • {$user['username']} (ID: {$user['id']}) - Role: {$user['role']} - " . 
             ($user['is_active'] ? 'ACTIVE' : 'INACTIVE') . "\n";
    }
    
    // Test commander login
    echo "\n🔑 Testing commander login:\n";
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute(['commander']);
    $user = $stmt->fetch();
    
    if ($user) {
        if (password_verify('Password123!', $user['password'])) {
            echo "   ✅ Password verification successful\n";
        } else {
            echo "   ❌ Password verification failed\n";
        }
    } else {
        echo "   ❌ Commander user not found\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}
