<?php

return [
    'up' => function (PDO $db) {
        try {
            $db->exec("ALTER TABLE users ADD COLUMN design_preference ENUM('modern', 'pixel') DEFAULT 'modern'");
        } catch (\PDOException $e) {
            // Ignore if exists
        }
    },

    'down' => function (PDO $db) {
        try {
            $db->exec("ALTER TABLE users DROP COLUMN design_preference");
        } catch (\PDOException $e) {}
    }
];
