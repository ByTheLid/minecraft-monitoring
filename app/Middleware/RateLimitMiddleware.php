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

            // Clean expired entries
            $db->prepare("DELETE FROM rate_limits WHERE expires_at < NOW()")->execute();

            // Check current count
            $stmt = $db->prepare("SELECT hits FROM rate_limits WHERE `key` = ?");
            $stmt->execute([$key]);
            $row = $stmt->fetch();

            if ($row && $row['hits'] >= $this->maxRequests) {
                $response = new Response();
                return $response->json([
                    'success' => false,
                    'error' => ['code' => 'RATE_LIMIT', 'message' => 'Too many requests']
                ], 429);
            }

            if ($row) {
                $db->prepare("UPDATE rate_limits SET hits = hits + 1 WHERE `key` = ?")->execute([$key]);
            } else {
                $db->prepare(
                    "INSERT INTO rate_limits (`key`, hits, expires_at) VALUES (?, 1, DATE_ADD(NOW(), INTERVAL ? SECOND))"
                )->execute([$key, $this->windowSeconds]);
            }
        } catch (\Throwable $e) {
            // If rate_limits table doesn't exist yet, skip
            logger()->warning('Rate limit check failed: ' . $e->getMessage());
        }

        return $next($request);
    }
}
