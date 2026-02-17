<?php

return [
    'up' => "
        CREATE TABLE IF NOT EXISTS server_status_cache (
            server_id INT PRIMARY KEY,
            is_online TINYINT(1),
            players_online SMALLINT UNSIGNED,
            players_max SMALLINT UNSIGNED,
            version VARCHAR(64),
            ping_ms SMALLINT UNSIGNED,
            motd VARCHAR(512),
            favicon_base64 TEXT,
            last_checked_at TIMESTAMP NULL,
            FOREIGN KEY (server_id) REFERENCES servers(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    'down' => "DROP TABLE IF EXISTS server_status_cache",
];
