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

    public static function builder(): QueryBuilder
    {
        return (new QueryBuilder(static::db()))->table(static::$table);
    }

    public static function find(int $id): ?array
    {
        return static::builder()->where(static::$primaryKey, $id)->first();
    }

    public static function findBy(string $column, mixed $value): ?array
    {
        return static::builder()->where($column, $value)->first();
    }

    public static function all(string $orderBy = 'id', string $direction = 'ASC', int $limit = 100, int $offset = 0): array
    {
        return static::builder()
            ->orderBy($orderBy, $direction)
            ->limit($limit)
            ->offset($offset)
            ->get();
    }

    public static function create(array $data): int
    {
        return static::builder()->insert($data);
    }

    public static function update(int $id, array $data): bool
    {
        return static::builder()->where(static::$primaryKey, $id)->update($data);
    }

    public static function delete(int $id): bool
    {
        return static::builder()->where(static::$primaryKey, $id)->delete();
    }

    public static function count(string $where = '1=1', array $params = []): int
    {
        return static::builder()->whereRaw($where, $params)->count();
    }

    public static function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = static::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function paginate(int $page = 1, int $perPage = 20, string $where = '1=1', array $params = [], string $orderBy = 'id', string $direction = 'DESC'): array
    {
        $offset = ($page - 1) * $perPage;
        
        $total = static::count($where, $params);
        
        $data = static::builder()
            ->whereRaw($where, $params)
            ->orderBy($orderBy, $direction)
            ->limit($perPage)
            ->offset($offset)
            ->get();

        return [
            'data' => $data,
            'meta' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => (int) ceil($total / $perPage),
            ],
        ];
    }

    public static function __callStatic($name, $arguments)
    {
        return static::builder()->$name(...$arguments);
    }
}
