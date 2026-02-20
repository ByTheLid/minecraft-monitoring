<?php

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Models\Server;

class DashboardApiController extends Controller
{
    public function stats(Request $request): Response
    {
        $user = auth();
        $db = Database::getInstance();

        $servers = Server::getByUser($user['id']);
        $serverIds = array_column($servers, 'id');

        $totalVotes = 0;
        $totalPlayers = 0;

        if ($serverIds) {
            $placeholders = implode(',', array_fill(0, count($serverIds), '?'));

            $stmt = $db->prepare("SELECT COUNT(*) FROM votes WHERE server_id IN ({$placeholders}) AND voted_at > DATE_SUB(NOW(), INTERVAL 30 DAY)");
            $stmt->execute($serverIds);
            $totalVotes = (int) $stmt->fetchColumn();

            $stmt = $db->prepare("SELECT COALESCE(SUM(sc.players_online), 0) FROM server_status_cache sc WHERE sc.server_id IN ({$placeholders})");
            $stmt->execute($serverIds);
            $totalPlayers = (int) $stmt->fetchColumn();
        }

        return $this->success([
            'servers_count' => count($servers),
            'total_votes' => $totalVotes,
            'total_players' => $totalPlayers,
            'servers' => $servers,
        ]);
    }
}
