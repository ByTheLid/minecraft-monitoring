<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\User;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $page = max(1, (int) $request->query('page', 1));
        $result = User::paginate($page, 20);

        return $this->view('admin.users', [
            'users' => $result['data'],
            'meta' => $result['meta'],
        ]);
    }

    public function toggle(Request $request): Response
    {
        $id = (int) $request->param('id');
        $user = User::find($id);

        if ($user) {
            User::update($id, ['is_active' => $user['is_active'] ? 0 : 1]);
            flash('success', 'User status updated.');
        }

        return $this->redirect('/admin/users');
    }
}
