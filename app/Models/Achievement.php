<?php

namespace App\Models;

use App\Core\Database;

class Achievement
{
    // Dictionary of available achievements
    public const BADGES = [
        'first_vote' => [
            'key' => 'first_vote',
            'title' => 'First Blood',
            'description' => 'Voted for a server for the first time.',
            'icon' => 'fa-solid fa-hand-pointer',
            'color' => '#10b981' // Green
        ],
        'server_owner' => [
            'key' => 'server_owner',
            'title' => 'Server Owner',
            'description' => 'Added a server to the monitoring list.',
            'icon' => 'fa-solid fa-server',
            'color' => '#3b82f6' // Blue
        ],
        'supporter' => [
            'key' => 'supporter',
            'title' => 'Supporter',
            'description' => 'Purchased a boost package.',
            'icon' => 'fa-solid fa-heart',
            'color' => '#ef4444' // Red
        ]
    ];

    /**
     * Unlocks an achievement for a user
     */
    public static function unlock(int $userId, string $key): bool
    {
        if (!isset(self::BADGES[$key])) {
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
            SELECT achievement_key, unlocked_at 
            FROM user_achievements 
            WHERE user_id = ?
            ORDER BY unlocked_at DESC
        ");
        $stmt->execute([$userId]);
        $unlocked = $stmt->fetchAll();

        // Map database keys to full badge details
        $result = [];
        foreach ($unlocked as $row) {
            $key = $row['achievement_key'];
            if (isset(self::BADGES[$key])) {
                $badge = self::BADGES[$key];
                $badge['unlocked_at'] = $row['unlocked_at'];
                $result[] = $badge;
            }
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
