<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Review;

class ReviewController extends Controller
{
    public function index(Request $request): Response
    {
        // Simple manual pagination for reviews
        $page = max(1, (int) $request->query('page', 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;

        // For admin we want to fetch all recent reviews across all servers.
        // We will extend the Review model inline or create a custom query here for speed.
        $db = \App\Core\Database::getInstance();
        $sql = "SELECT r.*, u.username, s.name as server_name 
                FROM server_reviews r
                JOIN users u ON r.user_id = u.id
                JOIN servers s ON r.server_id = s.id
                ORDER BY r.created_at DESC
                LIMIT ? OFFSET ?";
                
        $stmt = $db->prepare($sql);
        $stmt->bindValue(1, $limit, \PDO::PARAM_INT);
        $stmt->bindValue(2, $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $reviews = $stmt->fetchAll();

        $countStmt = $db->query("SELECT COUNT(*) FROM server_reviews");
        $total = (int) $countStmt->fetchColumn();
        $pages = ceil($total / $limit);

        return $this->view('admin.reviews', [
            'reviews' => $reviews,
            'page' => $page,
            'pages' => $pages
        ]);
    }

    public function delete(Request $request): Response
    {
        $id = (int) $request->param('id');
        $review = Review::find($id);

        if (!$review) {
            flash('error', 'Review not found.');
            return $this->redirect('/admin/reviews');
        }

        Review::delete($id);
        flash('success', 'Review deleted successfully.');
        return $this->redirect('/admin/reviews');
    }
}
