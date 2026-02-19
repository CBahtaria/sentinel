<?php
require_once '../src/session.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    try {
        // Connect to database
        $pdo = new PDO("mysql:host=localhost;dbname=uedf_sentinel;charset=utf8mb4", "root", "");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Get user from database
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            // Set session with all user data
            setUserSession($user);
            
            // Update last login
            $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $updateStmt->execute([$user['id']]);
            
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Invalid username or password';
        }
    } catch (PDOException $e) {
        $error = 'Database error: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>BARTARIAN DEFENCE - Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: #0a0f1c;
            font-family: 'Share Tech Mono', monospace;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        .login-box {
            background: #151f2c;
            border: 2px solid #ff006e;
            border-radius: 10px;
            padding: 40px;
            width: 100%;
            max-width: 400px;
        }
        h1 {
            color: #ff006e;
            font-family: 'Orbitron', sans-serif;
            text-align: center;
            margin-bottom: 30px;
        }
        .input-group {
            margin-bottom: 20px;
        }
        label {
            color: #00ff9d;
            display: block;
            margin-bottom: 5px;
        }
        input {
            width: 100%;
            padding: 12px;
            background: #0a0f1c;
            border: 1px solid #ff006e;
            color: #00ff9d;
            border-radius: 5px;
            font-family: 'Share Tech Mono', monospace;
        }
        button {
            width: 100%;
            padding: 12px;
            background: #ff006e;
            color: #0a0f1c;
            border: none;
            border-radius: 5px;
            font-family: 'Orbitron', sans-serif;
            font-size: 16px;
            cursor: pointer;
            margin-top: 20px;
        }
        button:hover {
            background: #00ff9d;
        }
        .error {
            color: #ff006e;
            text-align: center;
            margin: 10px 0;
            padding: 10px;
            background: rgba(255,0,110,0.1);
            border-radius: 5px;
        }
        .info {
            text-align: center;
            margin-top: 20px;
            color: #4a5568;
            font-size: 14px;
        }
        .credentials {
            margin-top: 20px;
            padding: 15px;
            background: #0a0f1c;
            border-radius: 5px;
            border: 1px solid #ff006e;
        }
        .cred-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            border-bottom: 1px solid #ff006e40;
        }
        .cred-row:last-child {
            border-bottom: none;
        }
        .cred-user {
            color: #ff006e;
        }
        .cred-pass {
            color: #00ff9d;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h1>BARTARIAN DEFENCE v5.0</h1>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="input-group">
                <label>Username</label>
                <input type="text" name="username" value="commander" required>
            </div>

            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" value="Password123!" required>
            </div>

            <button type="submit">LOGIN</button>
        </form>

        <div class="credentials">
            <div class="cred-row">
                <span class="cred-user">commander</span>
                <span class="cred-pass">Password123!</span>
            </div>
            <div class="cred-row">
                <span class="cred-user">operator</span>
                <span class="cred-pass">Password123!</span>
            </div>
            <div class="cred-row">
                <span class="cred-user">analyst</span>
                <span class="cred-pass">Password123!</span>
            </div>
            <div class="cred-row">
                <span class="cred-user">viewer</span>
                <span class="cred-pass">Password123!</span>
            </div>
        </div>

        <div class="info">
            BARTARIAN DEFENCE FORCE<br>
            © 2026 | CLASSIFIED SYSTEM
        </div>
    </div>
</body>
</html>

