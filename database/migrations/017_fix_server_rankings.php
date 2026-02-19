<?php

return [
    'up' => function (PDO $db) {
        $db->exec("
            INSERT INTO server_rankings (server_id, rank_score, vote_count, boost_points)
            SELECT id, 0, 0, 0 FROM servers
            WHERE id NOT IN (SELECT server_id FROM server_rankings)
        ");
    },

    'down' => function (PDO $db) {
        // No reverse needed really
    }
];
