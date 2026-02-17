<?php

namespace App\Models;

use App\Core\Model;

class Vote extends Model
{
    protected static string $table = 'votes';

    public static function hasVotedRecently(int $serverId, ?int $userId, string $ip): bool
    {
        $sql = "SELECT COUNT(*) FROM votes WHERE server_id = ? AND voted_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)";
        $params = [$serverId];

        if ($userId) {
            $sql .= " AND (user_id = ? OR ip_address = ?)";
            $params[] = $userId;
            $params[] = $ip;
        } else {
            $sql .= " AND ip_address = ?";
            $params[] = $ip;
        }

        $stmt = static::db()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    public static function countVotesFromIp(string $ip, int $hours = 1): int
    {
        $stmt = static::db()->prepare(
            "SELECT COUNT(*) FROM votes WHERE ip_address = ? AND voted_at > DATE_SUB(NOW(), INTERVAL ? HOUR)"
        );
        $stmt->execute([$ip, $hours]);
        return (int) $stmt->fetchColumn();
    }

    public static function castVote(int $serverId, ?int $userId, string $ip): int
    {
        return static::create([
            'server_id' => $serverId,
            'user_id' => $userId,
            'ip_address' => $ip,
        ]);
    }

    public static function countForServer(int $serverId, int $days = 30): int
    {
        $stmt = static::db()->prepare(
            "SELECT COUNT(*) FROM votes WHERE server_id = ? AND voted_at > DATE_SUB(NOW(), INTERVAL ? DAY)"
        );
        $stmt->execute([$serverId, $days]);
        return (int) $stmt->fetchColumn();
    }

    public static function getMonthlyStats(int $serverId): array
    {
        $stmt = static::db()->prepare(
            "SELECT DATE(voted_at) as date, COUNT(*) as count
             FROM votes
             WHERE server_id = ? AND voted_at > DATE_SUB(NOW(), INTERVAL 30 DAY)
             GROUP BY DATE(voted_at)
             ORDER BY date"
        );
        $stmt->execute([$serverId]);
        return $stmt->fetchAll();
    }
}
