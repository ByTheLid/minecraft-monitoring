<?php

return [
    'up' => "
        CREATE TABLE IF NOT EXISTS sessions (
            id CHAR(64) PRIMARY KEY,
            user_id INT NOT NULL,
            ip_address VARCHAR(45),
            user_agent VARCHAR(255),
            expires_at TIMESTAMP NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    'down' => "DROP TABLE IF EXISTS sessions",
];
