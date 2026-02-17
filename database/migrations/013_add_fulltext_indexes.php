<?php

return [
    'up' => function (PDO $db) {
        $db->exec("CREATE FULLTEXT INDEX idx_servers_search ON servers(name, description)");
        $db->exec("CREATE INDEX idx_servers_active ON servers(is_active, is_approved)");
        $db->exec("CREATE INDEX idx_votes_server_time ON votes(server_id, voted_at)");
        $db->exec("CREATE INDEX idx_boosts_active ON boost_purchases(server_id, expires_at)");
    },
    'down' => function (PDO $db) {
        $db->exec("DROP INDEX idx_servers_search ON servers");
        $db->exec("DROP INDEX idx_servers_active ON servers");
        $db->exec("DROP INDEX idx_votes_server_time ON votes");
        $db->exec("DROP INDEX idx_boosts_active ON servers");
    },
];
