<?php

return [
    'up' => "
        ALTER TABLE servers 
        ADD COLUMN is_verified TINYINT(1) DEFAULT 0 AFTER is_approved,
        ADD COLUMN verify_token VARCHAR(32) DEFAULT NULL AFTER is_verified
    ",
    'down' => "
        ALTER TABLE servers 
        DROP COLUMN is_verified,
        DROP COLUMN verify_token
    "
];
