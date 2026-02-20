<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Env;
use App\Core\Migration;

Env::load(__DIR__ . '/../.env');

$action = $argv[1] ?? 'up';
$migration = new Migration();

try {
    if ($action === 'up') {
        $migrated = $migration->migrate();
        if (empty($migrated)) {
            echo "Nothing to migrate.\n";
        } else {
            foreach ($migrated as $name) {
                echo "Migrated: {$name}\n";
            }
        }
    } elseif ($action === 'down') {
        $steps = (int) ($argv[2] ?? 1);
        $rolled = $migration->rollback($steps);
        if (empty($rolled)) {
            echo "Nothing to rollback.\n";
        } else {
            foreach ($rolled as $name) {
                echo "Rolled back: {$name}\n";
            }
        }
    } else {
        echo "Usage: php migrate.php [up|down] [steps]\n";
    }
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
