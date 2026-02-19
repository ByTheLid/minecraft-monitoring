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

        return $this->view('profile.show', [
            'user' => $user,
            'servers' => $servers,
            'votes' => $votes,
        ]);
    }
}
