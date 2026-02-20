<?php

return [
    'up' => "
        CREATE TABLE IF NOT EXISTS rate_limits (
            `key` VARCHAR(128) PRIMARY KEY,
            hits INT NOT NULL DEFAULT 0,
            expires_at TIMESTAMP NOT NULL,
            INDEX idx_expires (expires_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    'down' => "DROP TABLE IF EXISTS rate_limits",
];
