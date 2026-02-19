<?php
require_once 'includes/session.php'; 
 
if (isset($_SESSION['user_id'])) { header('Location: home_simple.php'); exit; } 
if ($_SERVER['REQUEST_METHOD'] === 'POST') { 
    if ($_POST['username'] === 'admin' && $_POST['password'] === 'admin123') { 
        $_SESSION['user_id'] = 1; 
        $_SESSION['role'] = 'admin'; 
        $_SESSION['username'] = 'admin'; 
        $_SESSION['full_name'] = 'Gen. C.Bartaria'; 
        header('Location: home_simple.php'); 
        exit; 
    } elseif ($_POST['username'] === 'viewer' && $_POST['password'] === 'viewer123') { 
        $_SESSION['user_id'] = 2; 
        $_SESSION['role'] = 'client'; 
        $_SESSION['username'] = 'viewer'; 
        $_SESSION['full_name'] = 'Lt. Khumalo'; 
        header('Location: home_simple.php'); 
        exit; 
    } else { 
        $error = "Invalid login"; 
    } 
} 
?> 
<!DOCTYPE html>
<!-- saved from url=(0044)http://localhost:8080/sentinel/?module=login -->
<html lang="en"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UEDF Sentinel - Login</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: #0a0f1c;
            color: #e0e0e0;
            font-family: 'Share Tech Mono', monospace;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-image: radial-gradient(circle at 50% 50%, rgba(0,255,157,0.1) 0%, transparent 50%);
        }
        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 20px;
        }
        .login-box {
            background: rgba(10,15,28,0.95);
            border: 2px solid #00ff9d;
            padding: 40px;
            box-shadow: 0 0 50px rgba(0,255,157,0.3);
            border-radius: 8px;
        }
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo i {
            font-size: 3rem;
            color: #00ff9d;
        }
        .logo h2 {
            font-family: 'Orbitron', sans-serif;
            color: #fff;
            font-size: 2rem;
            background: linear-gradient(135deg, #00ff9d, #ff006e);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #00ff9d;
        }
        input {
            width: 100%;
            padding: 12px;
            background: rgba(0,0,0,0.5);
            border: 1px solid #00ff9d;
            color: #00ff9d;
            border-radius: 4px;
        }
        button {
            width: 100%;
            padding: 14px;
            background: transparent;
            border: 2px solid #00ff9d;
            color: #00ff9d;
            font-family: 'Orbitron', sans-serif;
            cursor: pointer;
            border-radius: 4px;
        }
        button:hover {
            background: #00ff9d;
            color: #0a0f1c;
        }
        .error {
            color: #ff006e;
            text-align: center;
            margin-bottom: 20px;
        }
        .credentials {
            margin-top: 20px;
            padding: 15px;
            background: rgba(0,0,0,0.3);
            border: 1px dashed #4a5568;
            border-radius: 4px;
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="logo">
                <i class="fas fa-shield-halved"></i>
                <h2>UEDF SENTINEL</h2>
                <p style="color:#ff006e;"> AUTHENTICATION REQUIRED</p>
            </div>
            
                        
            <form method="POST">
                <div class="form-group">
                    <label>USERNAME</label>
                    <input type="text" name="username" required="" autofocus="">
                </div>
                <div class="form-group">
                    <label>PASSWORD</label>
                    <input type="password" name="password" required="">
                </div>
                <button type="submit">LOGIN</button>
            </form>
            
            
        </div>
    </div>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&amp;display=swap" rel="stylesheet">

</body></html>
