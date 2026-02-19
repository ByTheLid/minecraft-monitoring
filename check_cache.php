<?php
require_once __DIR__ . '/vendor/autoload.php';
use App\Core\Database;
\App\Core\Env::load(__DIR__ . '/.env');

$db = Database::getInstance();
$rows = $db->query("SELECT * FROM server_status_cache")->fetchAll();
print_r($rows);
