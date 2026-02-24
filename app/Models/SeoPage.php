<?php

namespace App\Models;

use App\Core\Database;

class SeoPage
{
    public static function findByPath(string $urlPath): ?array
    {
        $stmt = Database::getInstance()->prepare("SELECT * FROM seo_pages WHERE url_path = ?");
        $stmt->execute([$urlPath]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function getAll(): array
    {
        return Database::getInstance()
            ->query("SELECT * FROM seo_pages ORDER BY category, value")
            ->fetchAll();
    }

    public static function getIndexed(): array
    {
        return Database::getInstance()
            ->query("SELECT * FROM seo_pages WHERE is_indexed = 1 ORDER BY category, value")
            ->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::getInstance()->prepare("SELECT * FROM seo_pages WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(array $data): int
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO seo_pages (category, value, url_path, h1, meta_title, meta_description, seo_text_template) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['category'], $data['value'], $data['url_path'],
            $data['h1'], $data['meta_title'], $data['meta_description'],
            $data['seo_text_template'] ?? '',
        ]);
        return (int) $db->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $sets = [];
        $params = [];
        foreach ($data as $key => $value) {
            $sets[] = "`{$key}` = ?";
            $params[] = $value;
        }
        $params[] = $id;
        return Database::getInstance()->prepare(
            "UPDATE seo_pages SET " . implode(', ', $sets) . " WHERE id = ?"
        )->execute($params);
    }

    public static function delete(int $id): bool
    {
        return Database::getInstance()->prepare("DELETE FROM seo_pages WHERE id = ?")->execute([$id]);
    }
}
