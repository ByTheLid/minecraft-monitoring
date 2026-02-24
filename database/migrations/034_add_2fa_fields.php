<?php

return [
    'up' => function($db) {
        $db->exec("ALTER TABLE users 
            ADD COLUMN two_factor_secret VARCHAR(255) DEFAULT NULL AFTER password_hash,
            ADD COLUMN two_factor_enabled TINYINT(1) DEFAULT 0 AFTER two_factor_secret,
            ADD COLUMN requires_2fa TINYINT(1) DEFAULT 0 AFTER two_factor_enabled,
            ADD COLUMN two_factor_grace_until TIMESTAMP NULL DEFAULT NULL AFTER requires_2fa");
    },
    'down' => function($db) {
        $db->exec("ALTER TABLE users 
            DROP COLUMN two_factor_secret,
            DROP COLUMN two_factor_enabled,
            DROP COLUMN requires_2fa,
            DROP COLUMN two_factor_grace_until");
    },
];
