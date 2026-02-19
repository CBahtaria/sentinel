<?php
require_once '../includes/session.php';
/**
 * UEDF SENTINEL - User Management with Email Alerts
 */


if (!isset($_SESSION['user_id'])) {
    header('Location: ?module=login');
    exit;
}

// Only commander can manage users
if ($_SESSION['role'] !== 'commander') {
    header('Location: ?module=home');
    exit;
}

require_once '../config/database.php';
require_once '../includes/mailer.php';

$db = Database::getInstance()->getConnection();
$message = '';
$error = '';

// Handle user creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $full_name = trim($_POST['full_name']);
    $role = $_POST['role'];
    $email = trim($_POST['email']);
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address';
    } else {
        try {
            // Check if username exists
            $check = $db->prepare("SELECT id FROM users WHERE username = ?");
            $check->execute([$username]);
            if ($check->fetch()) {
                $error = 'Username already exists';
            } else {
                // Insert new user
                $stmt = $db->prepare("INSERT INTO users (username, password, full_name, role, email) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$username, $password, $full_name, $role, $email]);
                
                // Send welcome email
                $subject = "Welcome to UEDF SENTINEL";
                $message_body = "
                <html>
                <body style='font-family: Share Tech Mono; background: #0a0f1c; color: #e0e0e0; padding: 20px;'>
                    <div style='max-width: 600px; margin: 0 auto; background: #151f2c; border: 2px solid #00ff9d; border-radius: 8px; padding: 30px;'>
                        <h1 style='color: #00ff9d; text-align: center;'>Welcome to UEDF SENTINEL</h1>
                        <p>Dear <strong>$full_name</strong>,</p>
                        <p>Your account has been created in the UEDF SENTINEL command system.</p>
                        
                        <div style='background: #0a0f1c; border-left: 4px solid #ff006e; padding: 20px; margin: 20px 0;'>
                            <p><strong>Username:</strong> $username</p>
                            <p><strong>Password:</strong> $password</p>
                            <p><strong>Role:</strong> " . strtoupper($role) . "</p>
                        </div>
                        
                        <p>Access the command center:</p>
                        <p><a href='http://172.20.10.3:8080/sentinel/?module=login' style='color: #00ff9d;'>http://172.20.10.3:8080/sentinel/</a></p>
                        
                        <p>You will receive threat alerts and daily reports at this email address.</p>
                        
                        <hr style='border-color: #ff006e40;'>
                        <p style='color: #a0aec0; font-size: 12px; text-align: center;'>UEDF SENTINEL v4.0</p>
                    </div>
                </body>
                </html>
                ";
                
                Mailer::send($email, $subject, $message_body);
                
                $message = "User created successfully and welcome email sent!";
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}

// Handle user deletion
if (isset($_GET['delete'])) {
    $user_id = (int)$_GET['delete'];
    if ($user_id != 1) { // Prevent deleting the main commander
        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $message = "User deleted successfully";
    }
}

// Handle test email
if (isset($_GET['test_email'])) {
    $user_id = (int)$_GET['test_email'];
    $stmt = $db->prepare("SELECT email, full_name FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        $result = Mailer::sendTest($user['email']);
        if ($result['success']) {
            $message = "Test email sent to {$user['full_name']}";
        } else {
            $error = "Failed to send test email: " . $result['message'];
        }
    }
}

// Get all users
$users = $db->query("SELECT id, username, full_name, role, email, last_login FROM users ORDER BY id")->fetchAll();

// Get email statistics
$email_stats = [
    'total_users' => count($users),
    'with_email' => count(array_filter($users, function($u) { return !empty($u['email']); }))
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UEDF SENTINEL - User Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&family=Share+Tech+Mono&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Share Tech Mono', monospace; }
        body {
            background: #0a0f1c;
            color: #e0e0e0;
            padding: 20px;
        }
        .header {
            background: #151f2c;
            border: 2px solid #ff006e;
            padding: 20px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 8px;
        }
        .header h1 {
            font-family: 'Orbitron', sans-serif;
            color: #ff006e;
        }
        .back-btn {
            padding: 10px 20px;
            background: transparent;
            border: 1px solid #00ff9d;
            color: #00ff9d;
            text-decoration: none;
            border-radius: 4px;
        }
        .stats-bar {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }
        .stat-card {
            background: #151f2c;
            border: 1px solid #00ff9d;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        .stat-value {
            font-size: 2rem;
            color: #00ff9d;
            font-family: 'Orbitron', sans-serif;
        }
        .message {
            background: #00ff9d20;
            border: 1px solid #00ff9d;
            color: #00ff9d;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .error {
            background: #ff006e20;
            border: 1px solid #ff006e;
            color: #ff006e;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .add-user-form {
            background: #151f2c;
            border: 1px solid #00ff9d;
            padding: 30px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .form-title {
            color: #00ff9d;
            font-family: 'Orbitron', sans-serif;
            margin-bottom: 20px;
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            color: #00ff9d;
            margin-bottom: 5px;
            font-size: 0.9rem;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 12px;
            background: #0a0f1c;
            border: 1px solid #ff006e;
            color: #00ff9d;
            border-radius: 4px;
            font-size: 1rem;
        }
        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: #00ff9d;
        }
        .btn {
            padding: 12px 30px;
            background: #ff006e;
            border: none;
            color: white;
            cursor: pointer;
            border-radius: 4px;
            font-family: 'Orbitron', sans-serif;
            font-size: 1rem;
        }
        .btn:hover {
            background: #00ff9d;
            color: #0a0f1c;
        }
        .users-table {
            background: #151f2c;
            border: 1px solid #ff006e;
            border-radius: 8px;
            overflow: hidden;
        }
        .table-header {
            display: grid;
            grid-template-columns: 0.5fr 1.5fr 2fr 1fr 2fr 1.5fr 1fr;
            padding: 15px;
            background: #ff006e20;
            color: #ff006e;
            font-family: 'Orbitron', sans-serif;
            font-weight: bold;
            border-bottom: 1px solid #ff006e;
        }
        .table-row {
            display: grid;
            grid-template-columns: 0.5fr 1.5fr 2fr 1fr 2fr 1.5fr 1fr;
            padding: 15px;
            border-bottom: 1px solid #ff006e40;
            align-items: center;
        }
        .table-row:hover {
            background: #ff006e10;
        }
        .role-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            text-align: center;
            display: inline-block;
        }
        .role-commander { background: #ff006e40; color: #ff006e; border: 1px solid #ff006e; }
        .role-operator { background: #ffbe0b40; color: #ffbe0b; border: 1px solid #ffbe0b; }
        .role-analyst { background: #4cc9f040; color: #4cc9f0; border: 1px solid #4cc9f0; }
        .role-viewer { background: #a0aec040; color: #a0aec0; border: 1px solid #a0aec0; }
        .action-btn {
            padding: 5px 10px;
            background: transparent;
            border: 1px solid #00ff9d;
            color: #00ff9d;
            cursor: pointer;
            border-radius: 4px;
            margin: 0 3px;
            font-size: 0.8rem;
        }
        .action-btn:hover {
            background: #00ff9d;
            color: #0a0f1c;
        }
        .action-btn.delete {
            border-color: #ff006e;
            color: #ff006e;
        }
        .action-btn.delete:hover {
            background: #ff006e;
            color: white;
        }
        .email-badge {
            color: #4cc9f0;
            font-size: 0.8rem;
        }
        .float-ai {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #ff006e, #00ff9d);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border: 2px solid white;
            z-index: 9999;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1><i class="fas fa-users"></i> USER MANAGEMENT</h1>
        <div>
            <span style="color: #00ff9d; margin-right: 15px;">
                <i class="fas fa-envelope"></i> Email System: ACTIVE
            </span>
            <a href="?module=home" class="back-btn"><i class="fas fa-arrow-left"></i> BACK</a>
        </div>
    </div>

    <div class="stats-bar">
        <div class="stat-card">
            <div class="stat-value"><?= $email_stats['total_users'] ?></div>
            <div class="stat-label">TOTAL USERS</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color: #4cc9f0;"><?= $email_stats['with_email'] ?></div>
            <div class="stat-label">EMAILS CONFIGURED</div>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="message"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="error"><i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="add-user-form">
        <h2 class="form-title"><i class="fas fa-user-plus"></i> ADD NEW TEAM MEMBER</h2>
        <form method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label><i class="fas fa-user"></i> USERNAME</label>
                    <input type="text" name="username" required placeholder="e.g., operator2">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-lock"></i> PASSWORD</label>
                    <input type="text" name="password" required placeholder="Temporary password">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-id-card"></i> FULL NAME</label>
                    <input type="text" name="full_name" required placeholder="e.g., Lt. Dlamini">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-envelope"></i> EMAIL</label>
                    <input type="email" name="email" required placeholder="For alerts and reports">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-shield-alt"></i> ROLE</label>
                    <select name="role">
                        <option value="operator">Operator</option>
                        <option value="analyst">Analyst</option>
                        <option value="viewer">Viewer</option>
                    </select>
                </div>
                <div class="form-group" style="display: flex; align-items: flex-end;">
                    <button type="submit" name="add_user" class="btn"><i class="fas fa-plus"></i> CREATE USER</button>
                </div>
            </div>
        </form>
    </div>

    <div class="users-table">
        <div class="table-header">
            <div>ID</div>
            <div>USERNAME</div>
            <div>FULL NAME</div>
            <div>ROLE</div>
            <div>EMAIL</div>
            <div>LAST LOGIN</div>
            <div>ACTIONS</div>
        </div>
        
        <?php foreach ($users as $user): ?>
        <div class="table-row">
            <div>#<?= $user['id'] ?></div>
            <div><i class="fas fa-user" style="color: #00ff9d;"></i> <?= htmlspecialchars($user['username']) ?></div>
            <div><?= htmlspecialchars($user['full_name']) ?></div>
            <div>
                <span class="role-badge role-<?= $user['role'] ?>">
                    <?= strtoupper($user['role']) ?>
                </span>
            </div>
            <div>
                <?php if (!empty($user['email'])): ?>
                    <span class="email-badge"><i class="fas fa-envelope"></i> <?= htmlspecialchars($user['email']) ?></span>
                <?php else: ?>
                    <span style="color: #ff006e;">No email</span>
                <?php endif; ?>
            </div>
            <div style="color: #a0aec0;"><?= $user['last_login'] ?? 'Never' ?></div>
            <div>
                <?php if (!empty($user['email'])): ?>
                    <a href="?test_email=<?= $user['id'] ?>" class="action-btn" title="Send test email"><i class="fas fa-paper-plane"></i></a>
                <?php endif; ?>
                <?php if ($user['id'] != 1): ?>
                    <a href="?delete=<?= $user['id'] ?>" class="action-btn delete" onclick="return confirm('Delete this user?')"><i class="fas fa-trash"></i></a>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="float-ai" onclick="window.location.href='?module=ai-assistant'">
        <i class="fas fa-robot" style="color: white; font-size: 24px;"></i>
    </div>
</body>
</html>
