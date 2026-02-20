<?php

return [
    'up' => function (PDO $db) {
        $db->exec("
            CREATE TABLE IF NOT EXISTS boost_packages (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(64) NOT NULL,
                points INT NOT NULL,
                price DECIMAL(10,2) NOT NULL,
                duration_days INT NOT NULL,
                is_active TINYINT(1) DEFAULT 1
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $db->exec("
            CREATE TABLE IF NOT EXISTS boost_purchases (
                id BIGINT PRIMARY KEY AUTO_INCREMENT,
                user_id INT NOT NULL,
                server_id INT NOT NULL,
                package_id INT NOT NULL,
                points INT NOT NULL,
                activated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                expires_at TIMESTAMP NOT NULL,
                FOREIGN KEY (user_id) REFERENCES users(id),
                FOREIGN KEY (server_id) REFERENCES servers(id),
                FOREIGN KEY (package_id) REFERENCES boost_packages(id),
                INDEX idx_active_boosts (server_id, expires_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    },
    'down' => function (PDO $db) {
        $db->exec("DROP TABLE IF EXISTS boost_purchases");
        $db->exec("DROP TABLE IF EXISTS boost_packages");
    },
];
