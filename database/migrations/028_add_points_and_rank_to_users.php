<?php

return [
    'up' => "
        ALTER TABLE users 
        ADD COLUMN points INT NOT NULL DEFAULT 0 AFTER role,
        ADD COLUMN `rank` VARCHAR(50) NOT NULL DEFAULT 'Новичок' AFTER points
    ",
    'down' => "
        ALTER TABLE users 
        DROP COLUMN points,
        DROP COLUMN `rank`
    ",
];
