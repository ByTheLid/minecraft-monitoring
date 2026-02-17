<?php

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Server;
use App\Models\Setting;

class AdminApiController extends Controller
{
    public function servers(Request $request): Response
    {
        $page = max(1, (int) $request->query('page', 1));
        $filter = $request->query('filter', 'all');
        $result = Server::getAllForAdmin($page, 20, $filter);

        return $this->success($result['data'], $result['meta']);
    }

    public function approve(Request $request): Response
    {
        $id = (int) $request->param('id');
        Server::update($id, ['is_approved' => 1]);
        return $this->success(['id' => $id, 'is_approved' => true]);
    }

    public function updateSettings(Request $request): Response
    {
        $data = $request->all();
        $allowedKeys = ['rank_kv', 'rank_kb', 'rank_ko', 'rank_ku', 'max_servers_per_user'];

        foreach ($data as $key => $value) {
            if (in_array($key, $allowedKeys)) {
                Setting::set($key, (string) $value);
            }
        }

        return $this->success(null);
    }

    public function logs(Request $request): Response
    {
        $date = $request->query('date', date('Y-m-d'));
        $logFile = dirname(__DIR__, 3) . "/storage/logs/{$date}.log";

        if (!file_exists($logFile)) {
            return $this->success([]);
        }

        $lines = array_slice(file($logFile, FILE_IGNORE_NEW_LINES), -100);
        return $this->success(array_reverse($lines));
    }
}
