<?php

// Ping all active approved servers
// Cron: every 3 minutes
// Also triggered on-demand from RefreshApiController (background process)

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Env;
use App\Core\Database;
use App\Services\MinecraftPing;
use App\Models\Server;
use App\Models\ServerStat;

Env::load(__DIR__ . '/../.env');

// --- Lock file to prevent concurrent runs ---
$lockFile = __DIR__ . '/../storage/cache/ping.lock';
if (file_exists($lockFile)) {
    $lockAge = time() - (int) filemtime($lockFile);
    if ($lockAge < 300) {
        echo "Another ping process is running (lock age: {$lockAge}s). Skipping.\n";
        exit(0);
    }
}
file_put_contents($lockFile, (string) getmypid());

// Remove lock on exit (normal or error)
register_shutdown_function(function () use ($lockFile) {
    @unlink($lockFile);
});

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
            $ping = new MinecraftPing($server['ip'], $server['port'], 3);
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
