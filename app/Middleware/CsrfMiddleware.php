<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;

class CsrfMiddleware
{
    public function handle(Request $request, callable $next): Response
    {
        if (in_array($request->method(), ['POST', 'PUT', 'DELETE'])) {
            $token = $request->input('_csrf_token') ?? $request->header('X-CSRF-Token');

            if (!$token || !csrf_verify($token)) {
                $response = new Response();
                if ($request->isAjax()) {
                    return $response->json([
                        'success' => false,
                        'error' => ['code' => 'CSRF_ERROR', 'message' => 'Invalid CSRF token']
                    ], 403);
                }
                flash('error', 'Invalid security token. Please try again.');
                return $response->redirect($request->header('Referer') ?? '/');
            }
        }

        return $next($request);
    }
}
