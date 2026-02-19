<!DOCTYPE html>
<html>
<head>
    <title>Simple Login Test</title>
    <style>
        body { background: #0a0f1c; color: #00ff9d; font-family: monospace; padding: 20px; }
        .success { color: #00ff9d; }
        .error { color: #ff006e; }
        form { margin: 20px 0; }
        input { 
            background: #151f2c; 
            border: 1px solid #ff006e; 
            color: #00ff9d; 
            padding: 10px; 
            margin: 5px;
            font-family: monospace;
        }
        button { 
            background: #ff006e; 
            color: #0a0f1c; 
            border: none; 
            padding: 10px 20px; 
            cursor: pointer;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <h1>?? Simple Login Test</h1>
    
    <?php
    session_start();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $pdo = new PDO("mysql:host=localhost;dbname=bartarian_defence", "root", "");
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$_POST['username']]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($_POST['password'], $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                echo "<div class='success'>? Login successful!</div>";
                echo "<p>Welcome, " . htmlspecialchars($user['full_name'] ?? $user['username']) . "</p>";
                echo "<p><a href='dashboard.php'>Go to Dashboard</a></p>";
            } else {
                echo "<div class='error'>? Login failed</div>";
            }
        } catch (Exception $e) {
            echo "<div class='error'>? Error: " . $e->getMessage() . "</div>";
        }
    }
    ?>
    
    <form method="POST">
        <div>
            <input type="text" name="username" placeholder="Username" value="commander">
        </div>
        <div>
            <input type="password" name="password" placeholder="Password" value="Password123!">
        </div>
        <button type="submit">Test Login</button>
    </form>
    
    <p><a href="login.php" style="color: #ff006e;">Go to main login page</a></p>
</body>
</html>

