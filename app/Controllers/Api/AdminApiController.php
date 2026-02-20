<?php

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Models\Server;
use App\Models\Setting;

class AdminApiController extends Controller
{
    public function servers(Request $request): Response
    {
        $page = max(1, (int) $request->query('page', 1));
        $filter = $request->query('filter', 'all');
        $result = Server::getAllForAdmin($page, 20, $filter);

        return $this->success($result['data'], $result['meta']);
    }

    public function approve(Request $request): Response
    {
        $id = (int) $request->param('id');
        Server::update($id, ['is_approved' => 1]);
        return $this->success(['id' => $id, 'is_approved' => true]);
    }

    public function updateSettings(Request $request): Response
    {
        $data = $request->all();
        $allowedKeys = ['rank_kv', 'rank_kb', 'rank_ko', 'rank_ku', 'max_servers_per_user'];

        foreach ($data as $key => $value) {
            if (in_array($key, $allowedKeys)) {
                Setting::set($key, (string) $value);
            }
        }

        return $this->success(null);
    }

    public function dashboardStats(Request $request): Response
    {
        $db = Database::getInstance();

        $registrations = $db->query(
            "SELECT DATE(created_at) as date, COUNT(*) as count
             FROM users
             WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
             GROUP BY DATE(created_at)
             ORDER BY date"
        )->fetchAll();

        $votes = $db->query(
            "SELECT DATE(voted_at) as date, COUNT(*) as count
             FROM votes
             WHERE voted_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
             GROUP BY DATE(voted_at)
             ORDER BY date"
        )->fetchAll();

        $recentUsers = $db->query(
            "SELECT 'registration' as type, username as label, created_at as time
             FROM users ORDER BY created_at DESC LIMIT 5"
        )->fetchAll();

        $recentServers = $db->query(
            "SELECT 'server_added' as type, name as label, created_at as time
             FROM servers ORDER BY created_at DESC LIMIT 5"
        )->fetchAll();

        $recentVotes = $db->query(
            "SELECT 'vote' as type, s.name as label, v.voted_at as time
             FROM votes v
             JOIN servers s ON v.server_id = s.id
             ORDER BY v.voted_at DESC LIMIT 5"
        )->fetchAll();

        $activity = array_merge($recentUsers, $recentServers, $recentVotes);
        usort($activity, fn($a, $b) => strtotime($b['time']) - strtotime($a['time']));
        $activity = array_slice($activity, 0, 10);

        return $this->success([
            'registrations' => $registrations,
            'votes' => $votes,
            'activity' => $activity,
        ]);
    }

    public function logs(Request $request): Response
    {
        $date = $request->query('date', date('Y-m-d'));
        $logFile = dirname(__DIR__, 3) . "/storage/logs/{$date}.log";

        if (!file_exists($logFile)) {
            return $this->success([]);
        }

        $lines = array_slice(file($logFile, FILE_IGNORE_NEW_LINES), -100);
        return $this->success(array_reverse($lines));
    }
}
