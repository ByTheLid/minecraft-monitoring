<?php

namespace App\Services;

use App\Models\User;
use App\Models\Session;
use App\Core\Database;

class AuthService
{
    public static function attempt(string $login, string $password, string $ip, string $userAgent): ?array
    {
        // Check brute force
        if (static::isRateLimited($ip)) {
            return null;
        }

        // Find user by username or email
        $user = User::findByUsername($login) ?? User::findByEmail($login);

        if (!$user || !$user['is_active']) {
            static::recordFailedAttempt($ip);
            return null;
        }

        if (!User::verifyPassword($user, $password)) {
            static::recordFailedAttempt($ip);
            return null;
        }

        // Create session
        $sessionId = Session::createSession($user['id'], $ip, $userAgent);

        // Set cookie
        $lifetime = (int) config('session.lifetime', 86400);
        setcookie(config('session.cookie', 'mc_session'), $sessionId, [
            'expires' => time() + $lifetime,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Strict',
        ]);

        // Store in PHP session
        $_SESSION['user'] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'role' => $user['role'],
        ];

        return $user;
    }

    public static function check(string $cookieName): void
    {
        if (isset($_SESSION['user'])) {
            return;
        }

        $sessionId = $_COOKIE[$cookieName] ?? null;
        if (!$sessionId) {
            return;
        }

        $session = Session::findValid($sessionId);
        if (!$session) {
            // Clear invalid cookie
            setcookie($cookieName, '', ['expires' => time() - 3600, 'path' => '/']);
            return;
        }

        $_SESSION['user'] = [
            'id' => $session['uid'],
            'username' => $session['username'],
            'email' => $session['email'],
            'role' => $session['role'],
        ];
    }

    public static function logout(): void
    {
        $cookieName = config('session.cookie', 'mc_session');
        $sessionId = $_COOKIE[$cookieName] ?? null;

        if ($sessionId) {
            Session::destroy($sessionId);
            setcookie($cookieName, '', ['expires' => time() - 3600, 'path' => '/']);
        }

        unset($_SESSION['user']);
        session_destroy();
    }

    private static function isRateLimited(string $ip): bool
    {
        try {
            $db = Database::getInstance();
            $key = 'auth:' . $ip;
            $stmt = $db->prepare("SELECT hits, expires_at FROM rate_limits WHERE `key` = ?");
            $stmt->execute([$key]);
            $row = $stmt->fetch();

            if ($row && strtotime($row['expires_at']) > time() && $row['hits'] >= 5) {
                return true;
            }
        } catch (\Throwable) {
            // Table might not exist yet
        }

        return false;
    }

    private static function recordFailedAttempt(string $ip): void
    {
        try {
            $db = Database::getInstance();
            $key = 'auth:' . $ip;

            $stmt = $db->prepare("SELECT hits FROM rate_limits WHERE `key` = ? AND expires_at > NOW()");
            $stmt->execute([$key]);
            $row = $stmt->fetch();

            if ($row) {
                $db->prepare("UPDATE rate_limits SET hits = hits + 1 WHERE `key` = ?")->execute([$key]);
            } else {
                $db->prepare(
                    "INSERT INTO rate_limits (`key`, hits, expires_at) VALUES (?, 1, DATE_ADD(NOW(), INTERVAL 15 MINUTE))
                     ON DUPLICATE KEY UPDATE hits = 1, expires_at = DATE_ADD(NOW(), INTERVAL 15 MINUTE)"
                )->execute([$key]);
            }
        } catch (\Throwable) {
            // Table might not exist yet
        }
    }
}
