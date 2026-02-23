<?php

return [
    'up' => "
        ALTER TABLE users 
        ADD COLUMN bio TEXT NULL AFTER password_hash,
        ADD COLUMN social_discord VARCHAR(100) NULL AFTER bio,
        ADD COLUMN social_telegram VARCHAR(100) NULL AFTER social_discord;
    ",
    'down' => "
        ALTER TABLE users 
        DROP COLUMN bio,
        DROP COLUMN social_discord,
        DROP COLUMN social_telegram;
    ",
];
