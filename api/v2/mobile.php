<?php
/**
 * UEDF SENTINEL v5.0 - API v2 Mobile Endpoint
 * Optimized for mobile app consumption with enhanced security and features
 */

// Enable strict error handling
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Set content type if not already set
if (!headers_sent()) {
    header('Content-Type: application/json');
}

// Initialize response array
$response = [
    'api_version' => 'v2',
    'timestamp' => date('c'),
    'request_id' => uniqid('uedf_', true),
    'success' => false,
    'data' => null,
    'error' => null,
    'metadata' => [
        'server_time' => time(),
        'timezone' => date_default_timezone_get(),
        'environment' => $_SERVER['SERVER_NAME'] ?? 'local'
    ]
];

try {
    // API Key validation
    $valid_api_keys = [
        'UEDF2026' => 'mobile_app',
        'UEDF2026_PROD' => 'production_mobile',
        'UEDF2026_DEV' => 'development_mobile',
        'UEDF2026_TEST' => 'test_suite'
    ];
    
    // Get API key from multiple sources
    $api_key = '';
    if (isset($_GET['key'])) {
        $api_key = trim($_GET['key']);
    } elseif (isset($_SERVER['HTTP_X_API_KEY'])) {
        $api_key = trim($_SERVER['HTTP_X_API_KEY']);
    } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $auth_header = $_SERVER['HTTP_AUTHORIZATION'];
        if (preg_match('/Bearer\s+(.+)$/i', $auth_header, $matches)) {
            $api_key = $matches[1];
        }
    }
    
    // Validate API key
    if (empty($api_key)) {
        throw new Exception('API key is required', 401);
    }
    
    if (!isset($valid_api_keys[$api_key])) {
        // Log invalid key attempt (security monitoring)
        error_log("Invalid API key attempt: " . substr($api_key, 0, 10) . "... from IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
        throw new Exception('Invalid API key', 401);
    }
    
    $client_type = $valid_api_keys[$api_key];
    
    // Rate limiting (simple implementation)
    session_start();
    $rate_limit_key = 'rate_limit_' . $_SERVER['REMOTE_ADDR'];
    $rate_limit = $_SESSION[$rate_limit_key] ?? ['count' => 0, 'reset' => time() + 60];
    
    if ($rate_limit['reset'] < time()) {
        $rate_limit = ['count' => 1, 'reset' => time() + 60];
    } else {
        $rate_limit['count']++;
    }
    
    // 60 requests per minute limit
    if ($rate_limit['count'] > 60) {
        throw new Exception('Rate limit exceeded. Try again in ' . ($rate_limit['reset'] - time()) . ' seconds', 429);
    }
    
    $_SESSION[$rate_limit_key] = $rate_limit;
    
    // Get action parameter with validation
    $allowed_actions = ['status', 'stats', 'features', 'config', 'alerts', 'drones', 'threats'];
    $action = $_GET['action'] ?? 'status';
    
    if (!in_array($action, $allowed_actions)) {
        throw new Exception('Unknown action: ' . htmlspecialchars($action), 400);
    }
    
    // Get format parameter (optional)
    $format = $_GET['format'] ?? 'json';
    if (!in_array($format, ['json', 'minimal'])) {
        $format = 'json';
    }
    
    // Process action
    switch ($action) {
        case 'status':
            $response['data'] = [
                'status' => 'online',
                'version' => '2.0.5',
                'build' => '2026.02.1701',
                'timestamp' => date('Y-m-d H:i:s'),
                'uptime' => time() - strtotime('today'), // Mock uptime
                'client' => $client_type,
                'features' => [
                    'push_notifications' => true,
                    'offline_mode' => true,
                    'biometric_auth' => true,
                    'live_tracking' => true,
                    'encryption' => 'AES-256',
                    'compression' => 'gzip'
                ],
                'server_load' => [
                    'cpu' => rand(20, 60) . '%',
                    'memory' => rand(30, 70) . '%',
                    'connections' => rand(5, 50)
                ]
            ];
            break;
            
        case 'stats':
            // Simulate real-time stats with some variation
            $drone_count = rand(12, 18);
            $threat_count = rand(3, 12);
            
            $response['data'] = [
                'drones' => [
                    'total' => $drone_count,
                    'active' => rand(8, $drone_count),
                    'charging' => rand(1, 4),
                    'maintenance' => rand(0, 2),
                    'deployed' => rand(5, $drone_count - 2)
                ],
                'threats' => [
                    'total' => $threat_count,
                    'critical' => rand(0, 3),
                    'high' => rand(1, 5),
                    'medium' => rand(2, 8),
                    'low' => rand(0, 4),
                    'neutralized' => rand(1, 6)
                ],
                'nodes' => [
                    'total' => 24,
                    'online' => rand(20, 24),
                    'offline' => rand(0, 4)
                ],
                'users' => [
                    'active' => rand(8, 15),
                    'total' => 12,
                    'sessions' => rand(5, 12)
                ],
                'network' => [
                    'bandwidth' => rand(10, 100) . ' Mbps',
                    'latency' => rand(5, 50) . ' ms',
                    'packet_loss' => rand(0, 2) . '%'
                ],
                'last_updated' => date('Y-m-d H:i:s')
            ];
            break;
            
        case 'features':
            $response['data'] = [
                'mobile_features' => [
                    'push_notifications' => [
                        'enabled' => true,
                        'provider' => 'Firebase',
                        'topics' => ['alerts', 'updates', 'emergency']
                    ],
                    'offline_mode' => [
                        'enabled' => true,
                        'max_cache_days' => 7,
                        'sync_on_connect' => true
                    ],
                    'biometric_auth' => [
                        'enabled' => true,
                        'methods' => ['fingerprint', 'face_id'],
                        'timeout' => 300
                    ],
                    'live_tracking' => [
                        'enabled' => true,
                        'update_interval' => 5,
                        'precision' => 'high'
                    ],
                    'encryption' => [
                        'algorithm' => 'AES-256-GCM',
                        'key_rotation' => '30 days'
                    ]
                ],
                'api_features' => [
                    'rate_limit' => '60/minute',
                    'max_payload' => '10MB',
                    'compression' => true,
                    'batch_requests' => true
                ]
            ];
            break;
            
        case 'config':
            $response['data'] = [
                'app_version_min' => '2.0.0',
                'app_version_recommended' => '2.0.5',
                'update_required' => false,
                'api_endpoints' => [
                    'base' => '/api/v2/',
                    'websocket' => 'wss://' . ($_SERVER['SERVER_NAME'] ?? 'api.uedf.com') . '/ws',
                    'cdn' => 'https://cdn.uedf.com/'
                ],
                'timeouts' => [
                    'connection' => 30,
                    'request' => 15,
                    'sync' => 60
                ],
                'features' => [
                    'debug_mode' => ($client_type === 'development_mobile'),
                    'analytics' => true,
                    'crash_reporting' => true
                ]
            ];
            break;
            
        case 'alerts':
            $alert_levels = ['info', 'warning', 'critical'];
            $alerts = [];
            
            // Generate mock alerts
            for ($i = 0; $i < rand(1, 5); $i++) {
                $level = $alert_levels[array_rand($alert_levels)];
                $alerts[] = [
                    'id' => uniqid(),
                    'level' => $level,
                    'message' => 'Alert ' . ($i + 1) . ': ' . ($level === 'critical' ? 'Immediate attention required' : 'System notification'),
                    'timestamp' => date('Y-m-d H:i:s', strtotime('-' . rand(0, 60) . ' minutes')),
                    'acknowledged' => (rand(0, 1) == 1),
                    'source' => ['drone', 'node', 'system'][rand(0, 2)]
                ];
            }
            
            $response['data'] = [
                'total_alerts' => count($alerts),
                'unread' => rand(0, count($alerts)),
                'alerts' => $alerts,
                'next_poll' => date('Y-m-d H:i:s', strtotime('+30 seconds'))
            ];
            break;
            
        case 'drones':
            $drones = [];
            for ($i = 1; $i <= 15; $i++) {
                $status = ['active', 'charging', 'maintenance', 'standby'][rand(0, 3)];
                $battery = $status === 'charging' ? rand(80, 100) : rand(20, 95);
                
                $drones[] = [
                    'id' => 'DRN-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                    'name' => 'Sentinel ' . $i,
                    'status' => $status,
                    'battery' => $battery . '%',
                    'location' => [
                        'lat' => 34.0522 + (rand(-100, 100) / 1000),
                        'lng' => -118.2437 + (rand(-100, 100) / 1000)
                    ],
                    'last_update' => date('Y-m-d H:i:s', strtotime('-' . rand(0, 5) . ' minutes')),
                    'speed' => rand(0, 60) . ' km/h',
                    'altitude' => rand(0, 500) . ' m'
                ];
            }
            
            $response['data'] = [
                'total' => count($drones),
                'drones' => $drones,
                'fleet_summary' => [
                    'active' => count(array_filter($drones, fn($d) => $d['status'] === 'active')),
                    'charging' => count(array_filter($drones, fn($d) => $d['status'] === 'charging')),
                    'maintenance' => count(array_filter($drones, fn($d) => $d['status'] === 'maintenance'))
                ]
            ];
            break;
            
        case 'threats':
            $threats = [];
            $threat_types = ['hostile_drone', 'unauthorized_access', 'signal_jamming', 'physical_intrusion'];
            $severities = ['low', 'medium', 'high', 'critical'];
            
            for ($i = 1; $i <= rand(3, 8); $i++) {
                $severity = $severities[array_rand($severities)];
                $threats[] = [
                    'id' => 'THR-' . uniqid(),
                    'type' => $threat_types[array_rand($threat_types)],
                    'severity' => $severity,
                    'description' => ucfirst($severity) . ' severity threat detected',
                    'timestamp' => date('Y-m-d H:i:s', strtotime('-' . rand(0, 30) . ' minutes')),
                    'location' => [
                        'zone' => 'Zone ' . rand(1, 5),
                        'coordinates' => [
                            'lat' => 34.0522 + (rand(-500, 500) / 1000),
                            'lng' => -118.2437 + (rand(-500, 500) / 1000)
                        ]
                    ],
                    'assigned_drone' => rand(0, 1) ? 'DRN-' . str_pad(rand(1, 15), 4, '0', STR_PAD_LEFT) : null,
                    'status' => ['detected', 'investigating', 'neutralizing', 'resolved'][rand(0, 3)]
                ];
            }
            
            $response['data'] = [
                'total' => count($threats),
                'threats' => $threats,
                'risk_assessment' => [
                    'overall_risk' => ['low', 'medium', 'high'][rand(0, 2)],
                    'hotspots' => rand(1, 3),
                    'eta_response' => rand(30, 120) . ' seconds'
                ]
            ];
            break;
    }
    
    $response['success'] = true;
    
    // Add pagination metadata for list responses
    if (in_array($action, ['alerts', 'drones', 'threats'])) {
        $response['metadata']['pagination'] = [
            'page' => isset($_GET['page']) ? (int)$_GET['page'] : 1,
            'limit' => isset($_GET['limit']) ? min((int)$_GET['limit'], 100) : 20,
            'total' => $response['data']['total'] ?? count($response['data'][$action] ?? [])
        ];
    }
    
} catch (Exception $e) {
    $http_code = $e->getCode() >= 400 ? $e->getCode() : 500;
    http_response_code($http_code);
    
    $response['error'] = [
        'code' => $http_code,
        'message' => $e->getMessage(),
        'type' => (new ReflectionClass($e))->getShortName()
    ];
    
    // Development-only debug info
    if (ini_get('display_errors') === '1') {
        $response['error']['debug'] = [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ];
    }
}

// Add execution time
$response['metadata']['execution_time_ms'] = round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 2);

// Output response
if ($format === 'minimal') {
    // Minimal format (no pretty print, less metadata)
    echo json_encode($response, JSON_UNESCAPED_SLASHES);
} else {
    // Pretty print for development
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
}

// Log successful API calls in production
if (ini_get('display_errors') === '0') {
    error_log("API v2 Mobile: {$action} - " . ($response['success'] ? 'Success' : 'Failed') . " - Client: {$client_type}");
}
?>
