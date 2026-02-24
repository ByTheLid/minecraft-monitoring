<?php

namespace App\Services;

use App\Core\Database;
use App\Models\SeoPage;

class SeoService
{
    const MIN_SERVERS = 15;
    const MIN_TOTAL_DESCRIPTION_LENGTH = 500;

    /**
     * Recalculate is_indexed for all SEO pages based on threshold rules
     */
    public static function recalculate(): int
    {
        $pages = SeoPage::getAll();
        $updated = 0;
        $db = Database::getInstance();

        foreach ($pages as $page) {
            $count = self::countServers($page['category'], $page['value']);
            $descLen = self::sumDescriptionLength($page['category'], $page['value']);

            $shouldIndex = $count >= self::MIN_SERVERS && $descLen >= self::MIN_TOTAL_DESCRIPTION_LENGTH;

            SeoPage::update($page['id'], [
                'server_count' => $count,
                'is_indexed' => $shouldIndex ? 1 : 0,
            ]);
            $updated++;
        }

        return $updated;
    }

    /**
     * Count servers matching a category/value filter
     */
    public static function countServers(string $category, string $value): int
    {
        $db = Database::getInstance();
        $where = "s.is_active = 1 AND s.is_approved = 1";
        $params = [];

        match ($category) {
            'version' => (function() use (&$where, &$params, $value) {
                $where .= " AND sc.version LIKE ?";
                $params[] = "%{$value}%";
            })(),
            'tag' => (function() use (&$where, &$params, $value) {
                $where .= " AND s.tags LIKE ?";
                $params[] = "%\"{$value}\"%";
            })(),
            default => null,
        };

        $stmt = $db->prepare("
            SELECT COUNT(*) FROM servers s
            LEFT JOIN server_status_cache sc ON s.id = sc.server_id
            WHERE {$where}
        ");
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Sum description lengths for servers matching filter
     */
    public static function sumDescriptionLength(string $category, string $value): int
    {
        $db = Database::getInstance();
        $where = "s.is_active = 1 AND s.is_approved = 1";
        $params = [];

        match ($category) {
            'version' => (function() use (&$where, &$params, $value) {
                $where .= " AND sc.version LIKE ?";
                $params[] = "%{$value}%";
            })(),
            'tag' => (function() use (&$where, &$params, $value) {
                $where .= " AND s.tags LIKE ?";
                $params[] = "%\"{$value}\"%";
            })(),
            default => null,
        };

        $stmt = $db->prepare("
            SELECT COALESCE(SUM(LENGTH(s.description)), 0) FROM servers s
            LEFT JOIN server_status_cache sc ON s.id = sc.server_id
            WHERE {$where}
        ");
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Render SEO text template with dynamic variables
     */
    public static function renderTemplate(string $template, array $vars): string
    {
        return str_replace(
            array_map(fn($k) => "{{$k}}", array_keys($vars)),
            array_values($vars),
            $template
        );
    }

    /**
     * Get servers for a specific category/value filter with pagination
     */
    public static function getFilteredServers(string $category, string $value, int $page = 1, int $perPage = 20): array
    {
        $db = Database::getInstance();
        $where = "s.is_active = 1 AND s.is_approved = 1";
        $params = [];

        match ($category) {
            'version' => (function() use (&$where, &$params, $value) {
                $where .= " AND sc.version LIKE ?";
                $params[] = "%{$value}%";
            })(),
            'tag' => (function() use (&$where, &$params, $value) {
                $where .= " AND s.tags LIKE ?";
                $params[] = "%\"{$value}\"%";
            })(),
            default => null,
        };

        $offset = ($page - 1) * $perPage;

        // Count
        $countStmt = $db->prepare("SELECT COUNT(*) FROM servers s LEFT JOIN server_status_cache sc ON s.id = sc.server_id WHERE {$where}");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        // Fetch
        $params[] = $perPage;
        $params[] = $offset;
        $stmt = $db->prepare("
            SELECT s.*, sc.is_online, sc.players_online, sc.players_max, sc.version,
                   sr.rank_score, sr.vote_count
            FROM servers s
            LEFT JOIN server_status_cache sc ON s.id = sc.server_id
            LEFT JOIN server_rankings sr ON s.id = sr.server_id
            WHERE {$where}
            ORDER BY sr.rank_score DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute($params);

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
}
