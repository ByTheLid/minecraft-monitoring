<?php

return [
    'up' => "
        INSERT INTO settings (`key`, `value`) VALUES 
        ('gamification_action_caps', '{\"vote\":3,\"review\":1,\"daily_login\":1}'),
        ('gamification_points_per_action', '{\"vote\":10,\"review\":25,\"daily_login\":5,\"add_server\":50,\"buy_boost\":100}'),
        ('gamification_rank_thresholds', '{\"0\":\"Novice\",\"100\":\"Bronze\",\"500\":\"Silver\",\"1500\":\"Gold\",\"5000\":\"Diamond\",\"10000\":\"Legendary\"}')
        ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);
    ",
    'down' => "
        DELETE FROM settings WHERE `key` IN ('gamification_action_caps', 'gamification_points_per_action', 'gamification_rank_thresholds')
    ",
];
