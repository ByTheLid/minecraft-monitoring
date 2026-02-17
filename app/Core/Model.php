<?php

namespace App\Core;

use PDO;
use PDOStatement;

abstract class Model
{
    protected static string $table = '';
    protected static string $primaryKey = 'id';

    protected static function db(): PDO
    {
        return Database::getInstance();
    }

    public static function find(int $id): ?array
    {
        $table = static::$table;
        $pk = static::$primaryKey;
        $stmt = static::db()->prepare("SELECT * FROM `{$table}` WHERE `{$pk}` = ? LIMIT 1");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public static function findBy(string $column, mixed $value): ?array
    {
        $table = static::$table;
        $stmt = static::db()->prepare("SELECT * FROM `{$table}` WHERE `{$column}` = ? LIMIT 1");
        $stmt->execute([$value]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public static function all(string $orderBy = 'id', string $direction = 'ASC', int $limit = 100, int $offset = 0): array
    {
        $table = static::$table;
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $stmt = static::db()->prepare("SELECT * FROM `{$table}` ORDER BY `{$orderBy}` {$direction} LIMIT ? OFFSET ?");
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll();
    }

    public static function create(array $data): int
    {
        $table = static::$table;
        $columns = implode('`, `', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $stmt = static::db()->prepare("INSERT INTO `{$table}` (`{$columns}`) VALUES ({$placeholders})");
        $stmt->execute(array_values($data));

        return (int) static::db()->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $table = static::$table;
        $pk = static::$primaryKey;
        $sets = implode(', ', array_map(fn($col) => "`{$col}` = ?", array_keys($data)));

        $stmt = static::db()->prepare("UPDATE `{$table}` SET {$sets} WHERE `{$pk}` = ?");
        $values = array_values($data);
        $values[] = $id;
        return $stmt->execute($values);
    }

    public static function delete(int $id): bool
    {
        $table = static::$table;
        $pk = static::$primaryKey;
        $stmt = static::db()->prepare("DELETE FROM `{$table}` WHERE `{$pk}` = ?");
        return $stmt->execute([$id]);
    }

    public static function count(string $where = '1=1', array $params = []): int
    {
        $table = static::$table;
        $stmt = static::db()->prepare("SELECT COUNT(*) FROM `{$table}` WHERE {$where}");
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public static function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = static::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function paginate(int $page = 1, int $perPage = 20, string $where = '1=1', array $params = [], string $orderBy = 'id', string $direction = 'DESC'): array
    {
        $table = static::$table;
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $offset = ($page - 1) * $perPage;

        $total = static::count($where, $params);

        $stmt = static::db()->prepare(
            "SELECT * FROM `{$table}` WHERE {$where} ORDER BY `{$orderBy}` {$direction} LIMIT ? OFFSET ?"
        );
        $allParams = array_merge($params, [$perPage, $offset]);
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
}
