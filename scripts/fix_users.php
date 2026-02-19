<?php
// Fix users table and role data
try {
    $pdo = new PDO("mysql:host=localhost;dbname=bartarian_defence", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "🔧 BARTARIAN DEFENCE - DATABASE FIX\n";
    echo "===================================\n\n";
    
    // Check if users table exists
    $result = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($result->rowCount() == 0) {
        echo "Creating users table...\n";
        
        $pdo->exec("
            CREATE TABLE users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) UNIQUE NOT NULL,
                password_hash VARCHAR(255) NOT NULL,
                email VARCHAR(100) UNIQUE,
                full_name VARCHAR(100),
                role ENUM('viewer','analyst','operator','commander','admin','superadmin') DEFAULT 'viewer',
                department VARCHAR(50),
                last_login DATETIME,
                last_login_ip VARCHAR(45),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                is_active BOOLEAN DEFAULT TRUE,
                INDEX idx_username (username),
                INDEX idx_role (role)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        echo "✅ Users table created\n";
    } else {
        echo "✅ Users table exists\n";
    }
    
    // Check if we have users
    $count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    echo "Current users in database: $count\n";
    
    if ($count == 0) {
        // Insert default users with proper roles
        echo "Adding default users...\n";
        
        $users = [
            ['commander', password_hash('Password123!', PASSWORD_DEFAULT), 'commander@defence.bd', 'Gen. Bartaria', 'commander', 'Command'],
            ['operator', password_hash('Password123!', PASSWORD_DEFAULT), 'operator@defence.bd', 'Maj. Dlamini', 'operator', 'Operations'],
            ['analyst', password_hash('Password123!', PASSWORD_DEFAULT), 'analyst@defence.bd', 'Capt. Nkosi', 'analyst', 'Intelligence'],
            ['viewer', password_hash('Password123!', PASSWORD_DEFAULT), 'viewer@defence.bd', 'Lt. Mamba', 'viewer', 'Monitoring']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, email, full_name, role, department) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($users as $user) {
            $stmt->execute($user);
        }
        echo "✅ Added 4 users with proper roles\n";
    } else {
        // Update existing users to ensure they have proper roles
        echo "Updating existing users...\n";
        
        $updates = [
            ['commander', 'commander', 'Gen. Bartaria', 'Command'],
            ['operator', 'operator', 'Maj. Dlamini', 'Operations'],
            ['analyst', 'analyst', 'Capt. Nkosi', 'Intelligence'],
            ['viewer', 'viewer', 'Lt. Mamba', 'Monitoring']
        ];
        
        $stmt = $pdo->prepare("UPDATE users SET role = ?, full_name = ?, department = ? WHERE username = ?");
        foreach ($updates as $update) {
            $stmt->execute([$update[1], $update[2], $update[3], $update[0]]);
        }
        echo "✅ User roles updated\n";
    }
    
    // Verify users
    echo "\n📋 Current users in database:\n";
    $users = $pdo->query("SELECT id, username, role, full_name, department FROM users")->fetchAll();
    foreach ($users as $user) {
        echo "   • {$user['username']} (ID: {$user['id']}) - Role: {$user['role']} - {$user['full_name']} ({$user['department']})\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n✅ Database fix complete!\n";
