<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class Review extends Model
{
    protected static string $table = 'server_reviews';

    public static function getByServerId(int $serverId, int $limit = 50, int $offset = 0): array
    {
        $sql = "SELECT r.*, u.username 
                FROM " . static::$table . " r
                JOIN users u ON r.user_id = u.id
                WHERE r.server_id = ?
                ORDER BY r.created_at DESC
                LIMIT ? OFFSET ?";
        
        $stmt = static::db()->prepare($sql);
        $stmt->bindValue(1, $serverId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->bindValue(3, $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    public static function hasUserReviewed(int $serverId, int $userId): bool
    {
        $sql = "SELECT 1 FROM " . static::$table . " WHERE server_id = ? AND user_id = ? LIMIT 1";
        $stmt = static::db()->prepare($sql);
        $stmt->execute([$serverId, $userId]);
        return (bool) $stmt->fetchColumn();
    }

    public static function getAverageRating(int $serverId): float
    {
        $sql = "SELECT AVG(rating) FROM " . static::$table . " WHERE server_id = ?";
        $stmt = static::db()->prepare($sql);
        $stmt->execute([$serverId]);
        $avg = $stmt->fetchColumn();
        return $avg ? (float) $avg : 0.0;
    }
    
    public static function countByServerId(int $serverId): int
    {
        $sql = "SELECT COUNT(*) FROM " . static::$table . " WHERE server_id = ?";
        $stmt = static::db()->prepare($sql);
        $stmt->execute([$serverId]);
        return (int) $stmt->fetchColumn();
    }
}
