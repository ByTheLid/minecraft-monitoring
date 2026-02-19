<?php

namespace App\Models;

use App\Core\Model;

class ServerStat extends Model
{
    protected static string $table = 'server_stats';

    public static function record(int $serverId, array $data): int
    {
        return static::create([
            'server_id' => $serverId,
            'is_online' => $data['is_online'] ? 1 : 0,
            'players_online' => $data['players_online'] ?? 0,
            'players_max' => $data['players_max'] ?? 0,
            'version' => $data['version'] ?? null,
            'ping_ms' => $data['ping_ms'] ?? null,
            'motd' => $data['motd'] ?? null,
        ]);
    }

    public static function getHistory(int $serverId, string $period = '24h'): array
    {
        $interval = match ($period) {
            '7d' => '7 DAY',
            '30d' => '30 DAY',
            default => '24 HOUR',
        };

        // For short periods use raw data, for longer use hourly aggregates
        if ($period === '24h') {
            $stmt = static::db()->prepare(
                "SELECT is_online, players_online, players_max, ping_ms, checked_at
                 FROM server_stats
                 WHERE server_id = ? AND checked_at > DATE_SUB(NOW(), INTERVAL {$interval})
                 ORDER BY checked_at"
            );
            $stmt->execute([$serverId]);
        } else {
            $stmt = static::db()->prepare(
                "SELECT 
                    avg_players as players_online, 
                    max_players as players_max, 
                    uptime_percent, 
                    avg_ping as ping_ms, 
                    hour as checked_at
                 FROM server_stats_hourly
                 WHERE server_id = ? AND hour > DATE_SUB(NOW(), INTERVAL {$interval})
                 ORDER BY hour"
            );
            $stmt->execute([$serverId]);
        }

        return $stmt->fetchAll();
    }

    public static function cleanOld(int $days = 30): int
    {
        $stmt = static::db()->prepare(
            "DELETE FROM server_stats WHERE checked_at < DATE_SUB(NOW(), INTERVAL ? DAY)"
        );
        $stmt->execute([$days]);
        return $stmt->rowCount();
    }
}
