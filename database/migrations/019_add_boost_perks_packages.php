<?php

return [
    'up' => function (PDO $db) {
        try {
            $db->exec("ALTER TABLE boost_packages ADD COLUMN stars INT DEFAULT 0");
            $db->exec("ALTER TABLE boost_packages ADD COLUMN has_border TINYINT(1) DEFAULT 0");
            $db->exec("ALTER TABLE boost_packages ADD COLUMN has_bg_color TINYINT(1) DEFAULT 0");
        } catch (\PDOException $e) {
            // Ignore if exists
        }
    },

    'down' => function (PDO $db) {
        $db->exec("ALTER TABLE boost_packages DROP COLUMN stars, DROP COLUMN has_border, DROP COLUMN has_bg_color");
    }
];
