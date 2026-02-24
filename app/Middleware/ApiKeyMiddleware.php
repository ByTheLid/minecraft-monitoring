<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Models\ApiKey;

class ApiKeyMiddleware
{
    public function handle(Request $request, callable $next): Response
    {
        $apiKey = $_SERVER['HTTP_X_API_KEY'] ?? null;

        if ($apiKey) {
            $key = ApiKey::findByKey($apiKey);

            if (!$key || !$key['is_active']) {
                $response = new Response();
                return $response->json([
                    'success' => false,
                    'data' => null,
                    'meta' => [],
                    'error' => ['code' => 'INVALID_API_KEY', 'message' => 'Invalid or deactivated API key']
                ], 401);
            }

            // Update last_used_at (non-blocking, fire and forget)
            ApiKey::touch($key['id']);

            // Store authenticated rate limit for RateLimitMiddleware
            $_SERVER['_API_RATE_LIMIT'] = $key['rate_limit'];
        } else {
            // Anonymous access — lower rate limit
            $_SERVER['_API_RATE_LIMIT'] = 30;
        }

        return $next($request);
    }
}
