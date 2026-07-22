<?php
declare(strict_types=1);
namespace UEDF\Cache;

class RedisCache {
    private \Redis $redis;
    private static ?RedisCache $instance = null;

    private function __construct() {
        $host = getenv('REDIS_HOST') ?: 'localhost';
        $port = (int)(getenv('REDIS_PORT') ?: 6379);
        $this->redis = new \Redis();
        if (!$this->redis->connect($host, $port, 2.0)) {
            throw new \RuntimeException("Redis connect failed: {$host}:{$port}");
        }
        $password = getenv('REDIS_PASSWORD');
        if ($password !== false && $password !== '') {
            $this->redis->auth($password);
        }
    }

    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Get a cached value. Returns null if key missing or expired.
    public function get(string $key): mixed {
        $val = $this->redis->get($key);
        return $val === false ? null : json_decode($val, true);
    }

    // Set a value with TTL in seconds.
    public function set(string $key, mixed $value, int $ttl = 30): bool {
        return (bool)$this->redis->setex($key, $ttl, json_encode($value));
    }

    public function delete(string $key): bool {
        return (bool)$this->redis->del($key);
    }
}
