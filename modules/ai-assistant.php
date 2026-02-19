<?php
require_once '../includes/session.php';
/**
 * UEDF SENTINEL v5.0 - AI Assistant Module
 * Unified AI interface with command processing and interactive UI
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    
}

// Authentication check
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// Get user data for personalization
$username = $_SESSION['username'] ?? 'Commander';
$role = $_SESSION['role'] ?? 'operator';
$user_id = $_SESSION['user_id'];

// Define AI Assistant class (can be moved to separate file in production)
class AIAssistant {
    private $commands = [];
    private $responses = [];
    private $user_role;
    private $user_name;
    
    public function __construct($role = 'operator', $name = 'Commander') {
        $this->user_role = $role;
        $this->user_name = $name;
        $this->initializeCommands();
        $this->initializeResponses();
    }
    
    private function initializeCommands() {
        $this->commands = [
            'show drones' => ['action' => 'redirect', 'url' => '?module=drones'],
            'show map' => ['action' => 'redirect', 'url' => '?module=map'],
            'show threats' => ['action' => 'redirect', 'url' => '?module=threats'],
            'dashboard' => ['action' => 'redirect', 'url' => '?module=home'],
            'settings' => ['action' => 'redirect', 'url' => 'settings_page.php'],
            'enterprise' => ['action' => 'redirect', 'url' => '?module=enterprise'],
            'history' => ['action' => 'redirect', 'url' => '?module=history'],
            'help' => ['action' => 'message', 'message' => 'Available commands: show drones, show map, show threats, dashboard, settings, enterprise, history, system status, weather, time, help'],
            'time' => ['action' => 'message', 'message' => 'Current system time: ' . date('H:i:s')],
            'weather' => ['action' => 'message', 'message' => 'Weather data: Clear skies, visibility 10km, wind 5 knots'],
            'system status' => ['action' => 'message', 'message' => 'All systems operational. CPU: 45%, Memory: 62%, Network: Stable'],
            'who am i' => ['action' => 'message', 'message' => "You are {$this->user_name}, ranked as {$this->user_role}"],
            'clear' => ['action' => 'clear', 'message' => 'Screen cleared'],
            'logout' => ['action' => 'redirect', 'url' => '../logout.php'],
            'exit' => ['action' => 'redirect', 'url' => '?module=home']
        ];
    }
    
    private function initializeResponses() {
        $this->responses = [
            'greetings' => [
                "Welcome back, {$this->user_name}. How may I assist?",
                "At your service, {$this->user_name}. Awaiting commands.",
                "Systems online. Ready for your command, {$this->user_name}.",
                "Good to see you, {$this->user_name}. What's our mission?",
                "AI Assistant active. How can I help you today, {$this->user_name}?"
            ],
            'fallback' => [
                "Command not recognized. Type 'help' for available commands.",
                "I don't understand that command. Try 'help' for assistance.",
                "Unable to process. Please check your command syntax.",
                "Command unknown. Available commands listed under 'help'."
            ],
            'role_based' => [
                'commander' => [
                    "Welcome, Commander. All systems ready for your orders.",
                    "Command authority verified. What are your orders?",
                    "Strategic systems online. Awaiting your command."
                ],
                'operator' => [
                    "Operator access granted. How can I assist?",
                    "Systems ready for operation. Enter your command.",
                    "Tactical systems online. Awaiting instructions."
                ],
                'admin' => [
                    "Administrator access confirmed. Full system control available.",
                    "Welcome, Admin. All systems at your command.",
                    "Root access granted. What would you like to configure?"
                ]
            ]
        ];
    }
    
    public function processCommand($command) {
        $command = strtolower(trim($command));
        
        // Check for exact matches
        if (isset($this->commands[$command])) {
            $cmd = $this->commands[$command];
            
            if ($cmd['action'] === 'redirect') {
                return [
                    'action' => 'redirect',
                    'url' => $cmd['url'],
                    'message' => "Navigating to " . str_replace('?module=', '', $cmd['url']) . "..."
                ];
            } elseif ($cmd['action'] === 'clear') {
                return [
                    'action' => 'clear',
                    'message' => $cmd['message']
                ];
            } else {
                return [
                    'action' => 'message',
                    'message' => $cmd['message']
                ];
            }
        }
        
        // Check for partial matches
        foreach ($this->commands as $key => $cmd) {
            if (strpos($key, $command) !== false || strpos($command, $key) !== false) {
                if ($cmd['action'] === 'redirect') {
                    return [
                        'action' => 'redirect',
                        'url' => $cmd['url'],
                        'message' => "Did you mean '{$key}'? Taking you there..."
                    ];
                }
            }
        }
        
        // Process dynamic commands
        if (preg_match('/^show (.+)$/', $command, $matches)) {
            $module = $matches[1];
            return [
                'action' => 'redirect',
                'url' => "?module={$module}",
                'message' => "Loading {$module} module..."
            ];
        }
        
        if (preg_match('/^go to (.+)$/', $command, $matches)) {
            $destination = $matches[1];
            return [
                'action' => 'redirect',
                'url' => "?module={$destination}",
                'message' => "Navigating to {$destination}..."
            ];
        }
        
        if ($command === 'what time is it' || $command === 'current time') {
            return [
                'action' => 'message',
                'message' => 'Current system time: ' . date('H:i:s')
            ];
        }
        
        // Return fallback response
        $fallback = $this->responses['fallback'][array_rand($this->responses['fallback'])];
        return [
            'action' => 'message',
            'message' => $fallback
        ];
    }
    
    public function getRandomGreeting() {
        // Check for role-based greeting first
        if (isset($this->responses['role_based'][$this->user_role])) {
            $role_greetings = $this->responses['role_based'][$this->user_role];
            return $role_greetings[array_rand($role_greetings)];
        }
        
        // Fallback to regular greetings
        return $this->responses['greetings'][array_rand($this->responses['greetings'])];
    }
    
    public function getContextualHelp() {
        $help = [
            'common_commands' => [
                'show drones' => 'Display drone fleet status',
                'show map' => 'Open tactical map',
                'show threats' => 'View threat assessment',
                'system status' => 'Check system health',
                'weather' => 'Get weather conditions'
            ],
            'navigation' => [
                'dashboard' => 'Return to main dashboard',
                'settings' => 'Open system settings',
                'history' => 'View command history',
                'enterprise' => 'Enterprise management'
            ],
            'system' => [
                'help' => 'Show this help message',
                'clear' => 'Clear screen',
                'time' => 'Show current time',
                'who am i' => 'Display user info',
                'logout' => 'Log out of system'
            ]
        ];
        
        return $help;
    }
}

// Initialize AI Assistant
$ai = new AIAssistant($role, $username);
$result = null;
$response_message = '';
$clear_screen = false;

// Process POST command
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['command'])) {
    $command = trim($_POST['command']);
    
    // Log command for history (optional)
    $_SESSION['command_history'][] = [
        'command' => $command,
        'timestamp' => time(),
        'user' => $username
    ];
    
    // Keep only last 50 commands
    if (count($_SESSION['command_history']) > 50) {
        array_shift($_SESSION['command_history']);
    }
    
    $result = $ai->processCommand($command);
    
    if ($result['action'] === 'redirect') {
        // Store message for display after redirect
        $_SESSION['flash_message'] = $result['message'];
        header("Location: " . $result['url']);
        exit;
    } elseif ($result['action'] === 'clear') {
        $clear_screen = true;
    } else {
        $response_message = $result['message'];
    }
}

// Get flash message if exists
$flash_message = $_SESSION['flash_message'] ?? '';
unset($_SESSION['flash_message']);

// Get contextual help data
$contextual_help = $ai->getContextualHelp();

// Get greeting
$greeting = $ai->getRandomGreeting();

// Include header if using template system
$page_title = 'AI Assistant';
if (file_exists('../includes/header.php')) {
    include '../includes/header.php';
} else {
    // Fallback header
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>UEDF Sentinel - AI Assistant</title>
        <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Share+Tech+Mono&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <?php
}
?>

<style>
    * { 
        margin: 0; 
        padding: 0; 
        box-sizing: border-box; 
        font-family: 'Share Tech Mono', monospace; 
    }
    
    body { 
        background: #0a0f1c; 
        color: #00ff9d; 
        padding: 20px; 
        margin: 0;
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        background-image: 
            radial-gradient(circle at 50% 50%, rgba(255,0,110,0.1) 0%, transparent 50%),
            linear-gradient(45deg, #0a0f1c 0%, #151f2c 100%);
        position: relative;
    }
    
    body::before {
        content: '';
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: repeating-linear-gradient(
            0deg,
            rgba(0,255,157,0.03) 0px,
            rgba(0,0,0,0) 2px,
            rgba(0,255,157,0.03) 4px
        );
        pointer-events: none;
    }
    
    .assistant-container {
        max-width: 900px;
        width: 100%;
        background: rgba(21,31,44,0.95);
        border: 2px solid #ff006e;
        border-radius: 20px;
        padding: 30px;
        box-shadow: 
            0 0 50px rgba(255,0,110,0.3),
            inset 0 0 30px rgba(0,255,157,0.1);
        backdrop-filter: blur(10px);
        position: relative;
        overflow: hidden;
        animation: containerPulse 4s infinite;
    }
    
    @keyframes containerPulse {
        0%, 100% { border-color: #ff006e; box-shadow: 0 0 50px rgba(255,0,110,0.3); }
        50% { border-color: #00ff9d; box-shadow: 0 0 70px rgba(0,255,157,0.3); }
    }
    
    .assistant-container::after {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: linear-gradient(
            45deg,
            transparent 30%,
            rgba(255,0,110,0.1) 50%,
            transparent 70%
        );
        animation: rotate 10s linear infinite;
        pointer-events: none;
    }
    
    @keyframes rotate {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    
    .user-info {
        position: absolute;
        top: 20px;
        right: 20px;
        background: rgba(255,0,110,0.15);
        border: 1px solid #ff006e;
        padding: 8px 20px;
        border-radius: 30px;
        font-size: 0.9rem;
        color: #ff006e;
        z-index: 10;
        backdrop-filter: blur(5px);
        animation: userPulse 3s infinite;
    }
    
    @keyframes userPulse {
        0%, 100% { border-color: #ff006e; }
        50% { border-color: #00ff9d; color: #00ff9d; }
    }
    
    .user-info i {
        margin-right: 8px;
        color: #00ff9d;
    }
    
    .header {
        text-align: center;
        margin-bottom: 30px;
        border-bottom: 2px solid #ff006e;
        padding-bottom: 20px;
        position: relative;
    }
    
    .header h1 {
        font-family: 'Orbitron', sans-serif;
        color: #ff006e;
        font-size: 2.5rem;
        margin-bottom: 15px;
        text-shadow: 
            0 0 30px rgba(255,0,110,0.5),
            2px 2px 0 #00ff9d;
        letter-spacing: 3px;
    }
    
    .header h1 i {
        color: #00ff9d;
        margin-right: 15px;
        animation: spin 10s linear infinite;
    }
    
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    
    .status-badge {
        display: inline-block;
        background: rgba(0,255,157,0.1);
        border: 1px solid #00ff9d;
        color: #00ff9d;
        padding: 8px 30px;
        border-radius: 30px;
        font-size: 0.9rem;
        margin-top: 5px;
        animation: statusPulse 2s infinite;
    }
    
    @keyframes statusPulse {
        0%, 100% { opacity: 1; background: rgba(0,255,157,0.1); }
        50% { opacity: 0.8; background: rgba(0,255,157,0.2); }
    }
    
    .status-badge i {
        margin-right: 8px;
        animation: blink 1s infinite;
    }
    
    @keyframes blink {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.3; }
    }
    
    .greeting {
        background: linear-gradient(135deg, rgba(0,255,157,0.1) 0%, rgba(255,0,110,0.1) 100%);
        border: 1px solid #00ff9d;
        border-radius: 50px;
        padding: 20px 40px;
        margin-bottom: 30px;
        text-align: center;
        font-size: 1.3rem;
        position: relative;
        overflow: hidden;
    }
    
    .greeting::before {
        content: '';
        position: absolute;
        top: -2px;
        left: -2px;
        right: -2px;
        bottom: -2px;
        background: linear-gradient(45deg, #ff006e, #00ff9d, #ff006e);
        border-radius: 50px;
        z-index: -1;
        animation: borderGlow 3s linear infinite;
        opacity: 0.5;
    }
    
    @keyframes borderGlow {
        0% { filter: blur(5px); }
        50% { filter: blur(10px); }
        100% { filter: blur(5px); }
    }
    
    .greeting i {
        color: #ff006e;
        margin-right: 15px;
        font-size: 1.8rem;
    }
    
    .flash-message {
        background: rgba(255,0,110,0.2);
        border: 1px solid #ff006e;
        border-radius: 10px;
        padding: 15px 25px;
        margin-bottom: 20px;
        text-align: center;
        animation: slideIn 0.5s ease-out;
    }
    
    @keyframes slideIn {
        from { transform: translateY(-20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
    
    .response {
        background: #0a0f1c;
        border: 2px solid #ff006e;
        border-radius: 15px;
        padding: 30px;
        margin-bottom: 30px;
        min-height: 120px;
        font-size: 1.2rem;
        line-height: 1.6;
        position: relative;
        box-shadow: inset 0 0 30px rgba(255,0,110,0.2);
        word-wrap: break-word;
    }
    
    .response::before {
        content: '🤖 AI RESPONSE';
        position: absolute;
        top: -15px;
        left: 30px;
        background: #ff006e;
        color: #0a0f1c;
        padding: 5px 20px;
        border-radius: 30px;
        font-size: 0.8rem;
        font-weight: bold;
        letter-spacing: 1px;
    }
    
    .input-area {
        display: flex;
        gap: 15px;
        margin-bottom: 30px;
        position: relative;
        z-index: 10;
    }
    
    input {
        flex: 1;
        padding: 18px 25px;
        background: #0a0f1c;
        border: 2px solid #00ff9d;
        color: #00ff9d;
        font-family: 'Share Tech Mono', monospace;
        font-size: 1.1rem;
        border-radius: 50px;
        transition: all 0.3s ease;
    }
    
    input:focus {
        outline: none;
        border-color: #ff006e;
        box-shadow: 
            0 0 30px rgba(255,0,110,0.3),
            inset 0 0 20px rgba(0,255,157,0.2);
        transform: scale(1.02);
    }
    
    input::placeholder {
        color: #4a5568;
        font-style: italic;
    }
    
    button {
        padding: 18px 40px;
        background: transparent;
        border: 2px solid #ff006e;
        color: #ff006e;
        font-family: 'Orbitron', sans-serif;
        font-weight: 700;
        border-radius: 50px;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 1.1rem;
        letter-spacing: 1px;
        position: relative;
        overflow: hidden;
    }
    
    button::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,0,110,0.2), transparent);
        transition: left 0.5s ease;
    }
    
    button:hover {
        background: #ff006e;
        color: #0a0f1c;
        box-shadow: 0 0 30px rgba(255,0,110,0.5);
        transform: translateY(-3px);
    }
    
    button:hover::before {
        left: 100%;
    }
    
    .examples {
        margin-top: 30px;
        padding-top: 25px;
        border-top: 2px solid rgba(255,0,110,0.3);
        position: relative;
    }
    
    .examples h3 {
        color: #ff006e;
        margin-bottom: 20px;
        font-family: 'Orbitron', sans-serif;
        font-size: 1.2rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .examples h3 i {
        color: #00ff9d;
        font-size: 1.4rem;
    }
    
    .example-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }
    
    .example-category {
        background: rgba(0,255,157,0.05);
        border: 1px solid #00ff9d;
        border-radius: 15px;
        padding: 15px;
    }
    
    .example-category h4 {
        color: #00ff9d;
        margin-bottom: 10px;
        font-family: 'Orbitron', sans-serif;
        font-size: 0.9rem;
    }
    
    .example-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    
    .example-tag {
        background: rgba(255,0,110,0.1);
        border: 1px solid #ff006e;
        padding: 8px 16px;
        border-radius: 25px;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .example-tag:hover {
        background: #ff006e;
        color: #0a0f1c;
        transform: translateY(-2px) scale(1.05);
        box-shadow: 0 5px 15px rgba(255,0,110,0.3);
    }
    
    .back-link {
        display: inline-block;
        margin-top: 30px;
        color: #00ff9d;
        text-decoration: none;
        border: 2px solid #00ff9d;
        padding: 12px 35px;
        border-radius: 50px;
        transition: all 0.3s ease;
        font-size: 1rem;
        font-weight: bold;
        letter-spacing: 1px;
    }
    
    .back-link:hover {
        background: #00ff9d;
        color: #0a0f1c;
        box-shadow: 0 0 30px rgba(0,255,157,0.5);
        transform: translateY(-3px);
    }
    
    .footer {
        text-align: center;
        margin-top: 30px;
        color: #4a5568;
        font-size: 0.9rem;
        position: relative;
    }
    
    .voice-indicator {
        display: inline-block;
        width: 12px;
        height: 12px;
        background: #00ff9d;
        border-radius: 50%;
        margin-right: 8px;
        animation: voicePulse 1s ease-in-out infinite;
        box-shadow: 0 0 15px #00ff9d;
    }
    
    @keyframes voicePulse {
        0%, 100% { 
            transform: scale(1); 
            opacity: 1;
            box-shadow: 0 0 15px #00ff9d;
        }
        50% { 
            transform: scale(1.3); 
            opacity: 0.7;
            box-shadow: 0 0 25px #00ff9d;
        }
    }
    
    .command-history {
        margin-top: 20px;
        font-size: 0.8rem;
        color: #4a5568;
        text-align: left;
        padding: 10px;
        border-left: 2px solid #ff006e;
    }
    
    .command-history span {
        color: #00ff9d;
        margin-right: 10px;
    }
    
    @media (max-width: 768px) {
        .assistant-container {
            padding: 20px;
        }
        
        .header h1 {
            font-size: 1.8rem;
        }
        
        .input-area {
            flex-direction: column;
        }
        
        button {
            width: 100%;
        }
        
        .user-info {
            position: relative;
            top: 0;
            right: 0;
            margin-bottom: 15px;
            display: inline-block;
        }
    }
</style>

<?php if (!file_exists('../includes/header.php')): ?>
</head>
<body>
<?php endif; ?>

<div class="assistant-container">
    <div class="user-info">
        <i class="fas fa-<?php echo $role === 'commander' ? 'crown' : ($role === 'admin' ? 'shield-halved' : 'user'); ?>"></i>
        <?php echo htmlspecialchars($username); ?> | 
        <span style="color: #00ff9d;"><?php echo strtoupper($role); ?></span>
        <span style="margin-left: 10px; font-size: 0.7rem;">ID: <?php echo substr($user_id, 0, 8); ?></span>
    </div>
    
    <div class="header">
        <h1>
            <i class="fas fa-microchip"></i> 
            AI COMMAND ASSISTANT
        </h1>
        <span class="status-badge">
            <i class="fas fa-circle"></i> QUANTUM CORE ONLINE
            <span class="voice-indicator"></span>
        </span>
    </div>
    
    <?php if ($flash_message): ?>
        <div class="flash-message">
            <i class="fas fa-info-circle"></i> 
            <?php echo htmlspecialchars($flash_message); ?>
        </div>
    <?php endif; ?>
    
    <div class="greeting">
        <i class="fas fa-comment-dots fa-shake"></i>
        <?php echo htmlspecialchars($greeting); ?>
    </div>
    
    <?php if ($clear_screen): ?>
        <script>
            document.querySelector('.response')?.remove();
        </script>
    <?php endif; ?>
    
    <?php if ($response_message): ?>
        <div class="response">
            <?php echo htmlspecialchars($response_message); ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" class="input-area" id="commandForm">
        <input type="text" 
               name="command" 
               placeholder="Enter your command..." 
               value="<?php echo isset($_POST['command']) ? htmlspecialchars($_POST['command']) : ''; ?>"
               autocomplete="off"
               autofocus>
        <button type="submit">
            <i class="fas fa-paper-plane"></i> 
            EXECUTE
        </button>
    </form>
    
    <div class="examples">
        <h3>
            <i class="fas fa-bolt"></i> 
            QUICK COMMANDS
        </h3>
        
        <div class="example-grid">
            <div class="example-category">
                <h4><i class="fas fa-drone"></i> FLEET COMMANDS</h4>
                <div class="example-tags">
                    <?php foreach (array_slice($contextual_help['common_commands'], 0, 3) as $cmd => $desc): ?>
                        <span class="example-tag" title="<?php echo $desc; ?>" 
                              onclick="setCommand('<?php echo $cmd; ?>')">
                            <?php echo $cmd; ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="example-category">
                <h4><i class="fas fa-compass"></i> NAVIGATION</h4>
                <div class="example-tags">
                    <?php foreach (array_slice($contextual_help['navigation'], 0, 3) as $cmd => $desc): ?>
                        <span class="example-tag" title="<?php echo $desc; ?>" 
                              onclick="setCommand('<?php echo $cmd; ?>')">
                            <?php echo $cmd; ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="example-category">
                <h4><i class="fas fa-cog"></i> SYSTEM</h4>
                <div class="example-tags">
                    <?php foreach (array_slice($contextual_help['system'], 0, 3) as $cmd => $desc): ?>
                        <span class="example-tag" title="<?php echo $desc; ?>" 
                              onclick="setCommand('<?php echo $cmd; ?>')">
                            <?php echo $cmd; ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <div style="margin-top: 20px;">
            <div class="example-tags" style="justify-content: center;">
                <span class="example-tag" onclick="setCommand('show drones')">🚁 show drones</span>
                <span class="example-tag" onclick="setCommand('show map')">🗺️ show map</span>
                <span class="example-tag" onclick="setCommand('show threats')">⚠️ show threats</span>
                <span class="example-tag" onclick="setCommand('dashboard')">📊 dashboard</span>
                <span class="example-tag" onclick="setCommand('settings')">⚙️ settings</span>
                <span class="example-tag" onclick="setCommand('system status')">💻 system status</span>
                <span class="example-tag" onclick="setCommand('help')">❓ help</span>
                <span class="example-tag" onclick="setCommand('clear')">🧹 clear</span>
                <span class="example-tag" onclick="setCommand('time')">⏰ time</span>
                <span class="example-tag" onclick="setCommand('weather')">🌤️ weather</span>
            </div>
        </div>
    </div>
    
    <?php if (!empty($_SESSION['command_history'])): ?>
        <div class="command-history">
            <i class="fas fa-history"></i> RECENT: 
            <?php 
            $recent = array_slice($_SESSION['command_history'], -5);
            foreach ($recent as $item): 
            ?>
                <span>›</span> <?php echo htmlspecialchars($item['command']); ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <div style="text-align: center;">
        <a href="?module=home" class="back-link">
            <i class="fas fa-arrow-left"></i> 
            RETURN TO COMMAND CENTER
        </a>
    </div>
    
    <div class="footer">
        <span class="voice-indicator"></span> 
        NEURAL INTERFACE ACTIVE • AWAITING INPUT
        <br>
        <small style="color: #4a5568; margin-top: 5px; display: block;">
            QUANTUM PROCESSING v5.0 • LATENCY: <?php echo rand(1, 10); ?>ms
        </small>
    </div>
</div>

<script>
    // Auto-focus input on page load
    document.addEventListener('DOMContentLoaded', function() {
        const input = document.querySelector('input[name="command"]');
        if (input) {
            input.focus();
            // Move cursor to end
            const len = input.value.length;
            input.setSelectionRange(len, len);
        }
    });
    
    // Function to set command from examples
    function setCommand(command) {
        const input = document.querySelector('input[name="command"]');
        if (input) {
            input.value = command;
            input.focus();
            
            // Visual feedback
            input.style.transform = 'scale(1.02)';
            setTimeout(() => {
                input.style.transform = '';
            }, 200);
        }
    }
    
    // Allow Enter key to submit with animation
    document.getElementById('commandForm')?.addEventListener('submit', function(e) {
        const button = this.querySelector('button');
        button.style.transform = 'scale(0.95)';
        setTimeout(() => {
            button.style.transform = '';
        }, 200);
    });
    
    // Voice simulation with random patterns
    let voiceActive = true;
    setInterval(() => {
        if (voiceActive) {
            const indicator = document.querySelector('.voice-indicator');
            if (indicator) {
                // Random pulse pattern
                const scale = 1 + Math.random() * 0.5;
                indicator.style.transform = `scale(${scale})`;
                setTimeout(() => {
                    indicator.style.transform = '';
                }, 200);
            }
        }
    }, 800);
    
    // Add keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl + L to clear input
        if (e.ctrlKey && e.key === 'l') {
            e.preventDefault();
            document.querySelector('input[name="command"]').value = '';
        }
        
        // Escape to clear and focus
        if (e.key === 'Escape') {
            const input = document.querySelector('input[name="command"]');
            input.value = '';
            input.focus();
        }
        
        // Up arrow for last command
        if (e.key === 'ArrowUp') {
            e.preventDefault();
            <?php if (!empty($_SESSION['command_history'])): ?>
            const lastCommand = <?php echo json_encode(end($_SESSION['command_history'])['command']); ?>;
            document.querySelector('input[name="command"]').value = lastCommand;
            <?php endif; ?>
        }
    });
    
    // Add typing sound simulation (visual only)
    const input = document.querySelector('input[name="command"]');
    if (input) {
        input.addEventListener('input', function() {
            this.style.borderColor = '#ff006e';
            setTimeout(() => {
                this.style.borderColor = '#00ff9d';
            }, 200);
        });
    }
    
    // Random AI thinking animation
    setInterval(() => {
        const response = document.querySelector('.response');
        if (response && !response.querySelector('.thinking')) {
            const thinking = document.createElement('div');
            thinking.className = 'thinking';
            thinking.style.cssText = `
                position: absolute;
                bottom: 10px;
                right: 20px;
                font-size: 0.7rem;
                color: #4a5568;
                animation: fadeInOut 2s infinite;
            `;
            thinking.innerHTML = '⚡ PROCESSING...';
            
            if (Math.random() > 0.7) {
                response.appendChild(thinking);
                setTimeout(() => thinking.remove(), 2000);
            }
        }
    }, 3000);
</script>

<style>
    @keyframes fadeInOut {
        0%, 100% { opacity: 0.3; }
        50% { opacity: 1; }
    }
    
    .example-tag {
        position: relative;
        overflow: hidden;
    }
    
    .example-tag::after {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.5s ease;
    }
    
    .example-tag:hover::after {
        left: 100%;
    }
    
    .command-history {
        animation: slideUp 0.5s ease-out;
    }
    
    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* Scrollbar styling */
    ::-webkit-scrollbar {
        width: 10px;
        background: #0a0f1c;
    }
    
    ::-webkit-scrollbar-track {
        border: 1px solid #ff006e;
        border-radius: 5px;
    }
    
    ::-webkit-scrollbar-thumb {
        background: linear-gradient(45deg, #ff006e, #00ff9d);
        border-radius: 5px;
    }
    
    ::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(45deg, #00ff9d, #ff006e);
    }
</style>

<?php
// Include footer if using template system
if (file_exists('../includes/footer.php')) {
    include '../includes/footer.php';
} else {
    echo "\n</body>\n</html>";
}
?>
