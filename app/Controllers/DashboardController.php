<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Server;
use App\Models\Setting;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = auth();
        $servers = Server::getByUser($user['id']);
        $recentVotes = \App\Models\Vote::getRecentForUserServers($user['id']);

        return $this->view('dashboard.index', [
            'servers' => $servers,
            'recentVotes' => $recentVotes,
        ]);
    }

    public function addServerForm(Request $request): Response
    {
        return $this->view('dashboard.add-server');
    }

    public function addServer(Request $request): Response
    {
        $user = auth();
        $maxServers = (int) Setting::get('max_servers_per_user', 5);

        if (Server::countByUser($user['id']) >= $maxServers) {
            flash('error', "You can have maximum {$maxServers} servers.");
            return $this->redirect('/dashboard/add');
        }

        $data = [
            'name' => sanitize($request->input('name', '')),
            'ip' => sanitize($request->input('ip', '')),
            'port' => (int) $request->input('port', 25565),
            'description' => sanitize($request->input('description', '')),
            'website' => sanitize($request->input('website', '')),
            'tags' => $request->input('tags', ''),
            // RCON Fields
            'rcon_host' => sanitize($request->input('rcon_host', '')),
            'rcon_port' => (int) $request->input('rcon_port', 0) ?: null,
            'rcon_password' => $request->input('rcon_password', ''), // Don't sanitize password (might have special chars)
            'reward_command' => $request->input('reward_command', ''),
        ];

        $_SESSION['_old_input'] = $data;

        $errors = $this->validate($data, [
            'name' => 'required|min:3|max:100',
            'ip' => 'required|ip',
            'port' => 'required|between:1,65535',
        ]);

        if ($data['website'] && !filter_var($data['website'], FILTER_VALIDATE_URL)) {
            $errors['website'] = 'Invalid URL';
        }

        if (Server::isDuplicate($data['ip'], $data['port'])) {
            $errors['ip'] = 'This server is already registered';
        }

        if ($errors) {
            flash('error', implode('. ', $errors));
            return $this->redirect('/dashboard/add');
        }

        // Process tags
        $tags = array_filter(array_map('trim', explode(',', $data['tags'])));

        Server::create([
            'user_id' => $user['id'],
            'name' => $data['name'],
            'ip' => $data['ip'],
            'port' => $data['port'],
            'description' => $data['description'],
            'website' => $data['website'],
            'tags' => json_encode($tags),
            'is_approved' => 1,
            'is_active' => 1,
            'rcon_host' => $data['rcon_host'] ?: null,
            'rcon_port' => $data['rcon_port'],
            'rcon_password' => $data['rcon_password'] ?: null,
            'reward_command' => $data['reward_command'] ?: null,
        ]);

        // Create ranking entry
        $serverId = \App\Core\Database::getInstance()->lastInsertId();
        if ($serverId) {
             \App\Core\Database::getInstance()->prepare(
                "INSERT INTO server_rankings (server_id) VALUES (?)"
             )->execute([$serverId]);
        }

        unset($_SESSION['_old_input']);
        flash('success', 'Server added! It will appear after moderation.');
        return $this->redirect('/dashboard');
    }

    public function editServerForm(Request $request): Response
    {
        $id = (int) $request->param('id');
        $server = Server::find($id);

        if (!$server || ($server['user_id'] != auth()['id'] && !is_admin())) {
            return $this->redirect('/dashboard');
        }

        return $this->view('dashboard.edit-server', ['server' => $server]);
    }

    public function editServer(Request $request): Response
    {
        $id = (int) $request->param('id');
        $server = Server::find($id);

        if (!$server || ($server['user_id'] != auth()['id'] && !is_admin())) {
            return $this->redirect('/dashboard');
        }

        $data = [
            'name' => sanitize($request->input('name', '')),
            'description' => sanitize($request->input('description', '')),
            'website' => sanitize($request->input('website', '')),
            'tags' => $request->input('tags', ''),
            // RCON
            'rcon_host' => sanitize($request->input('rcon_host', '')),
            'rcon_port' => (int) $request->input('rcon_port', 0) ?: null,
            'rcon_password' => $request->input('rcon_password', ''),
            'reward_command' => $request->input('reward_command', ''),
        ];

        $errors = $this->validate($data, [
            'name' => 'required|min:3|max:100',
        ]);

        if ($errors) {
            flash('error', implode('. ', $errors));
            return $this->redirect("/dashboard/edit/{$id}");
        }

        $tags = array_filter(array_map('trim', explode(',', $data['tags'])));

        Server::update($id, [
            'name' => $data['name'],
            'description' => $data['description'],
            'website' => $data['website'],
            'tags' => json_encode($tags),
            'rcon_host' => $data['rcon_host'] ?: null,
            'rcon_port' => $data['rcon_port'],
            'rcon_password' => $data['rcon_password'] ?: null,
            'reward_command' => $data['reward_command'] ?: null,
        ]);

        flash('success', 'Server updated.');
        return $this->redirect('/dashboard');
    }

    public function deleteServer(Request $request): Response
    {
        $id = (int) $request->param('id');
        $server = Server::find($id);

        if (!$server || ($server['user_id'] != auth()['id'] && !is_admin())) {
            return $this->redirect('/dashboard');
        }

        // Soft delete
        Server::update($id, ['is_active' => 0]);

        flash('success', 'Server removed.');
        return $this->redirect('/dashboard');
    }

    public function settings(Request $request): Response
    {
        return $this->view('dashboard.settings', [
             'user' => auth()
        ]);
    }

    public function updateSettings(Request $request): Response
    {
        $user = auth();
        $id = $user['id'];
        
        $email = sanitize($request->input('email', ''));
        $password = $request->input('password', '');
        $newPassword = $request->input('new_password', '');
        
        // Validation
        $rules = ['email' => 'required|email'];
        if ($newPassword) {
            $rules['password'] = 'required'; // Current password required to change
            $rules['new_password'] = 'min:6';
        }
        
        $errors = $this->validate([
            'email' => $email,
            'password' => $password,
            'new_password' => $newPassword
        ], $rules);
        
        if ($errors) {
            flash('error', implode('. ', $errors));
             return $this->redirect('/dashboard/settings');
        }
        
        // Check email uniqueness if changed
        if ($email !== $user['email']) {
            if (\App\Models\User::isEmailTaken($email)) {
                 flash('error', 'Email is already taken.');
                 return $this->redirect('/dashboard/settings');
            }
        }
        
        // Verify current password if changing password or high security action
        // For simplicity, verify only if changing password
        if ($newPassword) {
            if (!\App\Models\User::verifyPassword($user, $password)) {
                 flash('error', 'Incorrect current password.');
                 return $this->redirect('/dashboard/settings');
            }
        }
        
        $updateData = ['email' => $email];
        
        if ($newPassword) {
            $updateData['password_hash'] = password_hash($newPassword, PASSWORD_BCRYPT);
        }
        
        \App\Models\User::update($id, $updateData);
        
        // Update session
        $_SESSION['user']['email'] = $email;
        
        flash('success', 'Settings updated.');
        return $this->redirect('/dashboard/settings');
    }
}
