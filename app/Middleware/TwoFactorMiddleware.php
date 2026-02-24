<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Models\User;
use App\Services\SecurityService;

class TwoFactorMiddleware
{
    public function handle(Request $request, callable $next): Response
    {
        $sessionUser = auth();
        if (!$sessionUser) {
            return $next($request);
        }

        $user = User::find($sessionUser['id']);
        if (!$user) {
            return $next($request);
        }

        if (SecurityService::isCriticalBlocked($user)) {
            flash('error', 'Please enable Two-Factor Authentication to access this feature. Your grace period has expired.');
            return (new Response())->redirect('/dashboard/settings');
        }

        return $next($request);
    }
}
