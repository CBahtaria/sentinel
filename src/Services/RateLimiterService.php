<?php
/**
 * Rate Limiter Class
 */
class RateLimiter {
    private $storageDir;
    
    public function __construct() {
        $this->storageDir = sys_get_temp_dir() . '/ratelimits/';
        if (!file_exists($this->storageDir)) {
            mkdir($this->storageDir, 0777, true);
        }
    }
    
    public function check($key, $limit = 60, $window = 60) {
        $file = $this->storageDir . md5($key) . '.json';
        $now = time();
        
        if (file_exists($file)) {
            $data = json_decode(file_get_contents($file), true);
            if ($now > $data['reset']) {
                $data = ['count' => 1, 'reset' => $now + $window];
            } else {
                $data['count']++;
            }
        } else {
            $data = ['count' => 1, 'reset' => $now + $window];
        }
        
        file_put_contents($file, json_encode($data));
        
        return [
            'allowed' => $data['count'] <= $limit,
            'remaining' => max(0, $limit - $data['count']),
            'reset' => $data['reset']
        ];
    }
    
    public function getRemaining($key, $limit = 60) {
        $file = $this->storageDir . md5($key) . '.json';
        
        if (!file_exists($file)) {
            return $limit;
        }
        
        $data = json_decode(file_get_contents($file), true);
        return max(0, $limit - $data['count']);
    }
}
?>
