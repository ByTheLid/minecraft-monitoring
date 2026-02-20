<?php

/**
 * Clean up old data
 * Run daily at 03:00: 0 3 * * * php /path/to/cron/cleanup.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Env;
use App\Core\Database;
use App\Models\ServerStat;

Env::load(__DIR__ . '/../.env');

try {
    // Clean old detailed stats (keep 30 days)
    $deleted = ServerStat::cleanOld(30);
    echo "Deleted {$deleted} old stat records.\n";

    $db = Database::getInstance();

    // Clean expired sessions
    $db->exec("DELETE FROM sessions WHERE expires_at < NOW()");

    // Clean expired rate limits
    $db->exec("DELETE FROM rate_limits WHERE expires_at < NOW()");

    logger()->info("Cleanup completed: {$deleted} old stats deleted");

} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    logger()->error('Cleanup cron failed: ' . $e->getMessage());
    exit(1);
}
