<?php

require __DIR__ . '/../vendor/autoload.php';

use React\EventLoop\Loop;
use React\Socket\Connector;
use React\Promise\Promise;
use App\Core\Env;
use App\Core\Database;
use App\Services\AsyncMinecraftPing;

Env::load(__DIR__ . '/../.env');

echo "[Daemon] Starting High-Performance ReactPHP Ping Service...\n";

$connector = new Connector([
    'timeout' => 5.0
]);
$pinger = new AsyncMinecraftPing($connector, 5.0);

// Concurrency settings
$chunkSize = 250; 
$cronIntervalSeconds = 180; // 3 minutes

$cacheDir = __DIR__ . '/../storage/cache';
if (!is_dir($cacheDir)) mkdir($cacheDir, 0777, true);

$updateStatus = function($status, $servers = 0) use ($cacheDir) {
    file_put_contents($cacheDir . '/ping_daemon.json', json_encode([
        'status' => $status,
        'last_update' => date('Y-m-d H:i:s'),
        'servers_pinged' => $servers
    ]));
};

$isRunning = false;

// Command listener
Loop::addPeriodicTimer(3, function() use ($cacheDir, $updateStatus) {
    $cmdFile = $cacheDir . '/ping_daemon.command';
    if (file_exists($cmdFile)) {
        $cmd = trim(file_get_contents($cmdFile));
        if ($cmd === 'stop') {
            unlink($cmdFile);
            $updateStatus('stopped');
            echo "[Daemon] Stop command received. Terminating...\n";
            Loop::stop();
            exit(0);
        }
    }
});

$updateStatus('idle', 0);

$runPingCycle = function() use ($pinger, &$isRunning, $chunkSize, $updateStatus) {
    if ($isRunning) {
        $updateStatus('running (busy)', 0);
        echo "[Daemon] Warning: Previous cycle is still running. Skipping this tick.\n";
        return;
    }
    
    $isRunning = true;
    echo "[Daemon] [" . date('Y-m-d H:i:s') . "] Starting ping cycle...\n";
    $startTime = microtime(true);

    try {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT id, ip, port FROM servers WHERE is_approved = 1");
        $servers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $total = count($servers);
        echo "[Daemon] Found {$total} servers to ping.\n";

        $updateStatus('running', $total);

        if ($total === 0) {
            $isRunning = false;
            $updateStatus('idle', 0);
            return;
        }

        $chunks = array_chunk($servers, $chunkSize);
        $processChunk = function(int $index) use (&$processChunk, $chunks, $pinger, &$isRunning, $startTime, $total, $db) {
            if (!isset($chunks[$index])) {
                // All chunks done — recalculate rankings
                try {
                    $rankingService = \App\Services\RankingService::createFromSettings();
                    $recalculated = $rankingService->recalculateAll();
                    echo "[Daemon] Rankings recalculated for {$recalculated} servers.\n";
                } catch (\Throwable $e) {
                    echo "[Daemon] Rankings recalculation error: " . $e->getMessage() . "\n";
                }

                $elapsed = round(microtime(true) - $startTime, 2);
                echo "[Daemon] [" . date('Y-m-d H:i:s') . "] Cycle finished. Total servers: {$total}, Time: {$elapsed}s\n";
                $isRunning = false;
                $updateStatus('idle', $total);
                return;
            }

            $chunk = $chunks[$index];
            $promises = [];

            foreach ($chunk as $server) {
                $promises[$server['id']] = $pinger->ping(trim($server['ip']), (int)trim($server['port']));
            }

            \React\Promise\all($promises)->then(
                function(array $results) use ($processChunk, $index, $db, $chunk) {
                    // $results is array of [server_id => ping_result]
                    $onlineCount = 0;
                    $sqlCache = "INSERT INTO server_status_cache (server_id, is_online, players_online, players_max, version, ping_ms, motd, favicon_base64, last_checked_at) VALUES ";
                    $valuesCache = [];
                    $paramsCache = [];

                    $sqlStats = "INSERT INTO server_stats (server_id, is_online, players_online, players_max, version, ping_ms, motd, checked_at) VALUES ";
                    $valuesStats = [];
                    $paramsStats = [];

                    foreach ($results as $serverId => $res) {
                        if ($res['is_online']) $onlineCount++;
                        
                        // Cache update (includes motd + favicon)
                        $valuesCache[] = "(?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                        array_push($paramsCache, 
                            $serverId, 
                            $res['is_online'] ? 1 : 0, 
                            $res['players_online'], 
                            $res['players_max'], 
                            $res['version'] ?? '', 
                            $res['ping_ms'] ?? 0,
                            $res['motd'] ?? '',
                            $res['favicon'] ?? null
                        );

                        // History Stats update
                        $valuesStats[] = "(?, ?, ?, ?, ?, ?, ?, NOW())";
                        array_push($paramsStats,
                            $serverId,
                            $res['is_online'] ? 1 : 0,
                            $res['players_online'],
                            $res['players_max'],
                            $res['version'] ?? '',
                            $res['ping_ms'] ?? 0,
                            $res['motd'] ?? ''
                        );
                    }

                    try {
                        $db->beginTransaction();

                        $sqlCache .= implode(", ", $valuesCache) . " ON DUPLICATE KEY UPDATE 
                            is_online = VALUES(is_online),
                            players_online = VALUES(players_online),
                            players_max = VALUES(players_max),
                            version = VALUES(version),
                            ping_ms = VALUES(ping_ms),
                            motd = VALUES(motd),
                            favicon_base64 = VALUES(favicon_base64),
                            last_checked_at = NOW()";

                        $stmtCache = $db->prepare($sqlCache);
                        $stmtCache->execute($paramsCache);

                        $sqlStats .= implode(", ", $valuesStats);
                        $stmtStats = $db->prepare($sqlStats);
                        $stmtStats->execute($paramsStats);

                        $db->commit();
                        
                        echo "[Daemon] Chunk " . ($index + 1) . " processed. Online in chunk: {$onlineCount}/" . count($chunk) . "\n";
                    } catch (\Throwable $e) {
                        if ($db->inTransaction()) {
                            $db->rollBack();
                        }
                        echo "[Daemon] Error inserting chunk " . ($index + 1) . " to DB: " . $e->getMessage() . "\n";
                    }

                    // Process next chunk
                    $processChunk($index + 1);
                },
                function(\Exception $e) use ($processChunk, $index) {
                    echo "[Daemon] Error pinging chunk " . ($index + 1) . ": " . $e->getMessage() . "\n";
                    // Try next chunk anyway
                    $processChunk($index + 1);
                }
            );
        };

        // Start processing recursively
        $processChunk(0);
        
    } catch (\Throwable $e) {
        echo "[Daemon] DB Error: " . $e->getMessage() . "\n";
        $isRunning = false;
    }
};

// Start the first cycle immediately
$runPingCycle();

// Schedule subsequent cycles
Loop::addPeriodicTimer($cronIntervalSeconds, $runPingCycle);

Loop::run();
