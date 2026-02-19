<?php

return [
    'up' => function (PDO $db) {
        // Add new columns to boost_packages
        try {
            $db->exec("ALTER TABLE boost_packages ADD COLUMN color VARCHAR(7) DEFAULT '#ffcc00'");
            $db->exec("ALTER TABLE boost_packages ADD COLUMN features TEXT NULL"); // JSON
            $db->exec("ALTER TABLE boost_packages ADD COLUMN is_popular TINYINT(1) DEFAULT 0");
            $db->exec("ALTER TABLE boost_packages ADD COLUMN old_price DECIMAL(10,2) NULL");
        } catch (\PDOException $e) {
            // Ignore if exists
        }
    },

    'down' => function (PDO $db) {
        // We generally don't drop columns in down migrations to avoid data loss during dev rolling back
    }
];
