<?php
require_once __DIR__ . '/vendor/autoload.php';
use App\Core\Database;
\App\Core\Env::load(__DIR__ . '/.env');

$db = Database::getInstance();
$sql = "SELECT s.id, s.name, sc.is_online, sc.server_id as cache_sid 
        FROM servers s 
        LEFT JOIN server_status_cache sc ON s.id = sc.server_id";
$rows = $db->query($sql)->fetchAll();
print_r($rows);
