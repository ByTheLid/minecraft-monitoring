<?php

return [
    'up' => "
        CREATE TABLE IF NOT EXISTS servers (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            name VARCHAR(100) NOT NULL,
            ip VARCHAR(255) NOT NULL,
            port SMALLINT UNSIGNED DEFAULT 25565,
            description TEXT,
            website VARCHAR(255),
            banner_url VARCHAR(255),
            tags JSON,
            is_active TINYINT(1) DEFAULT 1,
            is_approved TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_server (ip, port)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    'down' => "DROP TABLE IF EXISTS servers",
];
