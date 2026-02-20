<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Server;
use App\Models\Post;

class HomeController extends Controller
{
    public function index(Request $request): Response
    {
        try {
            $topServers = Server::getApproved(1, 10, 'rank');
            $latestPosts = Post::getLatest(3);
        } catch (\Throwable $e) {
            $topServers = ['data' => []];
            $latestPosts = [];
        }

        return $this->view('home', [
            'servers' => $topServers['data'],
            'posts' => $latestPosts,
        ]);
    }

    public function servers(Request $request): Response
    {
        $page = max(1, (int) $request->query('page', 1));
        $sort = $request->query('sort', 'rank');
        $search = sanitize($request->query('search', ''));
        $status = $request->query('status', 'all');
        $version = sanitize($request->query('version', ''));
        $tags = sanitize($request->query('tags', ''));

        $result = Server::getApproved($page, 20, $sort, $search, $status, $version, $tags);

        return $this->view('servers.index', [
            'servers' => $result['data'],
            'meta' => $result['meta'],
            'filters' => compact('sort', 'search', 'status', 'version', 'tags'),
        ]);
    }

    public function serverDetail(Request $request): Response
    {
        $id = (int) $request->param('id');
        $server = Server::getDetail($id);

        if (!$server || (!$server['is_approved'] && (!auth() || (auth()['id'] != $server['user_id'] && !is_admin())))) {
            return $this->view('errors.404', [], 404);
        }

        return $this->view('servers.detail', [
            'server' => $server,
        ]);
    }
}
