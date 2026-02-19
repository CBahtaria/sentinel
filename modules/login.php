<?php
require_once __DIR__ . '/../includes/session.php';
/**
 * BARTARIA DEFENSE SYSTEM - Login Module
 */

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: ?module=home');
    exit;
}

$error = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // For testing - you can replace with database verification later
    if ($username === 'commander' && $password === 'commander123') {
        $_SESSION['user_id'] = 1;
        $_SESSION['username'] = 'commander';
        $_SESSION['full_name'] = 'Charles Bartaria';
        $_SESSION['role'] = 'commander';
        $_SESSION['two_factor_enabled'] = false;
        
        header('Location: ?module=home');
        exit;
    } else {
        $error = 'Invalid credentials';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BARTARIA DEFENSE - LOGIN</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&family=Share+Tech+Mono&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            background: #0a0f1c;
            color: #e0e0e0;
            font-family: 'Share Tech Mono', monospace;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-image: radial-gradient(circle at 10% 20%, rgba(255,0,110,0.05) 0%, transparent 20%),
                              radial-gradient(circle at 90% 80%, rgba(0,255,157,0.05) 0%, transparent 20%);
            padding: 20px;
        }
        
        .login-container {
            background: rgba(21,31,44,0.95);
            border: 2px solid #ff006e;
            border-radius: 12px;
            padding: 40px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 0 30px rgba(255,0,110,0.3);
            backdrop-filter: blur(10px);
        }
        
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo i {
            font-size: 3rem;
            color: #ff006e;
            filter: drop-shadow(0 0 10px #ff006e);
            animation: pulse 2s infinite;
        }
        
        .logo h1 {
            font-family: 'Orbitron', sans-serif;
            color: #00ff9d;
            font-size: 1.8rem;
            margin-top: 10px;
            text-shadow: 0 0 10px #00ff9d;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            color: #ff006e;
            margin-bottom: 8px;
            font-size: 0.9rem;
            letter-spacing: 1px;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            background: #0a0f1c;
            border: 1px solid #ff006e;
            color: #fff;
            border-radius: 6px;
            font-family: 'Share Tech Mono', monospace;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #00ff9d;
            box-shadow: 0 0 15px rgba(0,255,157,0.3);
        }
        
        .login-btn {
            width: 100%;
            padding: 12px;
            background: #ff006e;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-family: 'Orbitron', sans-serif;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
            position: relative;
            overflow: hidden;
        }
        
        .login-btn:hover {
            background: #00ff9d;
            color: #0a0f1c;
            box-shadow: 0 0 20px #00ff9d;
        }
        
        .login-btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255,255,255,0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }
        
        .login-btn:hover::before {
            width: 300px;
            height: 300px;
        }
        
        .error-message {
            color: #ff006e;
            text-align: center;
            margin-top: 15px;
            padding: 10px;
            border: 1px solid #ff006e;
            border-radius: 6px;
            background: rgba(255,0,110,0.1);
        }
        
        .info-box {
            margin-top: 25px;
            padding: 15px;
            background: #0a0f1c;
            border-radius: 6px;
            border: 1px solid #ff006e40;
            font-size: 0.85rem;
            color: #a0aec0;
        }
        
        .info-box p {
            margin: 5px 0;
        }
        
        .info-box i {
            color: #00ff9d;
            width: 20px;
        }
        
        .footer {
            margin-top: 20px;
            text-align: center;
            color: #4a5568;
            font-size: 0.75rem;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        @media (max-width: 480px) {
            .login-container {
                padding: 30px 20px;
            }
            
            .logo h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <i class="fas fa-shield-halved"></i>
            <h1>BARTARIA DEFENSE</h1>
        </div>
        
        <form method="POST" action="">
            <div class="form-group">
                <label><i class="fas fa-user"></i> USERNAME</label>
                <input type="text" name="username" value="commander" placeholder="Enter username" autocomplete="off">
            </div>
            
            <div class="form-group">
                <label><i class="fas fa-lock"></i> PASSWORD</label>
                <input type="password" name="password" value="commander123" placeholder="Enter password">
            </div>
            
            <button type="submit" class="login-btn">
                <i class="fas fa-sign-in-alt"></i> ACCESS TERMINAL
            </button>
            
            <?php if ($error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
        </form>
        
        <div class="info-box">
            <p><i class="fas fa-crown"></i> Commander Access</p>
            <p><i class="fas fa-user"></i> username: <span style="color:#00ff9d;">commander</span></p>
            <p><i class="fas fa-key"></i> password: <span style="color:#00ff9d;">commander123</span></p>
        </div>
        
        <div class="footer">
            <p>© <?= date('Y') ?> BARTARIA DEFENSE</p>
            <p>Named after Charles Bartaria</p>
        </div>
    </div>
</body>
</html>