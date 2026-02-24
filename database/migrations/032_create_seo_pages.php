<?php

return [
    'up' => function($db) {
        $db->exec("CREATE TABLE IF NOT EXISTS seo_pages (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            category VARCHAR(50) NOT NULL,
            value VARCHAR(100) NOT NULL,
            url_path VARCHAR(255) NOT NULL UNIQUE,
            h1 VARCHAR(255) NOT NULL,
            meta_title VARCHAR(160) NOT NULL,
            meta_description VARCHAR(320) NOT NULL,
            seo_text_template TEXT,
            is_indexed TINYINT(1) DEFAULT 0,
            server_count INT UNSIGNED DEFAULT 0,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_cat_val (category, value),
            INDEX idx_indexed (is_indexed)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    },
    'down' => "DROP TABLE IF EXISTS seo_pages",
];
