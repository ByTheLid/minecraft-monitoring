<?php

namespace App\Models;

use App\Core\Database;

class ApiKey
{
    public static function findByKey(string $key): ?array
    {
        $stmt = Database::getInstance()->prepare("SELECT * FROM api_keys WHERE api_key = ?");
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function getByUser(int $userId): array
    {
        $stmt = Database::getInstance()->prepare(
            "SELECT id, `name`, CONCAT(LEFT(api_key, 8), '...') as api_key_masked, rate_limit, is_active, last_used_at, created_at 
             FROM api_keys WHERE user_id = ? ORDER BY created_at DESC"
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function generate(int $userId, string $name = 'Default'): string
    {
        $key = bin2hex(random_bytes(32));
        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO api_keys (user_id, api_key, `name`) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $key, $name]);
        return $key;
    }

    public static function touch(int $id): void
    {
        Database::getInstance()->prepare(
            "UPDATE api_keys SET last_used_at = NOW() WHERE id = ?"
        )->execute([$id]);
    }

    public static function deactivate(int $id, int $userId): bool
    {
        return Database::getInstance()->prepare(
            "UPDATE api_keys SET is_active = 0 WHERE id = ? AND user_id = ?"
        )->execute([$id, $userId]);
    }

    public static function countByUser(int $userId): int
    {
        $stmt = Database::getInstance()->prepare("SELECT COUNT(*) FROM api_keys WHERE user_id = ?");
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }
}
