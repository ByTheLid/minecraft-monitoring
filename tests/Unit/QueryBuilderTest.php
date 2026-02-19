<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Core\QueryBuilder;
use PDO;
use PDOStatement;

class QueryBuilderTest extends TestCase
{
    private $pdo;
    private $qb;

    protected function setUp(): void
    {
        $this->pdo = $this->createMock(PDO::class);
        $this->qb = new QueryBuilder($this->pdo);
    }

    public function test_select_all_from_table()
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())->method('execute')->with([]);
        $stmt->expects($this->once())->method('fetchAll')->willReturn([]);

        $this->pdo->expects($this->once())
            ->method('prepare')
            ->with('SELECT * FROM users')
            ->willReturn($stmt);

        $this->qb->table('users')->get();
    }

    public function test_where_clause()
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())->method('execute')->with([1]);
        $stmt->expects($this->once())->method('fetchAll')->willReturn([]);

        $this->pdo->expects($this->once())
            ->method('prepare')
            ->with('SELECT * FROM users WHERE id = ?')
            ->willReturn($stmt);

        $this->qb->table('users')->where('id', 1)->get();
    }

    public function test_multiple_where_clauses()
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())->method('execute')->with([1, 'active']);
        
        $this->pdo->expects($this->once())
            ->method('prepare')
            // Note the space after WHERE that we fixed
            ->with('SELECT * FROM users WHERE id = ? AND status = ?')
            ->willReturn($stmt);

        $this->qb->table('users')->where('id', 1)->where('status', 'active')->get();
    }

    public function test_limit_and_offset()
    {
        $stmt = $this->createMock(PDOStatement::class);
        
        $this->pdo->expects($this->once())
            ->method('prepare')
            ->with('SELECT * FROM users LIMIT 10 OFFSET 5')
            ->willReturn($stmt);

        $this->qb->table('users')->limit(10)->offset(5)->get();
    }
    
    public function test_insert()
    {
         $stmt = $this->createMock(PDOStatement::class);
         $stmt->expects($this->once())->method('execute')->with(['admin', 'admin@example.com']);
         
         $this->pdo->expects($this->once())
             ->method('prepare')
             ->with('INSERT INTO users (username, email) VALUES (?, ?)')
             ->willReturn($stmt);
             
         $this->pdo->expects($this->once())->method('lastInsertId')->willReturn('1');
         
         $id = $this->qb->table('users')->insert(['username' => 'admin', 'email' => 'admin@example.com']);
         $this->assertEquals(1, $id);
    }
}
