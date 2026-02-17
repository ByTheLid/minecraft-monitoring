<?php

namespace App\Models;

use App\Core\Model;

class Session extends Model
{
    protected static string $table = 'sessions';
    protected static string $primaryKey = 'id';

    public static function createSession(int $userId, string $ip, string $userAgent): string
    {
        $id = bin2hex(random_bytes(32));
        $lifetime = (int) config('session.lifetime', 86400);

        static::db()->prepare(
            "INSERT INTO sessions (id, user_id, ip_address, user_agent, expires_at) VALUES (?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL ? SECOND))"
        )->execute([$id, $userId, $ip, substr($userAgent, 0, 255), $lifetime]);

        return $id;
    }

    public static function findValid(string $id): ?array
    {
        $stmt = static::db()->prepare(
            "SELECT s.*, u.id as uid, u.username, u.email, u.role, u.is_active
             FROM sessions s
             JOIN users u ON s.user_id = u.id
             WHERE s.id = ? AND s.expires_at > NOW() AND u.is_active = 1"
        );
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public static function destroy(string $id): void
    {
        static::db()->prepare("DELETE FROM sessions WHERE id = ?")->execute([$id]);
    }

    public static function destroyForUser(int $userId): void
    {
        static::db()->prepare("DELETE FROM sessions WHERE user_id = ?")->execute([$userId]);
    }

    public static function cleanExpired(): void
    {
        static::db()->exec("DELETE FROM sessions WHERE expires_at < NOW()");
    }
}
