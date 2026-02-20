<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $db = Database::getInstance();
        
        $stats = [
            'servers' => (int) $db->query("SELECT COUNT(*) FROM servers WHERE is_active = 1")->fetchColumn(),
            'users' => (int) $db->query("SELECT COUNT(*) FROM users")->fetchColumn(),
            'votes' => (int) $db->query("SELECT COUNT(*) FROM votes")->fetchColumn(),
            'boosts_active' => (int) ($db->query("SELECT COUNT(*) FROM boost_purchases WHERE expires_at > NOW()")->fetchColumn() ?: 0),
        ];

        $pendingServers = (int) $db->query("SELECT COUNT(*) FROM servers WHERE is_approved = 0 AND is_active = 1")->fetchColumn();

        $recentServers = $db->query("SELECT name, created_at FROM servers ORDER BY created_at DESC LIMIT 5")->fetchAll();
        $topServers = $db->query("
            SELECT s.name, COUNT(v.id) as vote_count 
            FROM servers s
            LEFT JOIN votes v ON s.id = v.server_id
            GROUP BY s.id
            ORDER BY vote_count DESC 
            LIMIT 5
        ")->fetchAll();

        $health = [
            'php' => PHP_VERSION,
            'db' => $db->getAttribute(\PDO::ATTR_SERVER_VERSION),
            'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
        ];

        return $this->view('admin.index', [
            'stats' => $stats,
            'pendingServers' => $pendingServers,
            'recentServers' => $recentServers,
            'topServers' => $topServers,
            'health' => $health
        ]);
    }
}
