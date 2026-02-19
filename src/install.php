<?php
require_once 'includes/session.php';
/**
 * UEDF SENTINEL v5.0 - Installation Wizard
 * UMBUTFO ESWATINI DEFENCE FORCE
 * Complete system installer
 */

// Set time limit
set_time_limit(300);

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
if (session_status() === PHP_SESSION_NONE) {
    
}

// Installation steps
$steps = [
    1 => 'System Check',
    2 => 'Database Setup',
    3 => 'Configuration',
    4 => 'Admin Account',
    5 => 'Complete'
];

$current_step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$error = '';
$success = '';

// Handle installation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($current_step === 1) {
        // System check passed, go to next step
        header('Location: ?step=2');
        exit;
    } elseif ($current_step === 2) {
        // Test database connection
        $host = $_POST['db_host'] ?? 'localhost';
        $name = $_POST['db_name'] ?? 'uedf_sentinel';
        $user = $_POST['db_user'] ?? 'root';
        $pass = $_POST['db_pass'] ?? '';
        
        try {
            $pdo = new PDO("mysql:host=$host", $user, $pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Create database if not exists
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `$name`");
            
            // Save credentials to session
            $_SESSION['db_config'] = [
                'host' => $host,
                'name' => $name,
                'user' => $user,
                'pass' => $pass
            ];
            
            header('Location: ?step=3');
            exit;
        } catch (PDOException $e) {
            $error = "Database connection failed: " . $e->getMessage();
        }
    } elseif ($current_step === 3) {
        // Create tables
        $db_config = $_SESSION['db_config'] ?? null;
        
        if (!$db_config) {
            header('Location: ?step=2');
            exit;
        }
        
        try {
            $pdo = new PDO(
                "mysql:host={$db_config['host']};dbname={$db_config['name']}",
                $db_config['user'],
                $db_config['pass']
            );
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Read and execute schema
            $schema = file_get_contents(__DIR__ . '/database_schema.sql');
            $pdo->exec($schema);
            
            header('Location: ?step=4');
            exit;
        } catch (Exception $e) {
            $error = "Failed to create tables: " . $e->getMessage();
        }
    } elseif ($current_step === 4) {
        // Create admin account
        $username = $_POST['username'] ?? 'commander';
        $password = $_POST['password'] ?? '';
        $full_name = $_POST['full_name'] ?? 'Gen. Bartaria';
        $email = $_POST['email'] ?? 'commander@uedf.sz';
        
        if (strlen($password) < 8) {
            $error = "Password must be at least 8 characters";
        } else {
            $db_config = $_SESSION['db_config'] ?? null;
            
            try {
                $pdo = new PDO(
                    "mysql:host={$db_config['host']};dbname={$db_config['name']}",
                    $db_config['user'],
                    $db_config['pass']
                );
                
                $hash = password_hash($password, PASSWORD_DEFAULT);
                
                $stmt = $pdo->prepare("
                    INSERT INTO users (username, password, full_name, email, role, two_factor_enabled, created_at) 
                    VALUES (?, ?, ?, ?, 'commander', 0, NOW())
                ");
                $stmt->execute([$username, $hash, $full_name, $email]);
                
                // Create config file
                $config_content = "<?php
/**
 * UEDF SENTINEL v5.0 - Database Configuration
 */

define('DB_HOST', '{$db_config['host']}');
define('DB_NAME', '{$db_config['name']}');
define('DB_USER', '{$db_config['user']}');
define('DB_PASS', '{$db_config['pass']}');

try {
    \$pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException \$e) {
    die('Database connection failed: ' . \$e->getMessage());
}
?>";
                
                file_put_contents(__DIR__ . '/config/database.php', $config_content);
                
                // Create install complete flag
                file_put_contents(__DIR__ . '/.installed', date('Y-m-d H:i:s'));
                
                header('Location: ?step=5');
                exit;
            } catch (Exception $e) {
                $error = "Failed to create admin account: " . $e->getMessage();
            }
        }
    }
}

// System checks for step 1
$checks = [];
if ($current_step === 1) {
    $checks = [
        'PHP Version' => [
            'required' => '7.4+',
            'current' => PHP_VERSION,
            'status' => version_compare(PHP_VERSION, '7.4.0', '>=')
        ],
        'PDO Extension' => [
            'required' => 'Yes',
            'current' => extension_loaded('pdo') ? 'Yes' : 'No',
            'status' => extension_loaded('pdo')
        ],
        'PDO MySQL' => [
            'required' => 'Yes',
            'current' => extension_loaded('pdo_mysql') ? 'Yes' : 'No',
            'status' => extension_loaded('pdo_mysql')
        ],
        'JSON Extension' => [
            'required' => 'Yes',
            'current' => extension_loaded('json') ? 'Yes' : 'No',
            'status' => extension_loaded('json')
        ],
        'Session Extension' => [
            'required' => 'Yes',
            'current' => extension_loaded('session') ? 'Yes' : 'No',
            'status' => extension_loaded('session')
        ],
        'OpenSSL Extension' => [
            'required' => 'Yes',
            'current' => extension_loaded('openssl') ? 'Yes' : 'No',
            'status' => extension_loaded('openssl')
        ],
        'GD Extension' => [
            'required' => 'Yes',
            'current' => extension_loaded('gd') ? 'Yes' : 'No',
            'status' => extension_loaded('gd')
        ],
        'CURL Extension' => [
            'required' => 'Yes',
            'current' => extension_loaded('curl') ? 'Yes' : 'No',
            'status' => extension_loaded('curl')
        ],
        'MBString Extension' => [
            'required' => 'Yes',
            'current' => extension_loaded('mbstring') ? 'Yes' : 'No',
            'status' => extension_loaded('mbstring')
        ],
        'Logs Directory' => [
            'required' => 'Writable',
            'current' => is_writable(__DIR__ . '/logs') ? 'Writable' : 'Not writable',
            'status' => is_writable(__DIR__ . '/logs') || mkdir(__DIR__ . '/logs', 0755, true)
        ],
        'Cache Directory' => [
            'required' => 'Writable',
            'current' => is_writable(__DIR__ . '/cache') ? 'Writable' : 'Not writable',
            'status' => is_writable(__DIR__ . '/cache') || mkdir(__DIR__ . '/cache', 0755, true)
        ],
        'Uploads Directory' => [
            'required' => 'Writable',
            'current' => is_writable(__DIR__ . '/uploads') ? 'Writable' : 'Not writable',
            'status' => is_writable(__DIR__ . '/uploads') || mkdir(__DIR__ . '/uploads', 0755, true)
        ]
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UEDF SENTINEL v5.0 - Installation Wizard</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: #0a0f1c;
            color: #e0e0e0;
            font-family: 'Share Tech Mono', monospace;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        .header {
            background: #151f2c;
            border: 2px solid #ff006e;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 10px;
            text-align: center;
        }
        h1 {
            color: #ff006e;
            font-family: 'Orbitron', sans-serif;
        }
        .progress {
            display: flex;
            justify-content: space-between;
            margin: 30px 0;
            position: relative;
        }
        .progress::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 0;
            right: 0;
            height: 2px;
            background: #ff006e40;
            z-index: 1;
        }
        .step {
            position: relative;
            z-index: 2;
            text-align: center;
            flex: 1;
        }
        .step-number {
            width: 30px;
            height: 30px;
            background: #151f2c;
            border: 2px solid #ff006e;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            font-weight: bold;
            color: #ff006e;
        }
        .step.active .step-number {
            background: #ff006e;
            color: #0a0f1c;
        }
        .step.completed .step-number {
            background: #00ff9d;
            border-color: #00ff9d;
            color: #0a0f1c;
        }
        .step-label {
            font-size: 0.8rem;
            color: #a0aec0;
        }
        .step.active .step-label {
            color: #ff006e;
        }
        .content {
            background: #151f2c;
            border: 2px solid #ff006e;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 20px;
        }
        h2 {
            color: #ff006e;
            font-family: 'Orbitron', sans-serif;
            margin-bottom: 20px;
        }
        .check-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #ff006e20;
        }
        .check-label {
            color: #00ff9d;
        }
        .check-status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
        }
        .check-status.pass {
            background: #00ff9d20;
            border: 1px solid #00ff9d;
            color: #00ff9d;
        }
        .check-status.fail {
            background: #ff006e20;
            border: 1px solid #ff006e;
            color: #ff006e;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            color: #ff006e;
            margin-bottom: 5px;
        }
        input, select {
            width: 100%;
            padding: 12px;
            background: #0a0f1c;
            border: 1px solid #ff006e;
            color: #00ff9d;
            border-radius: 5px;
            font-family: 'Share Tech Mono', monospace;
            font-size: 1rem;
        }
        input:focus {
            outline: none;
            border-color: #00ff9d;
        }
        .btn {
            padding: 12px 30px;
            background: #ff006e;
            color: #0a0f1c;
            border: none;
            border-radius: 30px;
            font-family: 'Orbitron', sans-serif;
            font-size: 1rem;
            cursor: pointer;
            transition: 0.3s;
        }
        .btn:hover {
            background: #00ff9d;
            transform: translateY(-2px);
        }
        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .error {
            background: #ff006e20;
            border: 1px solid #ff006e;
            color: #ff006e;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .success {
            background: #00ff9d20;
            border: 1px solid #00ff9d;
            color: #00ff9d;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .info {
            color: #4a5568;
            font-size: 0.9rem;
            margin-top: 5px;
        }
        .button-group {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>UEDF SENTINEL v5.0</h1>
            <p>Installation Wizard</p>
        </div>
        
        <div class="progress">
            <?php foreach ($steps as $num => $label): ?>
            <div class="step <?= $num == $current_step ? 'active' : '' ?> <?= $num < $current_step ? 'completed' : '' ?>">
                <div class="step-number"><?= $num ?></div>
                <div class="step-label"><?= $label ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="content">
            <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
            <div class="success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            
            <?php if ($current_step === 1): ?>
                <h2>System Requirements Check</h2>
                
                <?php foreach ($checks as $name => $check): ?>
                <div class="check-item">
                    <span class="check-label"><?= $name ?> (<?= $check['required'] ?>)</span>
                    <span>
                        <span class="check-status <?= $check['status'] ? 'pass' : 'fail' ?>">
                            <?= $check['current'] ?>
                        </span>
                    </span>
                </div>
                <?php endforeach; ?>
                
                <?php
                $all_passed = true;
                foreach ($checks as $check) {
                    if (!$check['status']) {
                        $all_passed = false;
                        break;
                    }
                }
                ?>
                
                <form method="POST" style="margin-top: 30px;">
                    <div class="button-group">
                        <button type="submit" class="btn" <?= !$all_passed ? 'disabled' : '' ?>>
                            Continue to Step 2 →
                        </button>
                    </div>
                </form>
                
            <?php elseif ($current_step === 2): ?>
                <h2>Database Configuration</h2>
                
                <form method="POST">
                    <div class="form-group">
                        <label>Database Host</label>
                        <input type="text" name="db_host" value="localhost" required>
                        <div class="info">Usually 'localhost'</div>
                    </div>
                    
                    <div class="form-group">
                        <label>Database Name</label>
                        <input type="text" name="db_name" value="uedf_sentinel" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Database Username</label>
                        <input type="text" name="db_user" value="root" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Database Password</label>
                        <input type="password" name="db_pass" value="">
                        <div class="info">Leave empty if no password</div>
                    </div>
                    
                    <div class="button-group">
                        <a href="?step=1" class="btn" style="background: transparent; border: 1px solid #ff006e; color: #ff006e;">Back</a>
                        <button type="submit" class="btn">Create Database →</button>
                    </div>
                </form>
                
            <?php elseif ($current_step === 3): ?>
                <h2>Creating Tables</h2>
                
                <p style="margin-bottom: 20px;">Creating database tables... This may take a moment.</p>
                
                <div style="text-align: center;">
                    <div class="btn" style="width: 200px; margin: 0 auto;" onclick="window.location.href='?step=4'">
                        Continue Manually
                    </div>
                </div>
                
                <script>
                    setTimeout(function() {
                        window.location.href = '?step=4';
                    }, 3000);
                </script>
                
            <?php elseif ($current_step === 4): ?>
                <h2>Create Admin Account</h2>
                
                <form method="POST">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" value="commander" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="full_name" value="Gen. Bartaria" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="commander@uedf.sz" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" value="" required>
                        <div class="info">Minimum 8 characters</div>
                    </div>
                    
                    <div class="button-group">
                        <a href="?step=3" class="btn" style="background: transparent; border: 1px solid #ff006e; color: #ff006e;">Back</a>
                        <button type="submit" class="btn">Complete Installation →</button>
                    </div>
                </form>
                
            <?php elseif ($current_step === 5): ?>
                <h2>Installation Complete!</h2>
                
                <p style="margin-bottom: 20px;">UEDF SENTINEL v5.0 has been successfully installed.</p>
                
                <div style="background: #0a0f1c; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
                    <h3 style="color: #00ff9d; margin-bottom: 10px;">Login Credentials:</h3>
                    <p><strong style="color: #ff006e;">Username:</strong> commander</p>
                    <p><strong style="color: #ff006e;">Password:</strong> [the password you set]</p>
                </div>
                
                <div class="button-group">
                    <a href="?module=login" class="btn">Go to Login →</a>
                </div>
                
                <!-- Delete install.php for security -->
                <?php
                // Uncomment in production
                // unlink(__FILE__);
                ?>
            <?php endif; ?>
        </div>
        
        <div style="text-align: center; color: #4a5568; margin-top: 20px;">
            UMBUTFO ESWATINI DEFENCE FORCE
        </div>
    </div>
</body>
</html>
