<?php

namespace App\Core;

use App\Models\Achievement;

class AchievementEngine
{
    /**
     * Get setting with JSON decode fallback
     */
    public static function getActionCaps(): array
    {
        return setting_json('gamification_action_caps', [
            'vote' => 3, 'review' => 1, 'daily_login' => 1
        ]);
    }

    public static function getPointsPerAction(): array
    {
        return setting_json('gamification_points_per_action', [
            'vote' => 10, 'review' => 25, 'daily_login' => 5, 'add_server' => 50, 'buy_boost' => 100
        ]);
    }

    public static function getRankThresholds(): array
    {
        $thresholds = setting_json('gamification_rank_thresholds', [
            0 => 'Novice', 100 => 'Bronze', 500 => 'Silver', 1500 => 'Gold', 5000 => 'Diamond', 10000 => 'Legendary'
        ]);
        ksort($thresholds, SORT_NUMERIC);
        return $thresholds;
    }

    /**
     * Add points with diminishing returns/caps
     */
    public static function addPoints(int $userId, string $action): bool
    {
        $pointsConfig = self::getPointsPerAction();
        if (!isset($pointsConfig[$action])) {
            return false; // Unknown action
        }

        $db = Database::getInstance();
        
        // Check daily cap if applicable
        $capsConfig = self::getActionCaps();
        if (isset($capsConfig[$action])) {
            $cap = $capsConfig[$action];
            $stmt = $db->prepare("
                SELECT COUNT(*) FROM user_points_log 
                WHERE user_id = ? AND action = ? AND DATE(created_at) = CURDATE()
            ");
            $stmt->execute([$userId, $action]);
            $todayCount = (int) $stmt->fetchColumn();

            if ($todayCount >= $cap) {
                return false; // Reached daily limit for this action, no points awarded
            }
        }

        $points = $pointsConfig[$action];

        try {
            $db->beginTransaction();

            // Log action
            $stmt = $db->prepare("INSERT INTO user_points_log (user_id, action, points) VALUES (?, ?, ?)");
            $stmt->execute([$userId, $action, $points]);

            // Update user total points
            $stmt = $db->prepare("UPDATE users SET points = points + ? WHERE id = ?");
            $stmt->execute([$points, $userId]);

            $db->commit();

            // Recalculate rank after points update
            self::updateRank($userId);

            return true;
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log("Failed to add points for user $userId: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Calculate and update public rank based on points
     */
    public static function updateRank(int $userId): void
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT points, `rank` FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if (!$user) return;

        $points = (int) $user['points'];
        $currentRank = $user['rank'];
        
        $newRank = 'Новичок';
        $rankKey = null;

        // Find highest applicable rank
        $thresholds = self::getRankThresholds();
        foreach ($thresholds as $threshold => $rankName) {
            if ($points >= $threshold) {
                $newRank = $rankName;
                if ($threshold == 500) $rankKey = 'rank_silver';
                if ($threshold == 1500) $rankKey = 'rank_gold';
                if ($threshold == 5000) $rankKey = 'rank_diamond';
                if ($threshold == 10000) $rankKey = 'rank_legendary';
            }
        }

        // If rank changed, update and potentially unlock achievement
        if ($newRank !== $currentRank) {
            $update = $db->prepare("UPDATE users SET `rank` = ? WHERE id = ?");
            $update->execute([$newRank, $userId]);

            if ($rankKey && !Achievement::has($userId, $rankKey)) {
                Achievement::unlock($userId, $rankKey);
                // Trigger flash notification? Might be done in middleware/controller
            }
        }
    }
}
