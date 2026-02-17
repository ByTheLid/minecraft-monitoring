<?php

namespace App\Core;

class App
{
    private Router $router;
    private Request $request;

    public function __construct()
    {
        // Load environment
        Env::load(dirname(__DIR__, 2) . '/.env');

        // Start session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Set error handling
        if (env('APP_DEBUG', false)) {
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
        } else {
            error_reporting(0);
            ini_set('display_errors', '0');
        }

        // Set timezone
        date_default_timezone_set('UTC');

        // Check auth from cookie (safe — DB might not be ready)
        try {
            \App\Services\AuthService::check(config('session.cookie', 'mc_session'));
        } catch (\Throwable $e) {
            // DB not ready yet, skip auth
        }

        $this->request = new Request();
        $this->router = new Router();
    }

    public function router(): Router
    {
        return $this->router;
    }

    public function run(): void
    {
        try {
            // Load routes
            $router = $this->router;
            require dirname(__DIR__, 2) . '/routes/web.php';
            require dirname(__DIR__, 2) . '/routes/api.php';

            // Security headers
            header('X-Content-Type-Options: nosniff');
            header('X-Frame-Options: DENY');
            header('X-XSS-Protection: 1; mode=block');
            header('Referrer-Policy: strict-origin-when-cross-origin');

            $response = $this->router->resolve($this->request);
            $response->send();
        } catch (\Throwable $e) {
            $this->handleException($e);
        }
    }

    private function handleException(\Throwable $e): void
    {
        logger()->error($e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);

        $response = new Response();

        if ($this->request->isAjax() || str_starts_with($this->request->path(), '/api/')) {
            $data = ['success' => false, 'error' => ['code' => 'INTERNAL_ERROR', 'message' => 'Internal server error']];
            if (env('APP_DEBUG')) {
                $data['error']['debug'] = [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ];
            }
            $response->json($data, 500)->send();
        } else {
            if (env('APP_DEBUG')) {
                $response->html(
                    '<h1>Error</h1><pre>' . e($e->getMessage()) . "\n" . e($e->getTraceAsString()) . '</pre>',
                    500
                )->send();
            } else {
                $response->html('<h1>500 Internal Server Error</h1>', 500)->send();
            }
        }
    }
}
