<?php

return [
    'up' => "
        CREATE TABLE IF NOT EXISTS server_stats (
            id BIGINT PRIMARY KEY AUTO_INCREMENT,
            server_id INT NOT NULL,
            is_online TINYINT(1) NOT NULL,
            players_online SMALLINT UNSIGNED DEFAULT 0,
            players_max SMALLINT UNSIGNED DEFAULT 0,
            version VARCHAR(64),
            ping_ms SMALLINT UNSIGNED,
            motd VARCHAR(512),
            checked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (server_id) REFERENCES servers(id) ON DELETE CASCADE,
            INDEX idx_server_checked (server_id, checked_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    'down' => "DROP TABLE IF EXISTS server_stats",
];
