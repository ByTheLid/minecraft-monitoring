<?php

return [
    'up' => "
        CREATE TABLE server_analytics_cache (
            server_id INT NOT NULL,
            date_hour TIMESTAMP NOT NULL,
            avg_players INT UNSIGNED DEFAULT 0,
            is_online_percent DECIMAL(5,2) DEFAULT 0,
            PRIMARY KEY (server_id, date_hour),
            FOREIGN KEY (server_id) REFERENCES servers(id) ON DELETE CASCADE
        ) ENGINE=InnoDB;
    ",
    'down' => "DROP TABLE IF EXISTS server_analytics_cache"
];
