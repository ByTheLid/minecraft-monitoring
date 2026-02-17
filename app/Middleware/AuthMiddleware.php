<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;

class AuthMiddleware
{
    public function handle(Request $request, callable $next): Response
    {
        if (!auth()) {
            $response = new Response();
            if ($request->isAjax() || str_starts_with($request->path(), '/api/')) {
                return $response->json([
                    'success' => false,
                    'error' => ['code' => 'UNAUTHORIZED', 'message' => 'Authentication required']
                ], 401);
            }
            flash('error', 'Please log in to access this page.');
            return $response->redirect('/login');
        }

        return $next($request);
    }
}
