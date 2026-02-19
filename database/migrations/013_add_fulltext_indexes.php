<?php

return [
    'up' => function (PDO $db) {
        $indexes = [
            "CREATE FULLTEXT INDEX idx_servers_search ON servers(name, description)",
            "CREATE INDEX idx_servers_active ON servers(is_active, is_approved)",
            "CREATE INDEX idx_votes_server_time ON votes(server_id, voted_at)",
            "CREATE INDEX idx_boosts_active ON boost_purchases(expires_at)"
        ];

        foreach ($indexes as $sql) {
            try {
                $db->exec($sql);
            } catch (PDOException $e) {
                // Ignore duplicate key error (1061) or index exists (1358)
                // SQLite might differ, but this is likely MySQL
            }
        }
    },
    'down' => function (PDO $db) {
        $db->exec("DROP INDEX idx_servers_search ON servers");
        $db->exec("DROP INDEX idx_servers_active ON servers");
        $db->exec("DROP INDEX idx_votes_server_time ON votes");
        $db->exec("DROP INDEX idx_boosts_active ON servers");
    },
];
