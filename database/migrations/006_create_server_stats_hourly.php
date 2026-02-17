<?php

return [
    'up' => "
        CREATE TABLE IF NOT EXISTS server_stats_hourly (
            server_id INT,
            hour TIMESTAMP,
            avg_players DECIMAL(8,2),
            max_players SMALLINT,
            uptime_percent DECIMAL(5,2),
            avg_ping DECIMAL(8,2),
            PRIMARY KEY (server_id, hour),
            FOREIGN KEY (server_id) REFERENCES servers(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    'down' => "DROP TABLE IF EXISTS server_stats_hourly",
];
