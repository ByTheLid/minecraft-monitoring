<?php

use App\Controllers\HealthController;
use App\Controllers\Api\ServerApiController;
use App\Controllers\Api\VoteApiController;
use App\Controllers\Api\RefreshApiController;
use App\Controllers\Api\DashboardApiController;
use App\Controllers\Api\AdminApiController;
use App\Controllers\Api\BoostApiController;
use App\Middleware\AuthMiddleware;
use App\Middleware\AdminMiddleware;
use App\Middleware\CorsMiddleware;

// Health check
$router->get('/health', [HealthController::class, 'index']);

$router->group('/api', function ($router) {
    // Public API
    $router->get('/servers', [ServerApiController::class, 'index']);
    $router->get('/servers/refresh', [RefreshApiController::class, 'refresh']);
    $router->get('/servers/{id}', [ServerApiController::class, 'show']);
    $router->get('/servers/{id}/stats', [ServerApiController::class, 'stats']);
    $router->post('/servers/{id}/vote', [VoteApiController::class, 'vote']);

    // Private API (auth required)
    $router->post('/servers', [ServerApiController::class, 'store'], [AuthMiddleware::class]);
    $router->put('/servers/{id}', [ServerApiController::class, 'update'], [AuthMiddleware::class]);
    $router->delete('/servers/{id}', [ServerApiController::class, 'destroy'], [AuthMiddleware::class]);
    $router->post('/boost/purchase', [BoostApiController::class, 'purchase'], [AuthMiddleware::class]);
    $router->get('/dashboard/stats', [DashboardApiController::class, 'stats'], [AuthMiddleware::class]);

    // Admin API
    $router->group('/admin', function ($router) {
        $router->get('/servers', [AdminApiController::class, 'servers']);
        $router->put('/servers/{id}/approve', [AdminApiController::class, 'approve']);
        $router->put('/settings', [AdminApiController::class, 'updateSettings']);
        $router->get('/logs', [AdminApiController::class, 'logs']);
    }, [AuthMiddleware::class, AdminMiddleware::class]);
}, [CorsMiddleware::class]);
