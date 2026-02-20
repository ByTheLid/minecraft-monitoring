<?php

namespace App\Models;

use App\Core\Model;

class Server extends Model
{
    protected static string $table = 'servers';

    public static function getApproved(int $page = 1, int $perPage = 20, string $sort = 'rank', string $search = '', string $status = 'all', string $version = '', string $tags = ''): array
    {
        $where = 's.is_active = 1 AND s.is_approved = 1';
        $params = [];
        $joins = "LEFT JOIN server_status_cache sc ON s.id = sc.server_id
                  LEFT JOIN server_rankings sr ON s.id = sr.server_id";

        if ($search) {
            $where .= " AND (s.name LIKE ? OR s.description LIKE ?)";
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

        if ($tags) {
            foreach (explode(',', $tags) as $tag) {
                $tag = trim($tag);
                if ($tag) {
                    $where .= " AND JSON_CONTAINS(s.tags, ?)";
                    $params[] = json_encode($tag);
                }
            }
        }

        $orderBy = match ($sort) {
            'players' => 'sc.players_online DESC',
            'votes' => 'sr.vote_count DESC',
            'newest' => 's.created_at DESC',
            default => 'sr.rank_score DESC',
        };

        // Count total
        $countSql = "SELECT COUNT(*) FROM servers s {$joins} WHERE {$where}";
        $stmt = static::db()->prepare($countSql);
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();

        // Fetch data
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT s.*, sc.is_online, sc.players_online, sc.players_max, sc.version, sc.ping_ms, sc.motd, sc.favicon_base64,
                       sr.rank_score, sr.vote_count, sr.boost_points,
                       sr.stars, sr.has_border, sr.has_bg_color, sr.highlight_color,
                       (SELECT GROUP_CONCAT(bp_pkg.name SEPARATOR ', ')
                        FROM boost_purchases bp
                        LEFT JOIN boost_packages bp_pkg ON bp.package_id = bp_pkg.id
                        WHERE bp.server_id = s.id AND bp.expires_at > NOW()) as active_boosts
                FROM servers s {$joins}
                WHERE {$where}
                ORDER BY {$orderBy}
                LIMIT ? OFFSET ?";

        $allParams = array_merge($params, [$perPage, $offset]);
        $stmt = static::db()->prepare($sql);
        $stmt->execute($allParams);

        return [
            'data' => $stmt->fetchAll(),
            'meta' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => (int) ceil($total / $perPage),
            ],
        ];
    }

    public static function getDetail(int $id): ?array
    {
        $sql = "SELECT s.*, sc.is_online, sc.players_online, sc.players_max, sc.version, sc.ping_ms, sc.motd, sc.favicon_base64,
                       sr.rank_score, sr.vote_count, sr.boost_points, sr.avg_online, sr.uptime_percent,
                       sr.stars, sr.has_border, sr.has_bg_color, sr.highlight_color,
                       u.username as owner_name,
                       (SELECT GROUP_CONCAT(bp_pkg.name SEPARATOR ', ')
                        FROM boost_purchases bp
                        LEFT JOIN boost_packages bp_pkg ON bp.package_id = bp_pkg.id
                        WHERE bp.server_id = s.id AND bp.expires_at > NOW()) as active_boosts
                FROM servers s
                LEFT JOIN server_status_cache sc ON s.id = sc.server_id
                LEFT JOIN server_rankings sr ON s.id = sr.server_id
                LEFT JOIN users u ON s.user_id = u.id
                WHERE s.id = ?";
        $stmt = static::db()->prepare($sql);
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public static function countByUser(int $userId): int
    {
        return static::builder()
            ->where('user_id', $userId)
            ->where('is_active', 1)
            ->count();
    }

    public static function getByUser(int $userId): array
    {
        $sql = "SELECT s.*, sc.is_online, sc.players_online, sc.players_max, sc.ping_ms,
                       sr.rank_score, sr.vote_count
                FROM servers s
                LEFT JOIN server_status_cache sc ON s.id = sc.server_id
                LEFT JOIN server_rankings sr ON s.id = sr.server_id
                WHERE s.user_id = ? AND s.is_active = 1
                ORDER BY s.created_at DESC";
        $stmt = static::db()->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function isDuplicate(string $ip, int $port, ?int $excludeId = null): bool
    {
        $query = static::builder()
            ->where('ip', $ip)
            ->where('port', $port)
            ->where('is_active', 1);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->count() > 0;
    }

    public static function getActiveApproved(): array
    {
        $sql = "SELECT id, ip, port FROM servers WHERE is_active = 1 AND is_approved = 1";
        return static::db()->query($sql)->fetchAll();
    }

    public static function getAllForAdmin(int $page = 1, int $perPage = 20, string $filter = 'all'): array
    {
        $where = '1=1';
        if ($filter === 'pending') {
            $where = 's.is_approved = 0 AND s.is_active = 1';
        } elseif ($filter === 'approved') {
            $where = 's.is_approved = 1 AND s.is_active = 1';
        } elseif ($filter === 'blocked') {
            $where = 's.is_active = 0';
        }

        $offset = ($page - 1) * $perPage;
        $countSql = "SELECT COUNT(*) FROM servers s WHERE {$where}";
        $stmt = static::db()->prepare($countSql);
        $stmt->execute();
        $total = (int) $stmt->fetchColumn();

        $sql = "SELECT s.*, u.username as owner_name, sc.is_online, sc.players_online,
                       sr.vote_count,
                       (SELECT GROUP_CONCAT(bp_pkg.name SEPARATOR ', ')
                        FROM boost_purchases bp
                        LEFT JOIN boost_packages bp_pkg ON bp.package_id = bp_pkg.id
                        WHERE bp.server_id = s.id AND bp.expires_at > NOW()) as active_boosts
                FROM servers s
                LEFT JOIN users u ON s.user_id = u.id
                LEFT JOIN server_status_cache sc ON s.id = sc.server_id
                LEFT JOIN server_rankings sr ON s.id = sr.server_id
                WHERE {$where}
                ORDER BY s.created_at DESC
                LIMIT ? OFFSET ?";
        $stmt = static::db()->prepare($sql);
        $stmt->execute([$perPage, $offset]);

        return [
            'data' => $stmt->fetchAll(),
            'meta' => ['page' => $page, 'per_page' => $perPage, 'total' => $total, 'total_pages' => (int) ceil($total / $perPage)],
        ];
    }
}
