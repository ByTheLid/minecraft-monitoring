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
$router->get('/users', [\App\Controllers\UserController::class, 'index']);
$router->get('/user/{username}', [\App\Controllers\ProfileController::class, 'show']);
$router->get('/sitemap.xml', [\App\Controllers\SitemapController::class, 'index']);
$router->get('/leaderboard', [\App\Controllers\LeaderboardController::class, 'index']);
$router->get('/servers/{category}/{value}', [\App\Controllers\SeoController::class, 'filterPage']);

// Internal API Routes (for the site itself)
$router->get('/api/server/{id}/banner.png', [\App\Controllers\Api\BannerController::class, 'generate']);
$router->get('/api/server/{id}/analytics', [\App\Controllers\Api\AnalyticsApiController::class, 'getChart']);

// Public Developer API Routes (JSON)
$router->group('/api/v1', function ($router) {
    $router->get('/servers', [\App\Controllers\Api\PublicApiController::class, 'servers']);
    $router->get('/server/{id}', [\App\Controllers\Api\PublicApiController::class, 'server']);
}, [\App\Middleware\CorsMiddleware::class, \App\Middleware\ApiKeyMiddleware::class, \App\Middleware\RateLimitMiddleware::class]);

// Reviews
$router->post('/server/{id}/review', [\App\Controllers\ReviewController::class, 'store'], [AuthMiddleware::class, CsrfMiddleware::class]);

// Utility routes
$router->post('/design/toggle', [DesignController::class, 'toggle']);

// Auth pages
$router->get('/login', [AuthController::class, 'loginForm']);
$router->post('/login', [AuthController::class, 'login'], [CsrfMiddleware::class]);
$router->get('/register', [AuthController::class, 'registerForm']);
$router->post('/register', [AuthController::class, 'register'], [CsrfMiddleware::class]);
$router->post('/logout', [AuthController::class, 'logout'], [CsrfMiddleware::class]);

// 2FA Verification (public — during login)
$router->get('/2fa/verify', [\App\Controllers\TwoFactorController::class, 'verifyForm']);
$router->post('/2fa/verify', [\App\Controllers\TwoFactorController::class, 'verify'], [CsrfMiddleware::class]);

// Password Reset
$router->get('/forgot-password', [AuthController::class, 'forgotPasswordForm']);
$router->post('/forgot-password', [AuthController::class, 'sendResetLink'], [CsrfMiddleware::class]);
$router->get('/reset-password', [AuthController::class, 'resetPasswordForm']);
$router->post('/reset-password', [AuthController::class, 'resetPassword'], [CsrfMiddleware::class]);

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
    
    $router->get('/api-keys', [DashboardController::class, 'apiKeys']);
    $router->post('/api-keys/generate', [DashboardController::class, 'generateApiKey']);
    $router->post('/api-keys/revoke', [DashboardController::class, 'revokeApiKey']);

    // Server Verification
    $router->get('/verify/{id}', [DashboardController::class, 'verifyServerForm']);
    $router->post('/verify/{id}', [DashboardController::class, 'verifyServer']);

    // Two-Factor Authentication
    $router->get('/2fa/setup', [\App\Controllers\TwoFactorController::class, 'setup']);
    $router->post('/2fa/enable', [\App\Controllers\TwoFactorController::class, 'enable']);
    $router->post('/2fa/disable', [\App\Controllers\TwoFactorController::class, 'disable']);
}, [AuthMiddleware::class, CsrfMiddleware::class]);

// Admin (admin required)
$router->group('/admin', function ($router) {
    // DashboardStats
    $router->get('', [AdminDashboardController::class, 'index']);
    $router->post('/daemon', [AdminDashboardController::class, 'daemonAction']);
    
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

    // Reviews (Admin)
    $router->get('/reviews', [\App\Controllers\Admin\ReviewController::class, 'index']);
    $router->post('/reviews/{id}/delete', [\App\Controllers\Admin\ReviewController::class, 'delete']);

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

    // Achievements CRUD
    $router->get('/achievements', [\App\Controllers\Admin\AchievementController::class, 'index']);
    $router->post('/achievements/create', [\App\Controllers\Admin\AchievementController::class, 'create']);
    $router->post('/achievements/edit/{id}', [\App\Controllers\Admin\AchievementController::class, 'edit']);
    $router->post('/achievements/delete/{id}', [\App\Controllers\Admin\AchievementController::class, 'delete']);

    // SEO Pages
    $router->get('/seo', [\App\Controllers\Admin\SeoController::class, 'index']);
    $router->post('/seo/store', [\App\Controllers\Admin\SeoController::class, 'store']);
    $router->post('/seo/update/{id}', [\App\Controllers\Admin\SeoController::class, 'update']);
    $router->post('/seo/delete/{id}', [\App\Controllers\Admin\SeoController::class, 'delete']);
    $router->post('/seo/recalculate', [\App\Controllers\Admin\SeoController::class, 'recalculate']);

}, [AuthMiddleware::class, AdminMiddleware::class, CsrfMiddleware::class]);
