<?php

return [
    'up' => function($db) {
        $db->exec("ALTER TABLE posts 
            ADD COLUMN meta_title VARCHAR(160) DEFAULT NULL AFTER slug,
            ADD COLUMN meta_description VARCHAR(320) DEFAULT NULL AFTER meta_title,
            ADD COLUMN canonical_url VARCHAR(255) DEFAULT NULL AFTER meta_description");
    },
    'down' => function($db) {
        $db->exec("ALTER TABLE posts DROP COLUMN meta_title, DROP COLUMN meta_description, DROP COLUMN canonical_url");
    },
];
