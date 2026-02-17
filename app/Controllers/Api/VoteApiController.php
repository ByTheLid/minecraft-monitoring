<?php

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Server;
use App\Models\Vote;

class VoteApiController extends Controller
{
    public function vote(Request $request): Response
    {
        $serverId = (int) $request->param('id');
        $server = Server::find($serverId);

        if (!$server || !$server['is_approved'] || !$server['is_active']) {
            return $this->error('NOT_FOUND', 'Server not found', [], 404);
        }

        $user = auth();
        $ip = $request->ip();

        // Can't vote for own server
        if ($user && $server['user_id'] == $user['id']) {
            return $this->error('OWN_SERVER', 'Cannot vote for your own server', [], 400);
        }

        // Rate limit: max 10 votes per hour from one IP
        if (Vote::countVotesFromIp($ip) >= 10) {
            return $this->error('RATE_LIMIT', 'Too many votes. Try again later.', [], 429);
        }

        // Already voted in 24h
        $userId = $user ? $user['id'] : null;
        if (Vote::hasVotedRecently($serverId, $userId, $ip)) {
            return $this->error('ALREADY_VOTED', 'You already voted for this server today', [], 400);
        }

        Vote::castVote($serverId, $userId, $ip);

        $newCount = Vote::countForServer($serverId);

        // Update rankings cache immediately
        $db = \App\Core\Database::getInstance();
        $db->prepare(
            "UPDATE server_rankings SET vote_count = ? WHERE server_id = ?"
        )->execute([$newCount, $serverId]);

        return $this->success(['vote_count' => $newCount]);
    }
}
