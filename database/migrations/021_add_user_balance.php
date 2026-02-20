<?php

return [
    'up' => function (PDO $db) {
        try {
            $db->exec("ALTER TABLE users ADD COLUMN balance DECIMAL(10,2) DEFAULT 0.00");
        } catch (\PDOException $e) {
            // Ignore if exists
        }
    },

    'down' => function (PDO $db) {
        $db->exec("ALTER TABLE users DROP COLUMN balance");
    }
];
