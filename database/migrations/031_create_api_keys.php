<?php

return [
    'up' => function($db) {
        $sql = <<<'SQL'
CREATE TABLE IF NOT EXISTS api_keys (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    api_key VARCHAR(64) NOT NULL UNIQUE,
    `name` VARCHAR(100) NOT NULL DEFAULT 'Default',
    rate_limit INT UNSIGNED DEFAULT 120,
    is_active TINYINT(1) DEFAULT 1,
    last_used_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
SQL;
        $db->exec($sql);
    },
    'down' => "DROP TABLE IF EXISTS api_keys",
];
