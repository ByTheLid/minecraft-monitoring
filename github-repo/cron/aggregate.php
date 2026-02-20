<?php

/**
 * Aggregate server stats into hourly data
 * Run every hour: 0 * * * * php /path/to/cron/aggregate.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Env;
use App\Core\Database;

Env::load(__DIR__ . '/../.env');

try {
    $db = Database::getInstance();

    // Aggregate for the previous hour
    $hour = date('Y-m-d H:00:00', strtotime('-1 hour'));

    $sql = "INSERT INTO server_stats_hourly (server_id, hour, avg_players, max_players, uptime_percent, avg_ping)
            SELECT
                server_id,
                ? as hour,
                AVG(players_online) as avg_players,
                MAX(players_online) as max_players,
                (SUM(is_online) / COUNT(*)) * 100 as uptime_percent,
                AVG(CASE WHEN ping_ms IS NOT NULL THEN ping_ms END) as avg_ping
            FROM server_stats
            WHERE checked_at >= ? AND checked_at < DATE_ADD(?, INTERVAL 1 HOUR)
            GROUP BY server_id
            ON DUPLICATE KEY UPDATE
                avg_players = VALUES(avg_players),
                max_players = VALUES(max_players),
                uptime_percent = VALUES(uptime_percent),
                avg_ping = VALUES(avg_ping)";

    $stmt = $db->prepare($sql);
    $stmt->execute([$hour, $hour, $hour]);

    $count = $stmt->rowCount();
    echo "Aggregated stats for {$count} servers at {$hour}.\n";
    logger()->info("Hourly aggregation: {$count} servers at {$hour}");

} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    logger()->error('Aggregate cron failed: ' . $e->getMessage());
    exit(1);
}
