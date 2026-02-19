<?php

return [
    'up' => function (PDO $db) {
        // Add username to votes table if not exists
        try {
             $db->exec("ALTER TABLE votes ADD COLUMN username VARCHAR(16) NULL AFTER user_id");
             $db->exec("CREATE INDEX idx_votes_username ON votes(username)");
        } catch (\PDOException $e) {
            // Ignore if column exists
        }

        // Create votifier_keys table
        $sql = "CREATE TABLE IF NOT EXISTS votifier_keys (
            id INT AUTO_INCREMENT PRIMARY KEY,
            server_id INT NOT NULL,
            public_key TEXT NOT NULL,
            address VARCHAR(255) NULL,
            port INT DEFAULT 8192,
            token VARCHAR(255) NULL,
            version ENUM('v1', 'v2') DEFAULT 'v1',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (server_id) REFERENCES servers(id) ON DELETE CASCADE,
            UNIQUE KEY unique_server_votifier (server_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $db->exec($sql);
    },

    'down' => function (PDO $db) {
        $db->exec("DROP TABLE IF EXISTS votifier_keys");
        try {
            $db->exec("ALTER TABLE votes DROP COLUMN username");
        } catch (\PDOException $e) {}
    }
];
