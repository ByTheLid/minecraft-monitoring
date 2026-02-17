<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;

class CorsMiddleware
{
    public function handle(Request $request, callable $next): Response
    {
        $response = $next($request);

        $response->header('Access-Control-Allow-Origin', '*');
        $response->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->header('Access-Control-Allow-Headers', 'Content-Type, X-CSRF-Token, Authorization');

        return $response;
    }
}
