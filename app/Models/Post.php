<?php

namespace App\Models;

use App\Core\Model;

class Post extends Model
{
    protected static string $table = 'posts';

    public static function getPublished(int $page = 1, int $perPage = 10): array
    {
        $offset = ($page - 1) * $perPage;

        $stmt = static::db()->prepare("SELECT COUNT(*) FROM posts WHERE is_published = 1");
        $stmt->execute();
        $total = (int) $stmt->fetchColumn();

        $stmt = static::db()->prepare(
            "SELECT p.*, u.username as author_name
             FROM posts p
             JOIN users u ON p.author_id = u.id
             WHERE p.is_published = 1
             ORDER BY p.published_at DESC
             LIMIT ? OFFSET ?"
        );
        $stmt->execute([$perPage, $offset]);

        return [
            'data' => $stmt->fetchAll(),
            'meta' => ['page' => $page, 'per_page' => $perPage, 'total' => $total, 'total_pages' => (int) ceil($total / $perPage)],
        ];
    }

    public static function getLatest(int $limit = 3): array
    {
        $stmt = static::db()->prepare(
            "SELECT p.*, u.username as author_name
             FROM posts p
             JOIN users u ON p.author_id = u.id
             WHERE p.is_published = 1
             ORDER BY p.published_at DESC
             LIMIT ?"
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    public static function findBySlug(string $slug): ?array
    {
        $stmt = static::db()->prepare(
            "SELECT p.*, u.username as author_name
             FROM posts p
             JOIN users u ON p.author_id = u.id
             WHERE p.slug = ?"
        );
        $stmt->execute([$slug]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public static function findPublished(int $id): ?array
    {
        $stmt = static::db()->prepare(
            "SELECT p.*, u.username as author_name
             FROM posts p
             JOIN users u ON p.author_id = u.id
             WHERE p.id = ? AND p.is_published = 1"
        );
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
}
