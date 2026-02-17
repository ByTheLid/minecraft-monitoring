<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Models\Server;
use App\Models\User;
use App\Models\Post;
use App\Models\Setting;
use App\Models\BoostPackage;

class AdminController extends Controller
{
    public function index(Request $request): Response
    {
        $db = Database::getInstance();

        $totalServers = (int) $db->query("SELECT COUNT(*) FROM servers WHERE is_active = 1")->fetchColumn();
        $pendingServers = (int) $db->query("SELECT COUNT(*) FROM servers WHERE is_approved = 0 AND is_active = 1")->fetchColumn();
        $totalUsers = (int) $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $todayVotes = (int) $db->query("SELECT COUNT(*) FROM votes WHERE voted_at > CURDATE()")->fetchColumn();

        return $this->view('admin.index', [
            'totalServers' => $totalServers,
            'pendingServers' => $pendingServers,
            'totalUsers' => $totalUsers,
            'todayVotes' => $todayVotes,
        ]);
    }

    public function servers(Request $request): Response
    {
        $filter = $request->query('filter', 'all');
        $page = max(1, (int) $request->query('page', 1));
        $result = Server::getAllForAdmin($page, 20, $filter);

        return $this->view('admin.servers', [
            'servers' => $result['data'],
            'meta' => $result['meta'],
            'filter' => $filter,
        ]);
    }

    public function approveServer(Request $request): Response
    {
        $id = (int) $request->param('id');
        Server::update($id, ['is_approved' => 1]);
        flash('success', 'Server approved.');
        return $this->redirect('/admin/servers?filter=pending');
    }

    public function rejectServer(Request $request): Response
    {
        $id = (int) $request->param('id');
        Server::update($id, ['is_active' => 0]);
        flash('success', 'Server rejected.');
        return $this->redirect('/admin/servers?filter=pending');
    }

    public function users(Request $request): Response
    {
        $page = max(1, (int) $request->query('page', 1));
        $result = User::paginate($page, 20);

        return $this->view('admin.users', [
            'users' => $result['data'],
            'meta' => $result['meta'],
        ]);
    }

    public function toggleUser(Request $request): Response
    {
        $id = (int) $request->param('id');
        $user = User::find($id);

        if ($user) {
            User::update($id, ['is_active' => $user['is_active'] ? 0 : 1]);
            flash('success', 'User status updated.');
        }

        return $this->redirect('/admin/users');
    }

    public function posts(Request $request): Response
    {
        $page = max(1, (int) $request->query('page', 1));
        $result = Post::paginate($page, 20, '1=1', [], 'created_at', 'DESC');

        return $this->view('admin.posts', [
            'posts' => $result['data'],
            'meta' => $result['meta'],
        ]);
    }

    public function createPostForm(Request $request): Response
    {
        return $this->view('admin.post-form', ['post' => null]);
    }

    public function createPost(Request $request): Response
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

    public function editPostForm(Request $request): Response
    {
        $id = (int) $request->param('id');
        $post = Post::find($id);

        if (!$post) {
            return $this->redirect('/admin/posts');
        }

        return $this->view('admin.post-form', ['post' => $post]);
    }

    public function editPost(Request $request): Response
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

    public function deletePost(Request $request): Response
    {
        $id = (int) $request->param('id');
        Post::delete($id);
        flash('success', 'Post deleted.');
        return $this->redirect('/admin/posts');
    }

    public function settings(Request $request): Response
    {
        $settings = Setting::getAll();
        return $this->view('admin.settings', ['settings' => $settings]);
    }

    public function updateSettings(Request $request): Response
    {
        $keys = ['rank_kv', 'rank_kb', 'rank_ko', 'rank_ku', 'max_servers_per_user'];
        foreach ($keys as $key) {
            $value = $request->input($key);
            if ($value !== null) {
                Setting::set($key, $value);
            }
        }

        flash('success', 'Settings updated.');
        return $this->redirect('/admin/settings');
    }

    public function boostPackages(Request $request): Response
    {
        $packages = BoostPackage::all('price', 'ASC');
        return $this->view('admin.boost', ['packages' => $packages]);
    }

    public function createBoostPackage(Request $request): Response
    {
        BoostPackage::create([
            'name' => sanitize($request->input('name', '')),
            'points' => (int) $request->input('points', 0),
            'price' => (float) $request->input('price', 0),
            'duration_days' => (int) $request->input('duration_days', 30),
        ]);

        flash('success', 'Boost package created.');
        return $this->redirect('/admin/boost');
    }

    public function editBoostPackage(Request $request): Response
    {
        $id = (int) $request->param('id');
        BoostPackage::update($id, [
            'name' => sanitize($request->input('name', '')),
            'points' => (int) $request->input('points', 0),
            'price' => (float) $request->input('price', 0),
            'duration_days' => (int) $request->input('duration_days', 30),
        ]);

        flash('success', 'Boost package updated.');
        return $this->redirect('/admin/boost');
    }

    public function deleteBoostPackage(Request $request): Response
    {
        $id = (int) $request->param('id');
        BoostPackage::update($id, ['is_active' => 0]);
        flash('success', 'Boost package deactivated.');
        return $this->redirect('/admin/boost');
    }
}
