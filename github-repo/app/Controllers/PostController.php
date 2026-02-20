<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Post;

class PostController extends Controller
{
    public function index(Request $request): Response
    {
        $page = max(1, (int) $request->query('page', 1));
        $result = Post::getPublished($page, 10);

        return $this->view('posts.index', [
            'posts' => $result['data'],
            'meta' => $result['meta'],
        ]);
    }

    public function show(Request $request): Response
    {
        $id = (int) $request->param('id');
        $post = Post::findPublished($id);

        if (!$post) {
            return $this->view('errors.404', [], 404);
        }

        return $this->view('posts.show', ['post' => $post]);
    }
}
