<?php

return [
    'up' => "
        ALTER TABLE servers 
        ADD COLUMN rcon_host VARCHAR(255) NULL AFTER tags,
        ADD COLUMN rcon_port SMALLINT UNSIGNED NULL AFTER rcon_host,
        ADD COLUMN rcon_password VARCHAR(255) NULL AFTER rcon_port,
        ADD COLUMN reward_command TEXT NULL AFTER rcon_password;

        ALTER TABLE votes 
        ADD COLUMN reward_sent TINYINT(1) DEFAULT 0 AFTER ip_address,
        ADD COLUMN reward_log TEXT NULL AFTER reward_sent;
    ",
    'down' => "
        ALTER TABLE servers 
        DROP COLUMN rcon_host,
        DROP COLUMN rcon_port,
        DROP COLUMN rcon_password,
        DROP COLUMN reward_command;

        ALTER TABLE votes 
        DROP COLUMN reward_sent,
        DROP COLUMN reward_log;
    "
];
