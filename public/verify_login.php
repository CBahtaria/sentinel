<?php
echo "🔐 LOGIN SYSTEM VERIFICATION\n";
echo "===========================\n\n";

try {
    $pdo = new PDO("mysql:host=localhost;dbname=bartarian_defence", "root", "");
    
    echo "✅ Database connected\n\n";
    
    // Check what column name is used for password
    $result = $pdo->query("DESCRIBE users");
    $passwordColumn = null;
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        if (strpos($row['Field'], 'password') !== false) {
            $passwordColumn = $row['Field'];
        }
    }
    
    if ($passwordColumn) {
        echo "📋 Password column name: '$passwordColumn'\n\n";
        
        // Test commander login
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = 'commander'");
        $stmt->execute();
        $user = $stmt->fetch();
        
        if ($user) {
            echo "✅ Commander found\n";
            if (password_verify('Password123!', $user[$passwordColumn])) {
                echo "✅ Password is CORRECT using '$passwordColumn'\n";
                echo "   Login will work!\n";
            } else {
                echo "❌ Password is INCORRECT using '$passwordColumn'\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
