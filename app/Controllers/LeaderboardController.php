<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;

class LeaderboardController extends Controller
{
    public function index(Request $request): Response
    {
        $month = $request->query('month', date('Y-m'));
        
        // Validate month format
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            $month = date('Y-m');
        }

        $db = Database::getInstance();

        // Get top 100 for selected month
        $stmt = $db->prepare("
            SELECT ml.vote_count, ml.points_earned, ml.`rank`,
                   u.id as user_id, u.username, u.rank as user_rank, u.points as total_points
            FROM monthly_leaderboard ml
            JOIN users u ON ml.user_id = u.id
            WHERE ml.year_month = ?
            ORDER BY ml.vote_count DESC, ml.points_earned DESC
            LIMIT 100
        ");
        $stmt->execute([$month]);
        $leaderboard = $stmt->fetchAll();

        // Get available months for the dropdown
        $months = $db->query("
            SELECT DISTINCT year_month 
            FROM monthly_leaderboard 
            ORDER BY year_month DESC 
            LIMIT 12
        ")->fetchAll(\PDO::FETCH_COLUMN);

        // If current month not in list, add it
        if (!in_array(date('Y-m'), $months)) {
            array_unshift($months, date('Y-m'));
        }

        return $this->view('leaderboard.index', [
            'leaderboard' => $leaderboard,
            'currentMonth' => $month,
            'months' => $months,
        ]);
    }
}
