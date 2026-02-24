<?php

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Models\Server;
use App\Models\ServerStat;

class PublicApiController extends Controller
{
    /**
     * GET /api/v1/servers — paginated list with cursor-based pagination
     */
    public function servers(Request $request): Response
    {
        $cursor = (int) $request->query('cursor', 0);
        $limit = min(max((int) $request->query('limit', 20), 1), 50);
        $sort = $request->query('sort', 'rank');
        $search = trim($request->query('search', ''));
        $status = $request->query('status', 'all');
        $version = trim($request->query('version', ''));

        $db = Database::getInstance();

        // Build WHERE
        $where = "s.is_active = 1 AND s.is_approved = 1";
        $params = [];

        if ($cursor > 0) {
            $where .= " AND s.id < ?";
            $params[] = $cursor;
        }

        if ($search) {
            $where .= " AND (s.name LIKE ? OR s.ip LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        if ($status === 'online') {
            $where .= " AND sc.is_online = 1";
        } elseif ($status === 'offline') {
            $where .= " AND (sc.is_online = 0 OR sc.is_online IS NULL)";
        }

        if ($version) {
            $where .= " AND sc.version LIKE ?";
            $params[] = "%{$version}%";
        }

        // Build ORDER BY
        $orderBy = match ($sort) {
            'players' => "sc.players_online DESC",
            'votes' => "sr.vote_count DESC",
            'newest' => "s.created_at DESC",
            default => "sr.rank_score DESC",
        };

        $sql = "SELECT s.id, s.name, s.ip, s.port, s.description, s.website, s.tags, s.created_at,
                       sc.is_online, sc.players_online, sc.players_max, sc.version, sc.motd, sc.last_check,
                       sr.rank_score, sr.vote_count,
                       (SELECT COUNT(*) FROM server_reviews r WHERE r.server_id = s.id AND r.is_approved = 1) as reviews_count,
                       (SELECT ROUND(AVG(r.rating),1) FROM server_reviews r WHERE r.server_id = s.id AND r.is_approved = 1) as avg_rating
                FROM servers s
                LEFT JOIN server_status_cache sc ON s.id = sc.server_id
                LEFT JOIN server_rankings sr ON s.id = sr.server_id
                WHERE {$where}
                ORDER BY {$orderBy}
                LIMIT ?";
        $params[] = $limit + 1; // Fetch one extra to determine has_more

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        $hasMore = count($rows) > $limit;
        if ($hasMore) {
            array_pop($rows); // Remove the extra row
        }

        $data = array_map(fn($s) => $this->formatServer($s), $rows);

        $nextCursor = $hasMore && !empty($rows) ? end($rows)['id'] : null;

        return $this->success($data, [
            'cursor' => $nextCursor,
            'has_more' => $hasMore,
            'limit' => $limit,
            'sort' => $sort,
        ]);
    }

    /**
     * GET /api/v1/server/{id} — single server detail
     */
    public function server(Request $request): Response
    {
        $id = (int) $request->param('id');
        $server = Server::getDetail($id);

        if (!$server || !$server['is_approved'] || !$server['is_active']) {
            return $this->error('NOT_FOUND', 'Server not found', [], 404);
        }

        $history = ServerStat::getHistory($id, '24h');

        $publicData = $this->formatServer($server);
        $publicData['chart_24h'] = $history['data'] ?? [];

        return $this->success($publicData);
    }

    /**
     * Format server row into a safe public structure
     */
    private function formatServer(array $s): array
    {
        return [
            'id' => (int) $s['id'],
            'name' => $s['name'],
            'ip' => $s['ip'],
            'port' => (int) $s['port'],
            'description' => $s['description'] ?? '',
            'website' => $s['website'] ?? '',
            'tags' => json_decode($s['tags'] ?? '[]', true),
            'status' => [
                'online' => (bool) ($s['is_online'] ?? false),
                'players' => (int) ($s['players_online'] ?? $s['players'] ?? 0),
                'max_players' => (int) ($s['players_max'] ?? $s['max_players'] ?? 0),
                'motd' => $s['motd'] ?? '',
                'version' => $s['version'] ?? '',
                'last_check' => $s['last_check'] ?? null,
            ],
            'ranking' => [
                'votes' => (int) ($s['vote_count'] ?? $s['votes'] ?? 0),
                'rank_score' => (float) ($s['rank_score'] ?? 0),
                'reviews_count' => (int) ($s['reviews_count'] ?? 0),
                'average_rating' => (float) ($s['avg_rating'] ?? 0),
            ],
            'created_at' => $s['created_at'],
        ];
    }
}
