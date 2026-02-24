<?php

return [
    'up' => "
        CREATE TABLE IF NOT EXISTS achievements (
            id INT AUTO_INCREMENT PRIMARY KEY,
            achievement_key VARCHAR(50) NOT NULL UNIQUE,
            name VARCHAR(100) NOT NULL,
            description TEXT NOT NULL,
            icon VARCHAR(50) NOT NULL DEFAULT 'fa-star',
            color VARCHAR(30) NOT NULL DEFAULT 'blue',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    'down' => "DROP TABLE IF EXISTS achievements",
];
