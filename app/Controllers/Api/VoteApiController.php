<?php

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Server;
use App\Models\Vote;

use App\Core\Logger;
use App\Services\VotifierService;

class VoteApiController extends Controller
{
    public function vote(Request $request): Response
    {
        $serverId = (int) $request->param('id');
        $username = trim($request->input('username', ''));
        
        // Validate username
        $errors = $this->validate(['username' => $username], [
            'username' => 'required|min:3|max:16|regex:/^[a-zA-Z0-9_]+$/'
        ]);
        
        if (!empty($errors)) {
            return $this->error('VALIDATION_ERROR', 'Invalid username', $errors, 422);
        }

        $server = Server::find($serverId);

        if (!$server || !$server['is_approved'] || !$server['is_active']) {
            return $this->error('NOT_FOUND', 'Server not found', [], 404);
        }

        $user = auth();
        $ip = $request->ip();

        // Can't vote for own server (unless testing?)
        if ($user && $server['user_id'] == $user['id']) {
             // return $this->error('OWN_SERVER', 'Cannot vote for your own server', [], 400);
             // Allow for now for testing purposes? Or keep strict?
             // Keep strict as per original code.
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

        // Cast vote
        Vote::castVote($serverId, $userId, $ip, $username);

        // Update rankings cache immediately
        $db = \App\Core\Database::getInstance();
        
        $newCount = Vote::countForServer($serverId);
        $db->prepare(
            "UPDATE server_rankings SET vote_count = ? WHERE server_id = ?"
        )->execute([$newCount, $serverId]);

        // --- Votifier / RCON Logic ---
        $rewardStatus = $this->processRewards($serverId, $username, $ip);

        return $this->success([
            'vote_count' => $newCount,
            'reward_status' => $rewardStatus
        ]);
    }

    private function processRewards(int $serverId, string $username, string $ip): string
    {
        $db = \App\Core\Database::getInstance();
        $server = Server::find($serverId);
        
        // 1. Votifier (Legacy/NuVotifier)
        try {
            $stmt = $db->prepare("SELECT * FROM votifier_keys WHERE server_id = ?");
            $stmt->execute([$serverId]);
            $keyData = $stmt->fetch();

            if ($keyData) {
                $service = new VotifierService();
                $host = $keyData['address'] ?? $server['ip'];
                $port = (int) $keyData['port'];
                $publicKey = $keyData['public_key'];

                $service->sendVote($host, $port, $publicKey, $username, $ip);
                (new Logger())->info("Votifier vote sent", ['server_id' => $serverId, 'username' => $username]);
            }
        } catch (\Throwable $e) {
            (new Logger())->error("Votifier failed", ['server_id' => $serverId, 'error' => $e->getMessage()]);
        }

        // 2. RCON Reward
        if (empty($server['rcon_host']) || empty($server['rcon_port']) || empty($server['rcon_password']) || empty($server['reward_command'])) {
            return 'not_configured';
        }

        try {
            $rcon = new \App\Services\RconService(
                $server['rcon_host'],
                (int) $server['rcon_port'],
                $server['rcon_password']
            );

            if ($rcon->connect()) {
                $command = str_replace('{player}', $username, $server['reward_command']);
                $response = $rcon->sendCommand($command);
                $rcon->disconnect();

                $this->logReward($serverId, $username, true, "Command: $command | Response: $response");
                return 'sent';
            } else {
                $this->logReward($serverId, $username, false, "RCON Connection Failed");
                return 'failed';
            }
        } catch (\Throwable $e) {
            $this->logReward($serverId, $username, false, "RCON Error: " . $e->getMessage());
            return 'failed';
        }
    }

    private function logReward(int $serverId, string $username, bool $success, string $log): void
    {
        // We need to update the LAST vote record for this user/server.
        // Since we just cast the vote, it's the most recent one.
        $db = \App\Core\Database::getInstance();
        $sql = "UPDATE votes SET reward_sent = ?, reward_log = ? 
                WHERE server_id = ? AND (username = ? OR ip_address = ?) 
                ORDER BY id DESC LIMIT 1";
        // Note: we track username in votes table now?
        // Wait, Migration 014 added 'username' to votes.
        // But Vote::castVote uses it.
        // Let's ensure we match the correct record.
        
        $db->prepare($sql)->execute([$success ? 1 : 0, $log, $serverId, $username, $_SERVER['REMOTE_ADDR'] ?? '']);
    }
}
