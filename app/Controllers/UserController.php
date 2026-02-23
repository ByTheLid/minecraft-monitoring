<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $db = Database::getInstance();
        
        // Fetch top users
        $stmt = $db->query("
            SELECT u.id, u.username, u.created_at, u.role, u.bio,
                   (SELECT COUNT(*) FROM votes v WHERE v.user_id = u.id) as total_votes,
                   (SELECT COUNT(*) FROM user_achievements ua WHERE ua.user_id = u.id) as total_achievements,
                   (SELECT COUNT(*) FROM servers s WHERE s.user_id = u.id AND s.is_active = 1) as total_servers
            FROM users u
            ORDER BY total_votes DESC, total_achievements DESC
            LIMIT 50
        ");
        $users = $stmt->fetchAll();
        
        // Fetch achievements for these users to build avatars
        $userIds = array_column($users, 'id');
        $achievementsByUserId = [];
        
        if (!empty($userIds)) {
            $inClause = implode(',', array_fill(0, count($userIds), '?'));
            $achStmt = $db->prepare("SELECT user_id, achievement_key as `key`, unlocked_at FROM user_achievements WHERE user_id IN ($inClause)");
            $achStmt->execute($userIds);
            $allAchievements = $achStmt->fetchAll();
            foreach ($allAchievements as $ach) {
                // We add the static BADGES data to match what getAvatar might expect, or just the key
                $achievementsByUserId[$ach['user_id']][] = $ach;
            }
        }
        
        return $this->view('user.index', [
            'users' => $users,
            'achievementsByUserId' => $achievementsByUserId
        ]);
    }
}
