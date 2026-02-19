<?php
// Quick login test
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login Test</title>
    <style>
        body { background: #0a0f1c; color: #00ff9d; font-family: monospace; padding: 20px; }
        .success { color: #00ff9d; }
        .error { color: #ff006e; }
        pre { background: #151f2c; padding: 10px; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>?? Login System Test</h1>
    
    <?php
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=bartarian_defence", "root", "");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        echo "<h2>? Database Connection: SUCCESS</h2>";
        
        // Check users
        $users = $pdo->query("SELECT username, role FROM users")->fetchAll();
        echo "<h3>Users in database:</h3><pre>";
        foreach ($users as $user) {
            echo "• {$user['username']} - Role: {$user['role']}\n";
        }
        echo "</pre>";
        
        // Test commander password
        $stmt = $pdo->prepare("SELECT password FROM users WHERE username = 'commander'");
        $stmt->execute();
        $user = $stmt->fetch();
        
        if ($user) {
            if (password_verify('Password123!', $user['password'])) {
                echo "<h3 class='success'>? Commander password: CORRECT</h3>";
            } else {
                echo "<h3 class='error'>? Commander password: INCORRECT</h3>";
            }
        }
        
    } catch (Exception $e) {
        echo "<h2 class='error'>? Error: " . $e->getMessage() . "</h2>";
    }
    ?>
    
    <p><a href="login.php" style="color: #ff006e;">Go to Login Page ?</a></p>
</body>
</html>

