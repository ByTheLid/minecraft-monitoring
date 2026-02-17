<?php

// Ping all active approved servers
// Cron: every 3 minutes

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Env;
use App\Core\Database;
use App\Services\MinecraftPing;
use App\Models\Server;
use App\Models\ServerStat;

Env::load(__DIR__ . '/../.env');

$startTime = microtime(true);

try {
    $servers = Server::getActiveApproved();
    $total = count($servers);
    $online = 0;

    echo "Pinging {$total} servers...\n";

    // Process in batches of 50
    $batches = array_chunk($servers, 50);

    foreach ($batches as $batch) {
        foreach ($batch as $server) {
            $ping = new MinecraftPing($server['ip'], $server['port'], 5);
            $result = $ping->ping();

            // Record stat
            ServerStat::record($server['id'], $result);

            // Update cache
            $db = Database::getInstance();
            $stmt = $db->prepare(
                "INSERT INTO server_status_cache (server_id, is_online, players_online, players_max, version, ping_ms, motd, favicon_base64, last_checked_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
                 ON DUPLICATE KEY UPDATE
                    is_online = VALUES(is_online),
                    players_online = VALUES(players_online),
                    players_max = VALUES(players_max),
                    version = VALUES(version),
                    ping_ms = VALUES(ping_ms),
                    motd = VALUES(motd),
                    favicon_base64 = VALUES(favicon_base64),
                    last_checked_at = NOW()"
            );

            $stmt->execute([
                $server['id'],
                $result['is_online'] ? 1 : 0,
                $result['players_online'],
                $result['players_max'],
                $result['version'],
                $result['ping_ms'],
                $result['motd'],
                $result['favicon'],
            ]);

            if ($result['is_online']) {
                $online++;
            }
        }
    }

    $elapsed = round(microtime(true) - $startTime, 2);
    echo "Done! {$online}/{$total} online. Time: {$elapsed}s\n";
    logger()->info("Ping completed: {$online}/{$total} online, {$elapsed}s");

} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    logger()->error('Ping cron failed: ' . $e->getMessage());
    exit(1);
}
