<?php
require_once 'includes/session.php';
if (session_status() === PHP_SESSION_NONE) {
    
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ?module=login&redirect=quick-access');
    exit;
}

$access = $_SESSION['role'] ?? 'client';
$username = $_SESSION['username'] ?? 'OPERATOR';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UEDF - Quick Access Terminal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Share Tech Mono', monospace; }
        body { background: #0a0f1c; color: #00ff9d; padding: 20px; }
        .header { 
            background: #151f2c; 
            border-bottom: 2px solid #00ff9d; 
            padding: 20px; 
            margin-bottom: 30px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center;
        }
        .header h1 { 
            color: #00ff9d; 
            font-family: 'Orbitron', sans-serif; 
        }
        .header a { 
            color: #ff006e; 
            text-decoration: none; 
            margin-left: 15px;
        }
        .header a:hover {
            color: #00ff9d;
        }
        .search { 
            background: #151f2c; 
            border: 1px solid #00ff9d; 
            padding: 15px; 
            margin-bottom: 30px; 
            display: flex; 
            align-items: center;
        }
        .search i { margin-right: 10px; color: #00ff9d; }
        .search input { 
            flex:1; 
            background:transparent; 
            border:none; 
            color:#00ff9d; 
            font-size:1rem;
            outline: none;
        }
        .search .shortcut { color:#4a5568; }
        .grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(200px,1fr)); 
            gap: 20px; 
            margin-bottom: 30px;
        }
        .card { 
            background: #151f2c; 
            border: 1px solid #00ff9d; 
            padding: 20px; 
            text-decoration: none; 
            color: inherit; 
            transition: 0.3s; 
            display: block;
        }
        .card:hover { 
            border-color: #ff006e; 
            transform: translateY(-5px); 
        }
        .card i { 
            font-size: 2rem; 
            color: #00ff9d; 
            margin-bottom: 10px; 
        }
        .card:hover i { 
            color: #ff006e; 
        }
        .card h3 { 
            margin-bottom: 5px; 
        }
        .back-link { 
            margin-top: 30px; 
            display: inline-block; 
            color: #00ff9d; 
            text-decoration: none;
            padding: 10px 20px;
            border: 1px solid #00ff9d;
        }
        .back-link:hover {
            background: #00ff9d;
            color: #0a0f1c;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1><i class="fas fa-bolt"></i> QUICK ACCESS TERMINAL</h1>
        <div>
            <span style="color:#00ff9d;"><?= $username ?></span>
            <a href="?module=home">Home</a>
            <a href="?module=dashboard&access=<?= $access ?>">Dashboard</a>
        </div>
    </div>
    
    <div class="search">
        <i class="fas fa-search"></i>
        <input type="text" placeholder="Search modules... (Ctrl+K)" id="search">
        <span class="shortcut">Ctrl+K</span>
    </div>
    
    <div class="grid">
        <a href="?module=dashboard&access=admin" class="card">
            <i class="fas fa-crown"></i>
            <h3>Command</h3>
            <small style="color:#a0aec0;">Admin Access</small>
        </a>
        <a href="?module=dashboard&access=client" class="card">
            <i class="fas fa-user-shield"></i>
            <h3>Client</h3>
            <small style="color:#a0aec0;">Read Only</small>
        </a>
        <a href="?module=concurrency" class="card">
            <i class="fas fa-brain"></i>
            <h3>Threats</h3>
            <small style="color:#a0aec0;">v4.0</small>
        </a>
        <a href="?module=audit" class="card">
            <i class="fas fa-history"></i>
            <h3>Audit</h3>
            <small style="color:#a0aec0;">Logs</small>
        </a>
        <a href="?module=map" class="card">
            <i class="fas fa-map"></i>
            <h3>Intel</h3>
            <small style="color:#a0aec0;">Map</small>
        </a>
        <a href="?module=reports" class="card">
            <i class="fas fa-chart-line"></i>
            <h3>Reports</h3>
            <small style="color:#a0aec0;">Analytics</small>
        </a>
        <a href="?module=api&action=nodes" class="card">
            <i class="fas fa-plug"></i>
            <h3>API</h3>
            <small style="color:#a0aec0;">Gateway</small>
        </a>
        <?php if ($access === 'admin'): ?>
        <a href="?module=admin" class="card">
            <i class="fas fa-cog"></i>
            <h3>Admin</h3>
            <small style="color:#a0aec0;">Panel</small>
        </a>
        <?php endif; ?>
    </div>
    
    <div>
        <a href="?module=home" class="back-link"><i class="fas fa-arrow-left"></i> Back to Home</a>
    </div>
    
    <script>
        document.getElementById('search').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                const cmd = this.value.toLowerCase();
                if (cmd.includes('admin')) window.location.href='?module=dashboard&access=admin';
                else if (cmd.includes('client')) window.location.href='?module=dashboard&access=client';
                else if (cmd.includes('threat')) window.location.href='?module=concurrency';
                else if (cmd.includes('audit')) window.location.href='?module=audit';
                else if (cmd.includes('map') || cmd.includes('intel')) window.location.href='?module=map';
                else if (cmd.includes('report')) window.location.href='?module=reports';
                else if (cmd.includes('api')) window.location.href='?module=api&action=nodes';
                else if (cmd.includes('home')) window.location.href='?module=home';
            }
        });
        
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key.toLowerCase() === 'k') {
                e.preventDefault();
                document.getElementById('search').focus();
            }
        });
    </script>
</body>
</html>
