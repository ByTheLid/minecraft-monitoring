<?php
require_once __DIR__ . '/vendor/autoload.php';
use App\Models\Server;
\App\Core\Env::load(__DIR__ . '/.env');

$servers = Server::getApproved(1, 10, 'rank');
if (!empty($servers['data'])) {
    $first = $servers['data'][0];
    print_r([
        'id' => $first['id'],
        'name' => $first['name'],
        'is_online' => $first['is_online'] ?? 'MISSING',
        'ping_ms' => $first['ping_ms'] ?? 'MISSING'
    ]);
} else {
    echo "No servers found.\n";
}
