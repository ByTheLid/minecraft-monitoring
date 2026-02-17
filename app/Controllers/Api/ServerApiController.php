<?php

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Server;
use App\Models\ServerStat;
use App\Models\Setting;

class ServerApiController extends Controller
{
    public function index(Request $request): Response
    {
        $page = max(1, (int) $request->query('page', 1));
        $perPage = min(50, max(1, (int) $request->query('per_page', 20)));
        $sort = $request->query('sort', 'rank');
        $search = sanitize($request->query('search', ''));
        $status = $request->query('status', 'all');
        $version = sanitize($request->query('version', ''));
        $tags = sanitize($request->query('tags', ''));

        $result = Server::getApproved($page, $perPage, $sort, $search, $status, $version, $tags);

        return $this->success($result['data'], $result['meta']);
    }

    public function show(Request $request): Response
    {
        $id = (int) $request->param('id');
        $server = Server::getDetail($id);

        if (!$server || !$server['is_approved'] || !$server['is_active']) {
            return $this->error('NOT_FOUND', 'Server not found', [], 404);
        }

        return $this->success($server);
    }

    public function stats(Request $request): Response
    {
        $id = (int) $request->param('id');
        $period = $request->query('period', '24h');

        if (!in_array($period, ['24h', '7d', '30d'])) {
            $period = '24h';
        }

        $history = ServerStat::getHistory($id, $period);

        return $this->success($history);
    }

    public function store(Request $request): Response
    {
        $user = auth();
        $maxServers = (int) Setting::get('max_servers_per_user', 5);

        if (Server::countByUser($user['id']) >= $maxServers) {
            return $this->error('LIMIT_REACHED', "Maximum {$maxServers} servers allowed", [], 400);
        }

        $data = $request->all();
        $errors = $this->validate($data, [
            'name' => 'required|min:3|max:100',
            'ip' => 'required|ip',
            'port' => 'between:1,65535',
        ]);

        if ($errors) {
            return $this->error('VALIDATION_ERROR', 'Validation failed', $errors, 422);
        }

        $port = (int) ($data['port'] ?? 25565);

        if (Server::isDuplicate($data['ip'], $port)) {
            return $this->error('DUPLICATE', 'Server already registered', [], 409);
        }

        $tags = isset($data['tags']) ? (is_array($data['tags']) ? $data['tags'] : explode(',', $data['tags'])) : [];
        $tags = array_filter(array_map('trim', $tags));

        $id = Server::create([
            'user_id' => $user['id'],
            'name' => sanitize($data['name']),
            'ip' => sanitize($data['ip']),
            'port' => $port,
            'description' => sanitize($data['description'] ?? ''),
            'website' => sanitize($data['website'] ?? ''),
            'tags' => json_encode($tags),
        ]);

        return $this->success(['id' => $id], [], 201);
    }

    public function update(Request $request): Response
    {
        $id = (int) $request->param('id');
        $server = Server::find($id);

        if (!$server) {
            return $this->error('NOT_FOUND', 'Server not found', [], 404);
        }

        $user = auth();
        if ($server['user_id'] != $user['id'] && $user['role'] !== 'admin') {
            return $this->error('FORBIDDEN', 'Not authorized', [], 403);
        }

        $data = $request->all();
        $update = [];

        if (isset($data['name'])) $update['name'] = sanitize($data['name']);
        if (isset($data['description'])) $update['description'] = sanitize($data['description']);
        if (isset($data['website'])) $update['website'] = sanitize($data['website']);
        if (isset($data['tags'])) {
            $tags = is_array($data['tags']) ? $data['tags'] : explode(',', $data['tags']);
            $update['tags'] = json_encode(array_filter(array_map('trim', $tags)));
        }

        if ($update) {
            Server::update($id, $update);
        }

        return $this->success(['id' => $id]);
    }

    public function destroy(Request $request): Response
    {
        $id = (int) $request->param('id');
        $server = Server::find($id);

        if (!$server) {
            return $this->error('NOT_FOUND', 'Server not found', [], 404);
        }

        $user = auth();
        if ($server['user_id'] != $user['id'] && $user['role'] !== 'admin') {
            return $this->error('FORBIDDEN', 'Not authorized', [], 403);
        }

        Server::update($id, ['is_active' => 0]);

        return $this->success(null);
    }
}
