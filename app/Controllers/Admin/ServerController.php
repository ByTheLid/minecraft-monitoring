<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Models\Server;
use App\Models\Vote;

class ServerController extends Controller
{
    public function index(Request $request): Response
    {
        $filter = $request->query('filter', 'all');
        $page = max(1, (int) $request->query('page', 1));
        $result = Server::getAllForAdmin($page, 20, $filter);

        return $this->view('admin.servers', [
            'servers' => $result['data'],
            'meta' => $result['meta'],
            'filter' => $filter,
        ]);
    }

    public function approve(Request $request): Response
    {
        $id = (int) $request->param('id');
        Server::update($id, ['is_approved' => 1]);
        flash('success', 'Server approved.');
        return $this->redirect('/admin/servers?filter=pending');
    }

    public function reject(Request $request): Response
    {
        $id = (int) $request->param('id');
        Server::update($id, ['is_active' => 0]);
        flash('success', 'Server rejected/blocked.');
        return $this->redirect('/admin/servers?filter=pending');
    }

    public function unblock(Request $request): Response
    {
        $id = (int) $request->param('id');
        Server::update($id, ['is_active' => 1]);
        flash('success', 'Server unblocked.');
        return $this->redirect('/admin/servers?filter=blocked');
    }

    public function manualVote(Request $request): Response
    {
        $serverId = (int) $request->param('id');
        $count = (int) $request->input('count', 1);
        
        for ($i = 0; $i < $count; $i++) {
            Vote::create([
                'server_id' => $serverId,
                'user_id' => auth()['id'],
                'username' => 'AdminManual',
                'ip_address' => '127.0.0.1', 
            ]);
        }

        $db = Database::getInstance();
        $newCount = Vote::countForServer($serverId);
        $db->prepare("UPDATE server_rankings SET vote_count = ? WHERE server_id = ?")->execute([$newCount, $serverId]);

        flash('success', "Added {$count} manual votes.");
        return $this->redirect('/admin/servers');
    }

    public function manualBoost(Request $request): Response
    {
        $serverId = (int) $request->param('id');
        $days = (int) $request->input('days', 30);
        $points = (int) $request->input('points', 0);

        $db = Database::getInstance();
        $db->prepare("
            INSERT INTO boost_purchases (server_id, user_id, package_id, points, expires_at)
            VALUES (?, ?, NULL, ?, DATE_ADD(NOW(), INTERVAL ? DAY))
        ")->execute([
            $serverId, 
            auth()['id'], 
            $points, 
            $days
        ]);

        flash('success', "Added manual boost for {$days} days.");
        return $this->redirect('/admin/servers');
    }
}
