<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Env;
use App\Core\Database;
use App\Core\QueryBuilder;

Env::load(__DIR__ . '/.env');

$db = Database::getInstance();
$qb = new QueryBuilder($db);

// Test Select
$qb->table('users')->where('id', '>', 0)->limit(1);
$sql = $qb->toSql();
echo "SQL: $sql\n";
$user = $qb->first();
print_r($user);

// Test Insert (Rollback to avoid pollution?)
// Actually let's just test SQL generation for now to be safe against side effects.
// But we need to verify execution.

// Test Count
$count = $qb->fresh()->table('users')->count();
echo "Count: $count\n";

