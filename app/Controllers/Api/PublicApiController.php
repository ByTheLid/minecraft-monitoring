<?php

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Server;
use App\Models\ServerStat;

class PublicApiController extends Controller
{
    public function server(Request $request): Response
    {
        $id = (int) $request->param('id');
        $server = Server::getDetail($id);

        if (!$server || !$server['is_approved'] || !$server['is_active']) {
            return $this->error('NOT_FOUND', 'Server not found', [], 404);
        }

        // Get 24h history for the sparkline/chart if needed
        $history = ServerStat::getHistory($id, '24h');

        // Build a safe public array
        $publicData = [
            'id' => $server['id'],
            'name' => $server['name'],
            'ip' => $server['ip'],
            'port' => $server['port'],
            'version' => $server['version'],
            'description' => $server['description'],
            'website' => $server['website'],
            'tags' => json_decode($server['tags'] ?? '[]', true),
            'status' => [
                'online' => (bool)$server['is_online'],
                'players' => (int)$server['players'],
                'max_players' => (int)$server['max_players'],
                'motd' => $server['motd'],
                'last_check' => $server['last_check']
            ],
            'ranking' => [
                'votes' => (int)$server['votes'],
                'rank_score' => (float)$server['rank_score'],
                'reviews_count' => (int)($server['reviews_count'] ?? 0),
                'average_rating' => (float)($server['avg_rating'] ?? 0)
            ],
            'chart_24h' => $history['data'] ?? [],
            'created_at' => $server['created_at']
        ];

        return $this->success($publicData);
    }
}
