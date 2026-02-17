<?php

namespace App\Models;

use App\Core\Model;

class BoostPurchase extends Model
{
    protected static string $table = 'boost_purchases';

    public static function getActiveForServer(int $serverId): int
    {
        $stmt = static::db()->prepare(
            "SELECT COALESCE(SUM(points), 0) FROM boost_purchases WHERE server_id = ? AND expires_at > NOW()"
        );
        $stmt->execute([$serverId]);
        return (int) $stmt->fetchColumn();
    }

    public static function purchase(int $userId, int $serverId, int $packageId, int $points, int $durationDays): int
    {
        return static::create([
            'user_id' => $userId,
            'server_id' => $serverId,
            'package_id' => $packageId,
            'points' => $points,
            'expires_at' => date('Y-m-d H:i:s', strtotime("+{$durationDays} days")),
        ]);
    }
}
