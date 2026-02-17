<?php

return [
    'up' => "
        CREATE TABLE IF NOT EXISTS server_rankings (
            server_id INT PRIMARY KEY,
            rank_score DECIMAL(12,4) NOT NULL DEFAULT 0,
            vote_count INT DEFAULT 0,
            boost_points INT DEFAULT 0,
            avg_online DECIMAL(8,2) DEFAULT 0,
            uptime_percent DECIMAL(5,2) DEFAULT 0,
            calculated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (server_id) REFERENCES servers(id) ON DELETE CASCADE,
            INDEX idx_rank (rank_score DESC)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    'down' => "DROP TABLE IF EXISTS server_rankings",
];
