<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Server;

class ProfileController extends Controller
{
    public function show(Request $request): Response
    {
        $username = $request->param('username');
        $user = \App\Models\User::findByUsername($username);

        if (!$user) {
            return $this->view('errors.404', [], 404);
        }

        // Get user's active servers
        $servers = Server::getByUser($user['id']);
        
        // Get recent votes
        $votes = \App\Models\Vote::getByUser($user['id'], 5);

        // Get achievements
        $achievements = \App\Models\Achievement::getForUser($user['id']);

        // Compute rank progress
        $rankData = $this->computeRankProgress($user);

        return $this->view('profile.show', [
            'user' => $user,
            'servers' => $servers,
            'votes' => $votes,
            'achievements' => $achievements,
            'rankData' => $rankData,
        ]);
    }

    private function computeRankProgress(array $user): array
    {
        $thresholds = \App\Core\AchievementEngine::getRankThresholds();
        $userPoints = (int) ($user['points'] ?? 0);
        $currentRank = $user['rank'] ?? 'Novice';

        $nextRank = null;
        $nextThreshold = null;
        $prevThreshold = 0;

        foreach ($thresholds as $threshold => $rankName) {
            if ((int) $threshold > $userPoints) {
                $nextRank = $rankName;
                $nextThreshold = (int) $threshold;
                break;
            }
            $prevThreshold = (int) $threshold;
        }

        $progressPercent = 100; // Max rank reached
        $remaining = 0;
        if ($nextThreshold !== null && ($nextThreshold - $prevThreshold) > 0) {
            $progressPercent = (int) round(($userPoints - $prevThreshold) / ($nextThreshold - $prevThreshold) * 100);
            $remaining = $nextThreshold - $userPoints;
        }

        return [
            'current' => $currentRank,
            'points' => $userPoints,
            'next' => $nextRank,
            'nextThreshold' => $nextThreshold,
            'prevThreshold' => $prevThreshold,
            'progress' => $progressPercent,
            'remaining' => $remaining,
        ];
    }
}
