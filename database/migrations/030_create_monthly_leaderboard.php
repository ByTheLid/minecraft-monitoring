<?php

return [
    'up' => function($db) {
        $sql = <<<'SQL'
CREATE TABLE IF NOT EXISTS monthly_leaderboard (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    `year_month` CHAR(7) NOT NULL,
    vote_count INT UNSIGNED DEFAULT 0,
    points_earned INT UNSIGNED DEFAULT 0,
    position SMALLINT UNSIGNED DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_month (user_id, `year_month`),
    INDEX idx_ym_votes (`year_month`, vote_count DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
SQL;
        $db->exec($sql);
    },
    'down' => "DROP TABLE IF EXISTS monthly_leaderboard",
];
