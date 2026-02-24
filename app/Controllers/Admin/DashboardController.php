<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $db = Database::getInstance();
        
        $stats = [
            'servers' => (int) $db->query("SELECT COUNT(*) FROM servers WHERE is_active = 1")->fetchColumn(),
            'users' => (int) $db->query("SELECT COUNT(*) FROM users")->fetchColumn(),
            'votes' => (int) $db->query("SELECT COUNT(*) FROM votes")->fetchColumn(),
            'boosts_active' => (int) ($db->query("SELECT COUNT(*) FROM boost_purchases WHERE expires_at > NOW()")->fetchColumn() ?: 0),
        ];

        $pendingServers = (int) $db->query("SELECT COUNT(*) FROM servers WHERE is_approved = 0 AND is_active = 1")->fetchColumn();

        $recentServers = $db->query("SELECT name, created_at FROM servers ORDER BY created_at DESC LIMIT 5")->fetchAll();
        $topServers = $db->query("
            SELECT s.name, COUNT(v.id) as vote_count 
            FROM servers s
            LEFT JOIN votes v ON s.id = v.server_id
            GROUP BY s.id
            ORDER BY vote_count DESC 
            LIMIT 5
        ")->fetchAll();

        $health = [
            'php' => PHP_VERSION,
            'db' => $db->getAttribute(\PDO::ATTR_SERVER_VERSION),
            'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
        ];

        // Daemon Status
        $daemonStatusFile = __DIR__ . '/../../../storage/cache/ping_daemon.json';
        $daemonStatus = ['status' => 'stopped'];
        if (file_exists($daemonStatusFile)) {
            $data = json_decode(file_get_contents($daemonStatusFile), true);
            if ($data) {
                // Determine if it is actually alive based on last update (e.g., 5 mins)
                $lastPing = strtotime($data['last_update'] ?? '0');
                if (time() - $lastPing < 300) {
                    $daemonStatus = $data;
                } else {
                    $daemonStatus['status'] = 'dead (timeout)';
                }
            }
        }

        return $this->view('admin.index', [
            'stats' => $stats,
            'pendingServers' => $pendingServers,
            'recentServers' => $recentServers,
            'topServers' => $topServers,
            'health' => $health,
            'daemonStatus' => $daemonStatus
        ]);
    }

    public function daemonAction(Request $request): Response
    {
        $action = $request->input('action');
        
        $cacheDir = __DIR__ . '/../../../storage/cache';
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }

        $cmdFile = escapeshellarg($cacheDir . '/ping_daemon.command');
        $daemonPath = realpath(__DIR__ . '/../../../daemon/ping_service.php');

        if ($action === 'start') {
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                pclose(popen("start /B php " . escapeshellarg($daemonPath) . " > NUL 2>&1", "r"));
            } else {
                exec("nohup php " . escapeshellarg($daemonPath) . " > /dev/null 2>&1 &");
            }
            // Reset status temporarily
            file_put_contents($cacheDir . '/ping_daemon.json', json_encode([
                'status' => 'starting',
                'last_update' => date('Y-m-d H:i:s'),
                'servers_pinged' => 0
            ]));
            flash('success', 'Ping Daemon start signal sent.');
        } elseif ($action === 'stop') {
            file_put_contents($cacheDir . '/ping_daemon.command', 'stop');
            flash('success', 'Ping Daemon stop signal sent. It will shut down gracefully.');
        }

        return $this->redirect('/admin');
    }
}
