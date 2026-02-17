<?php

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Models\Server;
use App\Models\Setting;

class RefreshApiController extends Controller
{
    /**
     * GET /api/servers/refresh[?force=1]
     *
     * Always returns cached server data instantly (never pings inline).
     * If cooldown passed, triggers cron/ping.php as a background process.
     * Scales to thousands of servers without blocking the HTTP response.
     */
    public function refresh(Request $request): Response
    {
        $force = (bool) $request->query('force', '');
        $ip = $request->ip();
        $now = time();

        $pingTriggered = false;
        $lastGlobal = (int) Setting::get('last_refresh_time', '0');

        if ($force) {
            // Per-IP rate limit: 30 seconds
            $lastIp = (int) Setting::get("last_refresh_ip_{$ip}", '0');
            if ($now - $lastIp < 30) {
                return $this->success([
                    'refreshed' => false,
                    'reason' => 'rate_limited',
                    'retry_after' => 30 - ($now - $lastIp),
                ]);
            }
            Setting::set("last_refresh_ip_{$ip}", (string) $now);

            // Trigger background ping if global cooldown passed
            if ($now - $lastGlobal >= 60) {
                $pingTriggered = $this->triggerBackgroundPing();
                if ($pingTriggered) {
                    Setting::set('last_refresh_time', (string) $now);
                }
            }
        } else {
            // Auto-refresh: trigger background ping if >60s since last
            if ($now - $lastGlobal >= 60) {
                $pingTriggered = $this->triggerBackgroundPing();
                if ($pingTriggered) {
                    Setting::set('last_refresh_time', (string) $now);
                }
            }
        }

        // Always return current cached data instantly
        $topServers = Server::getApproved(1, 10, 'rank');
        $stats = $this->getStats();

        return $this->success([
            'refreshed' => true,
            'ping_triggered' => $pingTriggered,
            'total' => $stats['total'],
            'online' => $stats['online'],
            'servers' => $topServers['data'],
        ]);
    }

    /**
     * Launch cron/ping.php as a non-blocking background process.
     * Works on Windows (popen) and Linux (exec &).
     */
    private function triggerBackgroundPing(): bool
    {
        $pingScript = dirname(__DIR__, 3) . '/cron/ping.php';

        if (!file_exists($pingScript)) {
            return false;
        }

        // Prevent concurrent pings via lock file
        $lockFile = dirname(__DIR__, 3) . '/storage/cache/ping.lock';
        if (file_exists($lockFile)) {
            $lockAge = time() - (int) filemtime($lockFile);
            // Lock expires after 5 minutes (safety net)
            if ($lockAge < 300) {
                return false;
            }
        }

        $phpBinary = PHP_BINARY ?: 'php';

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $cmd = '"' . $phpBinary . '" "' . $pingScript . '"';
            pclose(popen('start /B ' . $cmd, 'r'));
        } else {
            $cmd = escapeshellarg($phpBinary) . ' ' . escapeshellarg($pingScript);
            exec($cmd . ' > /dev/null 2>&1 &');
        }

        return true;
    }

    /**
     * Get online/total counts from cache (fast, indexed queries).
     */
    private function getStats(): array
    {
        $db = Database::getInstance();

        $online = (int) $db->query(
            "SELECT COUNT(*) FROM server_status_cache WHERE is_online = 1"
        )->fetchColumn();

        $total = (int) $db->query(
            "SELECT COUNT(*) FROM servers WHERE is_active = 1 AND is_approved = 1"
        )->fetchColumn();

        return compact('online', 'total');
    }
}
