<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;

class HealthController extends Controller
{
    public function index(Request $request): Response
    {
        try {
            $db = Database::getInstance();
            $db->query("SELECT 1");
            $dbStatus = 'ok';
        } catch (\Throwable $e) {
            $dbStatus = 'error';
        }

        return $this->success([
            'status' => 'ok',
            'database' => $dbStatus,
            'timestamp' => date('c'),
        ]);
    }
}
