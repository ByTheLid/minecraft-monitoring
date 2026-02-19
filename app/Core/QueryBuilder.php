<?php

namespace App\Core;

use PDO;

class QueryBuilder
{
    protected PDO $pdo;
    protected string $table;
    protected array $bindings = [];
    protected array $selects = ['*'];
    protected array $wheres = [];
    protected array $orders = [];
    protected ?int $limit = null;
    protected ?int $offset = null;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function table(string $table): self
    {
        $this->table = $table;
        return $this;
    }

    public function select(array|string $columns = ['*']): self
    {
        $this->selects = is_array($columns) ? $columns : func_get_args();
        return $this;
    }

    public function where(string $column, string $operator, mixed $value = null): self
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        $this->wheres[] = [
            'type' => 'Basic',
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'boolean' => 'AND',
        ];

        $this->bindings[] = $value;

        return $this;
    }

    public function orWhere(string $column, string $operator, mixed $value = null): self
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        $this->wheres[] = [
            'type' => 'Basic',
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'boolean' => 'OR',
        ];

        $this->bindings[] = $value;

        return $this;
    }
    
    public function whereRaw(string $sql, array $bindings = []): self
    {
        $this->wheres[] = [
            'type' => 'Raw',
            'sql' => $sql,
            'boolean' => 'AND',
        ];
        
        $this->bindings = array_merge($this->bindings, $bindings);
        
        return $this;
    }

    public function orderBy(string $column, string $direction = 'asc'): self
    {
        $this->orders[] = [
            'column' => $column,
            'direction' => strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC',
        ];
        return $this;
    }

    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    public function offset(int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }

    public function get(): array
    {
        $sql = $this->toSql();
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($this->bindings);
        return $stmt->fetchAll();
    }

    public function first(): ?array
    {
        $this->limit(1);
        $result = $this->get();
        return $result[0] ?? null;
    }

    public function count(): int
    {
        $originalSelects = $this->selects;
        $this->selects = ['COUNT(*) as count'];
        
        $result = $this->first();
        
        $this->selects = $originalSelects; // Restore
        
        return (int) ($result['count'] ?? 0);
    }

    public function insert(array $values): int
    {
        $columns = implode(', ', array_keys($values));
        $placeholders = implode(', ', array_fill(0, count($values), '?'));

        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array_values($values));

        return (int) $this->pdo->lastInsertId();
    }

    public function update(array $values): bool
    {
        $sets = implode(', ', array_map(fn($col) => "{$col} = ?", array_keys($values)));
        
        // Build where clause manually for update
        $whereSql = $this->compileWheres();
        
        if (empty($whereSql)) {
             // Safety: prevent updating all rows without where clause, unless explicitly intended?
             // For now, let's allow it but it's risky. But typically update is called on an ID.
             // Or we force a where clause.
        }

        $sql = "UPDATE {$this->table} SET {$sets} {$whereSql}";
        
        // Bindings for update values first, then where clauses
        $bindings = array_merge(array_values($values), $this->bindings);
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($bindings);
    }
    
    public function delete(): bool
    {
        $whereSql = $this->compileWheres();
        $sql = "DELETE FROM {$this->table} {$whereSql}";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($this->bindings);
    }

    public function toSql(): string
    {
        $selects = implode(', ', $this->selects);
        $sql = "SELECT {$selects} FROM {$this->table}";

        $sql .= $this->compileWheres();
        $sql .= $this->compileOrders();
        $sql .= $this->compileLimit();

        return $sql;
    }

    protected function compileWheres(): string
    {
        if (empty($this->wheres)) {
            return '';
        }

        $sql = ' WHERE ';
        foreach ($this->wheres as $index => $where) {
            $prefix = $index === 0 ? '' : ' ' . $where['boolean'] . ' ';
            
            if ($where['type'] === 'Basic') {
                $sql .= "{$prefix}{$where['column']} {$where['operator']} ?";
            } elseif ($where['type'] === 'Raw') {
                $sql .= "{$prefix}{$where['sql']}";
            }
        }

        return $sql;
    }

    protected function compileOrders(): string
    {
        if (empty($this->orders)) {
            return '';
        }

        $orders = array_map(fn($order) => "{$order['column']} {$order['direction']}", $this->orders);
        return ' ORDER BY ' . implode(', ', $orders);
    }

    protected function compileLimit(): string
    {
        $sql = '';
        if ($this->limit !== null) {
            $sql .= " LIMIT {$this->limit}";
        }
        if ($this->offset !== null) {
            $sql .= " OFFSET {$this->offset}";
        }
        return $sql;
    }
    
    // Helper to clear state for reuse if needed, though usually new instance is better
    public function fresh(): self
    {
        $this->bindings = [];
        $this->selects = ['*'];
        $this->wheres = [];
        $this->orders = [];
        $this->limit = null;
        $this->offset = null;
        return $this;
    }
}
