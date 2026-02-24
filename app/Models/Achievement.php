<?php

namespace App\Models;

use App\Core\Database;

class Achievement
{
    public static function getByKey(string $key): ?array
    {
        $stmt = Database::getInstance()->prepare("SELECT * FROM achievements WHERE achievement_key = ?");
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function getAll(): array
    {
        $stmt = Database::getInstance()->query("SELECT * FROM achievements ORDER BY id ASC");
        return $stmt->fetchAll();
    }

    /**
     * Unlocks an achievement for a user
     */
    public static function unlock(int $userId, string $key): bool
    {
        $badge = self::getByKey($key);
        if (!$badge) {
            return false;
        }

        try {
            $stmt = Database::getInstance()->prepare("
                INSERT IGNORE INTO user_achievements (user_id, achievement_key) 
                VALUES (?, ?)
            ");
            return $stmt->execute([$userId, $key]);
        } catch (\Throwable $e) {
            error_log("Failed to unlock achievement $key for user $userId: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get unlocked achievements for a user
     */
    public static function getForUser(int $userId): array
    {
        $stmt = Database::getInstance()->prepare("
            SELECT ua.unlocked_at, a.* 
            FROM user_achievements ua 
            JOIN achievements a ON a.achievement_key = ua.achievement_key
            WHERE ua.user_id = ?
            ORDER BY ua.unlocked_at DESC
        ");
        $stmt->execute([$userId]);
        $unlocked = $stmt->fetchAll();

        // Ensure backward compatibility with array keys
        $result = [];
        foreach ($unlocked as $row) {
            $row['title'] = $row['name']; // map name to title for existing views
            $row['key'] = $row['achievement_key'];
            $result[] = $row;
        }

        return $result;
    }

    /**
     * Check if user has a specific achievement
     */
    public static function has(int $userId, string $key): bool
    {
        $stmt = Database::getInstance()->prepare("
            SELECT 1 FROM user_achievements 
            WHERE user_id = ? AND achievement_key = ?
        ");
        $stmt->execute([$userId, $key]);
        return (bool) $stmt->fetchColumn();
    }
}
