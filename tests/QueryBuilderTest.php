<?php

namespace Tests;

use App\Core\QueryBuilder;
use PDO;

class QueryBuilderTest
{
    private TestRunner $runner;

    public function __construct(TestRunner $runner)
    {
        $this->runner = $runner;
    }

    // Mock completely dummy PDO just to instantiate QueryBuilder
    private function getMockPdo(): PDO
    {
        // Using an anonymous class or just null if PHP allows (PDO can be tricky to mock without PHPUnit dummy)
        // Since we only test toSql(), we can pass a really basic mock if permitted,
        // or just initialize a memory sqlite db.
        $pdo = new PDO('sqlite::memory:');
        return $pdo;
    }

    public function testSelectSqlGeneration()
    {
        $qb = new QueryBuilder($this->getMockPdo());
        
        $sql = $qb->table('users')->select(['id', 'name'])->toSql();
        $this->runner->assertEquals("SELECT id, name FROM users", $sql, "Basic select SQL");
        
        $sqlAll = $qb->fresh()->table('users')->toSql();
        $this->runner->assertEquals("SELECT * FROM users", $sqlAll, "Default select * SQL");
    }

    public function testWhereSqlGeneration()
    {
        $qb = new QueryBuilder($this->getMockPdo());
        
        $sql = $qb->table('servers')
            ->where('status', '1')
            ->where('votes', '>', 100)
            ->toSql();
            
        $this->runner->assertEquals("SELECT * FROM servers WHERE status = ? AND votes > ?", $sql, "Where clause SQL generation");
    }

    public function testOrderAndLimitSqlGeneration()
    {
        $qb = new QueryBuilder($this->getMockPdo());
        
        $sql = $qb->table('users')
            ->orderBy('created_at', 'DESC')
            ->limit(10)
            ->offset(5)
            ->toSql();
            
        $this->runner->assertEquals("SELECT * FROM users ORDER BY created_at DESC LIMIT 10 OFFSET 5", $sql, "Order, Limit and Offset SQL");
    }
}
