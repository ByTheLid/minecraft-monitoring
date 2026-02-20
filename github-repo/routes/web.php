<?php

use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\PostController;
use App\Controllers\AdminController;
use App\Middleware\AuthMiddleware;
use App\Middleware\AdminMiddleware;
use App\Middleware\CsrfMiddleware;

// Public pages
$router->get('/', [HomeController::class, 'index']);
$router->get('/servers', [HomeController::class, 'servers']);
$router->get('/server/{id}', [HomeController::class, 'serverDetail']);
$router->get('/posts', [PostController::class, 'index']);
$router->get('/post/{id}', [PostController::class, 'show']);

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
}, [AuthMiddleware::class, CsrfMiddleware::class]);

// Admin (admin required)
$router->group('/admin', function ($router) {
    $router->get('', [AdminController::class, 'index']);
    $router->get('/servers', [AdminController::class, 'servers']);
    $router->get('/servers/{id}', [AdminController::class, 'serverDetail']);
    $router->post('/servers/{id}/edit', [AdminController::class, 'editServer']);
    $router->post('/servers/{id}/approve', [AdminController::class, 'approveServer']);
    $router->post('/servers/{id}/reject', [AdminController::class, 'rejectServer']);
    $router->post('/servers/{id}/unblock', [AdminController::class, 'unblockServer']);
    $router->post('/servers/{id}/reset-votes', [AdminController::class, 'resetVotes']);
    $router->post('/servers/{id}/reset-boosts', [AdminController::class, 'resetBoosts']);
    $router->get('/users', [AdminController::class, 'users']);
    $router->post('/users/{id}/toggle', [AdminController::class, 'toggleUser']);
    $router->post('/users/{id}/role', [AdminController::class, 'changeRole']);
    $router->get('/posts', [AdminController::class, 'posts']);
    $router->get('/posts/create', [AdminController::class, 'createPostForm']);
    $router->post('/posts/create', [AdminController::class, 'createPost']);
    $router->get('/posts/edit/{id}', [AdminController::class, 'editPostForm']);
    $router->post('/posts/edit/{id}', [AdminController::class, 'editPost']);
    $router->post('/posts/delete/{id}', [AdminController::class, 'deletePost']);
    $router->get('/settings', [AdminController::class, 'settings']);
    $router->post('/settings', [AdminController::class, 'updateSettings']);
    $router->get('/boost', [AdminController::class, 'boostPackages']);
    $router->post('/boost/create', [AdminController::class, 'createBoostPackage']);
    $router->post('/boost/edit/{id}', [AdminController::class, 'editBoostPackage']);
    $router->post('/boost/delete/{id}', [AdminController::class, 'deleteBoostPackage']);
}, [AuthMiddleware::class, AdminMiddleware::class, CsrfMiddleware::class]);
