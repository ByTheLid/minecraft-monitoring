<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Post;

class PostController extends Controller
{
    public function index(Request $request): Response
    {
        $page = max(1, (int) $request->query('page', 1));
        $result = Post::paginate($page, 20, '1=1', [], 'created_at', 'DESC');

        return $this->view('admin.posts', [
            'posts' => $result['data'],
            'meta' => $result['meta'],
        ]);
    }

    public function createForm(Request $request): Response
    {
        return $this->view('admin.post-form', ['post' => null]);
    }

    public function create(Request $request): Response
    {
        $data = [
            'title' => sanitize($request->input('title', '')),
            'content' => $request->input('content', ''),
            'category' => $request->input('category', 'news'),
            'is_published' => $request->input('is_published') ? 1 : 0,
        ];

        $errors = $this->validate($data, [
            'title' => 'required|min:3|max:255',
            'content' => 'required',
        ]);

        if ($errors) {
            flash('error', implode('. ', $errors));
            return $this->redirect('/admin/posts/create');
        }

        Post::create([
            'author_id' => auth()['id'],
            'title' => $data['title'],
            'slug' => slug($data['title']),
            'content' => $data['content'],
            'category' => $data['category'],
            'is_published' => $data['is_published'],
            'published_at' => $data['is_published'] ? date('Y-m-d H:i:s') : null,
        ]);

        flash('success', 'Post created.');
        return $this->redirect('/admin/posts');
    }

    public function editForm(Request $request): Response
    {
        $id = (int) $request->param('id');
        $post = Post::find($id);

        if (!$post) {
            return $this->redirect('/admin/posts');
        }

        return $this->view('admin.post-form', ['post' => $post]);
    }

    public function edit(Request $request): Response
    {
        $id = (int) $request->param('id');
        $data = [
            'title' => sanitize($request->input('title', '')),
            'content' => $request->input('content', ''),
            'category' => $request->input('category', 'news'),
            'is_published' => $request->input('is_published') ? 1 : 0,
        ];

        $wasPublished = Post::find($id)['is_published'] ?? 0;

        Post::update($id, [
            'title' => $data['title'],
            'slug' => slug($data['title']),
            'content' => $data['content'],
            'category' => $data['category'],
            'is_published' => $data['is_published'],
            'published_at' => ($data['is_published'] && !$wasPublished) ? date('Y-m-d H:i:s') : null,
        ]);

        flash('success', 'Post updated.');
        return $this->redirect('/admin/posts');
    }

    public function delete(Request $request): Response
    {
        $id = (int) $request->param('id');
        Post::delete($id);
        flash('success', 'Post deleted.');
        return $this->redirect('/admin/posts');
    }
}
