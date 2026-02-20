<?php

return [
    'up' => function (PDO $db) {
        try {
            $db->exec("ALTER TABLE server_rankings ADD COLUMN stars INT DEFAULT 0");
            $db->exec("ALTER TABLE server_rankings ADD COLUMN has_border TINYINT(1) DEFAULT 0");
            $db->exec("ALTER TABLE server_rankings ADD COLUMN has_bg_color TINYINT(1) DEFAULT 0");
            $db->exec("ALTER TABLE server_rankings ADD COLUMN highlight_color VARCHAR(7) NULL");
        } catch (\PDOException $e) {
            // Ignore if exists
        }
    },

    'down' => function (PDO $db) {
        $db->exec("ALTER TABLE server_rankings DROP COLUMN stars, DROP COLUMN has_border, DROP COLUMN has_bg_color, DROP COLUMN highlight_color");
    }
];
