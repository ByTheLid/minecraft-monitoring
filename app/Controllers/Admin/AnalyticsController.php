<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Vote;
use App\Core\Database;

class AnalyticsController extends Controller
{
    public function index(Request $request): Response
    {
        $db = Database::getInstance();
        $page = (int) $request->input('page', 1);
        $perPage = 20;

        $search = $request->input('search', '');
        
        $where = "1=1";
        $params = [];

        if ($search) {
            $where .= " AND (v.username LIKE ? OR s.name LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        // Count total
        $stmt = $db->prepare("
            SELECT COUNT(*) 
            FROM votes v 
            LEFT JOIN servers s ON v.server_id = s.id 
            WHERE $where
        ");
        $stmt->execute($params);
        $total = $stmt->fetchColumn();

        $totalPages = ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;

        // Fetch votes
        $stmt = $db->prepare("
            SELECT v.*, s.name as server_name 
            FROM votes v 
            LEFT JOIN servers s ON v.server_id = s.id 
            WHERE $where 
            ORDER BY v.voted_at DESC 
            LIMIT $perPage OFFSET $offset
        ");
        $stmt->execute($params);
        $votes = $stmt->fetchAll();

        return $this->view('admin.analytics', [
            'votes' => $votes,
            'page' => $page,
            'totalPages' => $totalPages,
            'search' => $search
        ]);
    }
}
