<?php

namespace App\Models;

use App\Core\Model;

class User extends Model
{
    protected static string $table = 'users';

    public static function findByUsername(string $username): ?array
    {
        return static::findBy('username', $username);
    }

    public static function findByEmail(string $email): ?array
    {
        return static::findBy('email', $email);
    }

    public static function register(string $username, string $email, string $password): int
    {
        return static::create([
            'username' => $username,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_BCRYPT),
        ]);
    }

    public static function verifyPassword(array $user, string $password): bool
    {
        return password_verify($password, $user['password_hash']);
    }

    public static function isUsernameTaken(string $username): bool
    {
        return static::findByUsername($username) !== null;
    }

    public static function isEmailTaken(string $email): bool
    {
        return static::findByEmail($email) !== null;
    }
    public static function createPasswordResetToken(string $email, string $token): void
    {
        // First delete any existing tokens for this email
        static::db()->prepare("DELETE FROM password_resets WHERE email = ?")->execute([$email]);

        // Insert new token
        static::db()->prepare("INSERT INTO password_resets (email, token) VALUES (?, ?)")->execute([$email, $token]);
    }

    public static function verifyPasswordResetToken(string $token): ?string
    {
        // Token is valid for 1 hour (3600 seconds)
        $stmt = static::db()->prepare("SELECT email FROM password_resets WHERE token = ? AND created_at >= NOW() - INTERVAL 1 HOUR LIMIT 1");
        $stmt->execute([$token]);
        $result = $stmt->fetchColumn();

        return $result ?: null;
    }

    public static function deletePasswordResetTokens(string $email): void
    {
        static::db()->prepare("DELETE FROM password_resets WHERE email = ?")->execute([$email]);
    }

    public static function updatePasswordByEmail(string $email, string $newPassword): bool
    {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = static::db()->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
        return $stmt->execute([$hash, $email]);
    }

    public static function getAvatar(array $user, array $achievements = []): array
    {
        $bgColor = '374151'; // Default gray
        $color = 'ffffff';
        $border = '';
        
        $achievementKeys = array_column($achievements, 'key');
        
        if (in_array('supporter', $achievementKeys)) {
            $bgColor = 'dc2626'; // Red
            $border = 'border: 3px solid #ef4444; box-shadow: 0 0 12px rgba(239,68,68,0.6);';
        } elseif (in_array('server_owner', $achievementKeys)) {
            $bgColor = '2563eb'; // Blue
            $border = 'border: 3px solid #3b82f6; box-shadow: 0 0 12px rgba(59,130,246,0.6);';
        } elseif (in_array('first_vote', $achievementKeys)) {
            $bgColor = '059669'; // Green
            $border = 'border: 2px solid #10b981;';
        }

        if (isset($user['role']) && $user['role'] === 'admin') {
            $bgColor = 'd97706'; // Gold
            $border = 'border: 3px dashed #f59e0b; box-shadow: 0 0 15px rgba(245,158,11,0.8);';
        }

        $url = "https://ui-avatars.com/api/?name=" . urlencode($user['username']) . "&background={$bgColor}&color={$color}&bold=true&format=svg";

        return [
            'url' => $url,
            'style' => $border
        ];
    }
}
