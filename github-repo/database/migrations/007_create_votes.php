<?php

return [
    'up' => "
        CREATE TABLE IF NOT EXISTS votes (
            id BIGINT PRIMARY KEY AUTO_INCREMENT,
            server_id INT NOT NULL,
            user_id INT,
            ip_address VARCHAR(45) NOT NULL,
            voted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (server_id) REFERENCES servers(id) ON DELETE CASCADE,
            INDEX idx_vote_lookup (server_id, ip_address, voted_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    'down' => "DROP TABLE IF EXISTS votes",
];
