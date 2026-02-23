<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Review;
use App\Models\Server;

class ReviewController extends Controller
{
    public function store(Request $request): Response
    {
        $user = auth();
        if (!$user) {
            flash('error', 'You must be logged in to leave a review.');
            return $this->redirect('/login');
        }

        $serverId = (int) $request->param('id');
        $server = Server::find($serverId);

        if (!$server || !$server['is_active']) {
            flash('error', 'Server not found.');
            return $this->redirect('/');
        }

        // Check if user already reviewed
        if (Review::hasUserReviewed($serverId, $user['id'])) {
            flash('error', 'You have already reviewed this server.');
            return $this->redirect("/server/{$serverId}");
        }

        $rating = (int) $request->input('rating', 0);
        $comment = trim($request->input('comment', ''));

        $errors = [];
        if ($rating < 1 || $rating > 5) {
            $errors[] = 'Please select a rating between 1 and 5 stars';
        }
        if (strlen($comment) < 10) {
            $errors[] = 'Your review comment must be at least 10 characters long';
        }

        if (!empty($errors)) {
            flash('error', implode('. ', $errors));
            return $this->redirect("/server/{$serverId}");
        }

        Review::create([
            'server_id' => $serverId,
            'user_id' => $user['id'],
            'rating' => $rating,
            'comment' => sanitize($comment) // Only allow safe HTML
        ]);

        flash('success', 'Thank you! Your review has been posted.');
        return $this->redirect("/server/{$serverId}");
    }
}
