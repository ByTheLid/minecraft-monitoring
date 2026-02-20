<?php

use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\PostController; // Public post controller
use App\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Controllers\Admin\ServerController as AdminServerController;
use App\Controllers\Admin\UserController as AdminUserController;
use App\Controllers\Admin\PostController as AdminPostController;
use App\Controllers\Admin\BoostController as AdminBoostController;
use App\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Controllers\DesignController;
use App\Middleware\AuthMiddleware;
use App\Middleware\AdminMiddleware;
use App\Middleware\CsrfMiddleware;

// Public pages
$router->get('/', [HomeController::class, 'index']);
$router->get('/servers', [HomeController::class, 'servers']);
$router->get('/server/{id}', [HomeController::class, 'serverDetail']);
$router->get('/posts', [PostController::class, 'index']);
$router->get('/post/{id}', [PostController::class, 'show']);
$router->get('/user/{username}', [\App\Controllers\ProfileController::class, 'show']);

// Utility routes
$router->post('/design/toggle', [DesignController::class, 'toggle']);

// Auth pages
$router->get('/login', [AuthController::class, 'loginForm']);
$router->post('/login', [AuthController::class, 'login'], [CsrfMiddleware::class]);
$router->get('/register', [AuthController::class, 'registerForm']);
$router->post('/register', [AuthController::class, 'register'], [CsrfMiddleware::class]);
$router->get('/logout', [AuthController::class, 'logout']);

// Dashboard (auth required)
$router->group('/dashboard', function ($router) {
    $router->get('', [DashboardController::class, 'index']);
    $router->get('/add', [DashboardController::class, 'addServerForm']);
    $router->post('/add', [DashboardController::class, 'addServer']);
    $router->get('/edit/{id}', [DashboardController::class, 'editServerForm']);
    $router->post('/edit/{id}', [DashboardController::class, 'editServer']);

    $router->post('/delete/{id}', [DashboardController::class, 'deleteServer']);
    $router->get('/server/{id}/boost', [\App\Controllers\User\BoostController::class, 'storeForm']);
    $router->post('/server/{id}/boost/purchase', [\App\Controllers\User\BoostController::class, 'purchase']);
    
    $router->get('/settings', [DashboardController::class, 'settings']);
    $router->post('/settings', [DashboardController::class, 'updateSettings']);
}, [AuthMiddleware::class, CsrfMiddleware::class]);

// Admin (admin required)
$router->group('/admin', function ($router) {
    // DashboardStats
    $router->get('', [AdminDashboardController::class, 'index']);
    
    // Servers
    $router->get('/servers', [AdminServerController::class, 'index']);
    $router->post('/servers/{id}/approve', [AdminServerController::class, 'approve']);
    $router->post('/servers/{id}/reject', [AdminServerController::class, 'reject']);
    $router->post('/servers/{id}/unblock', [AdminServerController::class, 'unblock']);
    $router->post('/servers/{id}/vote', [AdminServerController::class, 'manualVote']);
    $router->post('/servers/{id}/boost', [AdminServerController::class, 'manualBoost']);

    // Users
    $router->get('/users', [AdminUserController::class, 'index']);
    $router->post('/users/{id}/toggle', [AdminUserController::class, 'toggle']);

    // Posts
    $router->get('/posts', [AdminPostController::class, 'index']);
    $router->get('/posts/create', [AdminPostController::class, 'createForm']);
    $router->post('/posts/create', [AdminPostController::class, 'create']);
    $router->get('/posts/edit/{id}', [AdminPostController::class, 'editForm']);
    $router->post('/posts/edit/{id}', [AdminPostController::class, 'edit']);
    $router->post('/posts/delete/{id}', [AdminPostController::class, 'delete']);

    // Settings & Analytics
    $router->get('/settings', [AdminSettingsController::class, 'index']);
    $router->post('/settings', [AdminSettingsController::class, 'update']);
    $router->get('/analytics', [\App\Controllers\Admin\AnalyticsController::class, 'index']);
    
    // Boost Packages
    $router->get('/boost', [AdminBoostController::class, 'index']);
    $router->post('/boost/create', [AdminBoostController::class, 'create']);
    $router->post('/boost/edit/{id}', [AdminBoostController::class, 'edit']);
    $router->post('/boost/delete/{id}', [AdminBoostController::class, 'delete']);
    $router->post('/boost/activate/{id}', [AdminBoostController::class, 'activate']);
    $router->post('/boost/deactivate/{id}', [AdminBoostController::class, 'deactivate']);

}, [AuthMiddleware::class, AdminMiddleware::class, CsrfMiddleware::class]);
