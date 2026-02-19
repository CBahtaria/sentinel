<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=bartarian_defence", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "🔍 CHECKING USERS IN DATABASE\n";
    echo "==============================\n\n";
    
    $users = $pdo->query("SELECT id, username, role, is_active FROM users")->fetchAll();
    
    if (count($users) > 0) {
        echo "✅ Found " . count($users) . " users:\n\n";
        foreach ($users as $user) {
            echo "   • ID: {$user['id']}\n";
            echo "     Username: {$user['username']}\n";
            echo "     Role: {$user['role']}\n";
            echo "     Status: " . ($user['is_active'] ? 'ACTIVE' : 'INACTIVE') . "\n\n";
        }
    } else {
        echo "❌ No users found in database!\n\n";
        echo "Creating default users...\n";
        
        // Create default users
        $users = [
            ['commander', password_hash('Password123!', PASSWORD_DEFAULT), 'commander@defence.bd', 'Gen. Bartaria', 'commander'],
            ['operator', password_hash('Password123!', PASSWORD_DEFAULT), 'operator@defence.bd', 'Maj. Dlamini', 'operator'],
            ['analyst', password_hash('Password123!', PASSWORD_DEFAULT), 'analyst@defence.bd', 'Capt. Nkosi', 'analyst'],
            ['viewer', password_hash('Password123!', PASSWORD_DEFAULT), 'viewer@defence.bd', 'Lt. Mamba', 'viewer']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, email, full_name, role) VALUES (?, ?, ?, ?, ?)");
        foreach ($users as $user) {
            $stmt->execute($user);
            echo "   ✅ Created user: {$user[0]}\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
