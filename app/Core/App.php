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
    }
}
