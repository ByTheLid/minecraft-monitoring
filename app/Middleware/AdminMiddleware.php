<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;

class AdminMiddleware
{
    public function handle(Request $request, callable $next): Response
    {
        if (!is_admin()) {
            $response = new Response();
            if ($request->isAjax() || str_starts_with($request->path(), '/api/')) {
                return $response->json([
                    'success' => false,
                    'error' => ['code' => 'FORBIDDEN', 'message' => 'Admin access required']
                ], 403);
            }
            return $response->redirect('/');
        }

        return $next($request);
    }
}
