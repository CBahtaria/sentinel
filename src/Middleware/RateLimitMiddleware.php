<?php
declare(strict_types=1);
namespace UEDF\Middleware;

use UEDF\Cache\RedisCache;

class RateLimitMiddleware {
    private int $unauthLimit;   // requests per window
    private int $authLimit;
    private int $windowSeconds;

    public function __construct(int $unauthLimit = 5, int $authLimit = 50, int $windowSeconds = 1) {
        $this->unauthLimit = $unauthLimit;
        $this->authLimit = $authLimit;
        $this->windowSeconds = $windowSeconds;
    }

    /**
     * Check rate limit for the current request.
     * Returns true if allowed, false if rate-limited.
     * Uses sliding window counter stored in Redis.
     * Falls back to allowing all requests if Redis is unavailable (fail-open on infra failure only).
     */
    public function check(string $clientKey, bool $authenticated): bool {
        try {
            $cache = RedisCache::getInstance();
            $limit = $authenticated ? $this->authLimit : $this->unauthLimit;
            $redisKey = "rl:{$clientKey}:" . floor(time() / $this->windowSeconds);

            // Get current count; null means key does not exist yet
            $current = $cache->get($redisKey);
            $count = is_int($current) ? $current : (is_array($current) ? (int)$current[0] : 0);

            if ($count >= $limit) {
                return false;
            }

            // Increment — use set with a short window since RedisCache wraps with json
            $cache->set($redisKey, $count + 1, $this->windowSeconds + 1);
            return true;
        } catch (\Exception $e) {
            // Redis unavailable — fail open (infra failure, not abuse)
            error_log("RateLimitMiddleware Redis error: " . $e->getMessage());
            return true;
        }
    }
}
