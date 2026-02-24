<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database;

class RateLimitMiddleware
{
    private int $maxRequests;
    private int $windowSeconds;

    public function __construct(int $maxRequests = 60, int $windowSeconds = 60)
    {
        $this->maxRequests = $maxRequests;
        $this->windowSeconds = $windowSeconds;
    }

    public function handle(Request $request, callable $next): Response
    {
        $ip = $request->ip();
        $key = 'api:' . $ip;

        try {
            $db = Database::getInstance();

            // Probabilistic cleanup (1% of requests) instead of every request
            if (random_int(1, 100) === 1) {
                $db->prepare("DELETE FROM rate_limits WHERE expires_at < NOW()")->execute();
            }

            // Atomic upsert — no race condition
            $db->prepare("
                INSERT INTO rate_limits (`key`, hits, expires_at) 
                VALUES (?, 1, DATE_ADD(NOW(), INTERVAL ? SECOND))
                ON DUPLICATE KEY UPDATE hits = IF(expires_at >= NOW(), hits + 1, 1),
                    expires_at = IF(expires_at >= NOW(), expires_at, DATE_ADD(NOW(), INTERVAL ? SECOND))
            ")->execute([$key, $this->windowSeconds, $this->windowSeconds]);

            // Check if over limit
            $stmt = $db->prepare("SELECT hits FROM rate_limits WHERE `key` = ? AND expires_at >= NOW()");
            $stmt->execute([$key]);
            $row = $stmt->fetch();

            if ($row && $row['hits'] > $this->maxRequests) {
                $response = new Response();
                return $response->json([
                    'success' => false,
                    'error' => ['code' => 'RATE_LIMIT', 'message' => 'Too many requests']
                ], 429);
            }
        } catch (\Throwable $e) {
            // If rate_limits table doesn't exist yet, skip
            logger()->warning('Rate limit check failed: ' . $e->getMessage());
        }

        return $next($request);
    }
}
