<?php

// Deactivate expired boosts (checks for monitoring)
// Cron: every 15 minutes

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Env;
use App\Core\Database;

Env::load(__DIR__ . '/../.env');

try {
    $db = Database::getInstance();

    $stmt = $db->query("SELECT COUNT(*) FROM boost_purchases WHERE expires_at < NOW()");
    $expired = (int) $stmt->fetchColumn();

    $stmt = $db->query("SELECT COUNT(*) FROM boost_purchases WHERE expires_at > NOW()");
    $active = (int) $stmt->fetchColumn();

    echo "Active boosts: {$active}, Expired: {$expired}\n";
    logger()->info("Boost check: {$active} active, {$expired} expired");

} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    logger()->error('Expire boosts cron failed: ' . $e->getMessage());
    exit(1);
}
