<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Services\MinecraftPing;

// Use a reliable public server for testing, e.g., Hypixel
$host = 'mc.hypixel.net';
$port = 25565;

echo "Pinging {$host}:{$port}...\n";

try {
    $pinger = new MinecraftPing($host, $port, 5); // 5s timeout
    $info = $pinger->ping();

    echo "Status: " . ($info['is_online'] ? "ONLINE" : "OFFLINE") . "\n";
    if ($info['is_online']) {
        echo "Version: " . $info['version'] . "\n";
        echo "Players: " . $info['players_online'] . " / " . $info['players_max'] . "\n";
        echo "Latency: " . $info['ping_ms'] . "ms\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
