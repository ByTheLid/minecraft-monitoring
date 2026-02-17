<?php

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Models\Server;
use App\Models\ServerStat;
use App\Models\Setting;
use App\Services\MinecraftPing;

class RefreshApiController extends Controller
{
    public function refresh(Request $request): Response
    {
        $force = (bool) $request->query('force', '');
        $ip = $request->ip();

        // Rate limit: global 60s, manual (force) 30s per IP
        $lastGlobal = (int) Setting::get('last_refresh_time', '0');
        $now = time();

        if ($force) {
            // Per-IP rate limit: 30 seconds
            $lastIp = (int) Setting::get("last_refresh_ip_{$ip}", '0');
            if ($now - $lastIp < 30) {
                $remaining = 30 - ($now - $lastIp);
                return $this->success([
                    'refreshed' => false,
                    'reason' => 'rate_limited',
                    'retry_after' => $remaining,
                ]);
            }
        } else {
            // Auto-refresh: global 60 seconds
            if ($now - $lastGlobal < 60) {
                return $this->success([
                    'refreshed' => false,
                    'reason' => 'too_soon',
                    'retry_after' => 60 - ($now - $lastGlobal),
                ]);
            }
        }

        // Do the refresh
        Setting::set('last_refresh_time', (string) $now);
        if ($force) {
            Setting::set("last_refresh_ip_{$ip}", (string) $now);
        }

        $servers = Server::getActiveApproved();
        $online = 0;
        $total = count($servers);

        foreach ($servers as $server) {
            $ping = new MinecraftPing($server['ip'], $server['port'], 3);
            $result = $ping->ping();

            ServerStat::record($server['id'], $result);

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

        // Return updated server list
        $topServers = Server::getApproved(1, 10, 'rank');

        return $this->success([
            'refreshed' => true,
            'total' => $total,
            'online' => $online,
            'servers' => $topServers['data'],
        ]);
    }
}
