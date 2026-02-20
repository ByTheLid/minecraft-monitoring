<?php

return [
    'up' => function (PDO $db) {
        $db->exec("
            CREATE TABLE IF NOT EXISTS settings (
                `key` VARCHAR(64) PRIMARY KEY,
                value TEXT NOT NULL,
                description VARCHAR(255)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Insert default settings
        $stmt = $db->prepare("INSERT IGNORE INTO settings (`key`, value, description) VALUES (?, ?, ?)");
        $stmt->execute(['rank_kv', '1.0', 'Votes coefficient']);
        $stmt->execute(['rank_kb', '0.5', 'Boost coefficient']);
        $stmt->execute(['rank_ko', '0.3', 'Online coefficient']);
        $stmt->execute(['rank_ku', '0.2', 'Uptime coefficient']);
        $stmt->execute(['max_servers_per_user', '5', 'Maximum servers per user']);
    },
    'down' => "DROP TABLE IF EXISTS settings",
];
