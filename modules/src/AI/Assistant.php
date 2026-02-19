<?php
/**
 * UEDF SENTINEL - AI Voice Assistant
 * Voice-activated command processing
 */

class AIAssistant {
    private $commands = [
        'show drones' => '?module=drones',
        'show map' => '?module=map',
        'show threats' => '?module=concurrency',
        'threat monitor' => '?module=concurrency',
        'dashboard' => '?module=dashboard',
        'command dashboard' => '?module=dashboard',
        'home' => '?module=home',
        'main menu' => '?module=home',
        'audit' => '?module=audit',
        'audit logs' => '?module=audit',
        'reports' => '?module=reports',
        'generate report' => '?module=reports',
        'settings' => 'settings_page.php',
        'preferences' => 'settings_page.php',
        'drone control' => '?module=drone-control',
        'control drones' => '?module=drone-control',
        'enterprise' => '?module=enterprise',
        'enterprise dashboard' => '?module=enterprise',
        'history' => '?module=history',
        'threat history' => '?module=history',
        'analytics' => '?module=analytics',
        'statistics' => '?module=analytics',
        'notifications' => '?module=notifications',
        'alerts' => '?module=notifications',
        'quick access' => '?module=quick-access',
        'command bar' => '?module=quick-access',
        'help' => 'help',
        'what can you do' => 'help',
        'time' => 'time',
        'date' => 'date',
        'weather' => 'weather',
        'system status' => 'status'
    ];
    
    private $responses = [
        'welcome' => 'Welcome Commander. All systems operational.',
        'error' => 'I did not understand that command. Try saying "help" for available commands.',
        'processing' => 'Processing your request...',
        'success' => 'Command executed successfully.',
        'help' => 'Available commands: show drones, show map, show threats, dashboard, home, audit, reports, settings, drone control, enterprise, history, analytics, notifications, time, date, weather, system status',
        'time' => 'The current time is ',
        'date' => 'Today\'s date is ',
        'weather' => 'Current weather in Eswatini: Clear skies, 28°C',
        'status' => 'All systems operational. 15 drones active, 5 threats detected, 24 nodes online.'
    ];
    
    public function processCommand($input) {
        $input = strtolower(trim($input));
        
        // Check for exact matches
        foreach ($this->commands as $cmd => $action) {
            if ($input === $cmd || strpos($input, $cmd) !== false) {
                if ($action === 'help') {
                    return [
                        'action' => 'speak',
                        'message' => $this->responses['help']
                    ];
                } elseif ($action === 'time') {
                    return [
                        'action' => 'speak',
                        'message' => $this->responses['time'] . date('H:i') . ' Zulu'
                    ];
                } elseif ($action === 'date') {
                    return [
                        'action' => 'speak',
                        'message' => $this->responses['date'] . date('Y-m-d')
                    ];
                } elseif ($action === 'weather') {
                    return [
                        'action' => 'speak',
                        'message' => $this->responses['weather']
                    ];
                } elseif ($action === 'status') {
                    return [
                        'action' => 'speak',
                        'message' => $this->responses['status']
                    ];
                } else {
                    return [
                        'action' => 'redirect',
                        'url' => $action,
                        'message' => "Opening {$cmd}..."
                    ];
                }
            }
        }
        
        return [
            'action' => 'speak',
            'message' => $this->responses['error']
        ];
    }
    
    public function getVoiceResponse($text) {
        // In production, integrate with TTS service
        return $text;
    }
    
    public function getRandomGreeting() {
        $greetings = [
            'At your service, Commander.',
            'How may I assist you today?',
            'Systems ready. Awaiting your command.',
            'AI Assistant online. What are your orders?',
            'Ready for voice command input.'
        ];
        return $greetings[array_rand($greetings)];
    }
}
?>
