<?php

return [
    'up' => function($db) {
        $db->exec("CREATE TABLE IF NOT EXISTS two_factor_backup_codes (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            code VARCHAR(20) NOT NULL,
            is_used TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    },
    'down' => "DROP TABLE IF EXISTS two_factor_backup_codes",
];
