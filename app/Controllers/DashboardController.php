<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Server;
use App\Models\Setting;
use App\Services\MinecraftPing;

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

        // Unlock Achievement
        \App\Models\Achievement::unlock($user['id'], 'server_owner');

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
        $user = \App\Models\User::find(auth()['id']);
        return $this->view('dashboard.settings', [
             'user' => $user
        ]);
    }

    public function updateSettings(Request $request): Response
    {
        $user = auth();
        $id = $user['id'];
        
        $email = sanitize($request->input('email', ''));
        $bio = sanitize($request->input('bio', ''));
        $discord = sanitize($request->input('social_discord', ''));
        $telegram = sanitize($request->input('social_telegram', ''));
        $design = $request->input('design_preference', 'aesthetic');
        
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
            $fullUser = \App\Models\User::find($user['id']);
            if (!$fullUser || !\App\Models\User::verifyPassword($fullUser, $password)) {
                 flash('error', 'Incorrect current password.');
                 return $this->redirect('/dashboard/settings');
            }
        }
        
        $updateData = ['email' => $email];
        
        if ($newPassword) {
            $updateData['password_hash'] = password_hash($newPassword, PASSWORD_BCRYPT);
        }
        
        $updateData['bio'] = $bio;
        $updateData['social_discord'] = $discord;
        $updateData['social_telegram'] = $telegram;
        $updateData['design_preference'] = in_array($design, ['aesthetic', 'lightweight']) ? $design : 'aesthetic';
        
        \App\Models\User::update($id, $updateData);
        
        // Update session
        $_SESSION['user']['email'] = $email;
        $_SESSION['user']['design_preference'] = $updateData['design_preference'];
        
        flash('success', 'Settings updated.');
        return $this->redirect('/dashboard/settings');
    }

    public function apiKeys(Request $request): Response
    {
        $user = auth();
        $keys = \App\Models\ApiKey::getByUser($user['id']);
        $newKey = flash('new_api_key');

        return $this->view('dashboard.api-keys', [
            'keys' => $keys,
            'newKey' => $newKey,
        ]);
    }

    public function generateApiKey(Request $request): Response
    {
        $user = auth();
        $name = sanitize($request->input('name', 'Default'));

        if (\App\Models\ApiKey::countByUser($user['id']) >= 5) {
            flash('error', 'Maximum 5 API keys per user.');
            return $this->redirect('/dashboard/api-keys');
        }

        $key = \App\Models\ApiKey::generate($user['id'], $name ?: 'Default');
        flash('new_api_key', $key);
        flash('success', 'API key generated successfully.');
        return $this->redirect('/dashboard/api-keys');
    }

    public function revokeApiKey(Request $request): Response
    {
        $user = auth();
        $keyId = (int) $request->input('key_id');
        \App\Models\ApiKey::deactivate($keyId, $user['id']);
        flash('success', 'API key revoked.');
        return $this->redirect('/dashboard/api-keys');
    }

    public function verifyServerForm(Request $request): Response
    {
        $id = (int) $request->param('id');
        $server = Server::find($id);

        if (!$server || ($server['user_id'] != auth()['id'] && !is_admin())) {
            return $this->redirect('/dashboard');
        }

        if ($server['is_verified'] ?? false) {
            flash('success', 'Server is already verified.');
            return $this->redirect('/dashboard');
        }

        // Generate token if not exists
        if (empty($server['verify_token'])) {
            $token = 'MCM-' . substr(md5(uniqid((string)rand(), true)), 0, 8);
            Server::update($id, ['verify_token' => $token]);
            $server['verify_token'] = $token;
        }

        return $this->view('dashboard.verify-server', ['server' => $server]);
    }

    public function verifyServer(Request $request): Response
    {
        $id = (int) $request->param('id');
        $server = Server::find($id);

        if (!$server || ($server['user_id'] != auth()['id'] && !is_admin())) {
            return $this->redirect('/dashboard');
        }

        if ($server['is_verified'] ?? false) {
            return $this->redirect('/dashboard');
        }

        $token = $server['verify_token'];
        if (!$token) {
            flash('error', 'Token not found. Please reload the page.');
            return $this->redirect("/dashboard/verify/{$id}");
        }

        // Ping server to check MOTD
        $ping = new MinecraftPing($server['ip'], $server['port'], 3);
        $status = $ping->ping();

        if (!$status) {
            flash('error', 'Could not connect to the server. Is it online?');
            return $this->redirect("/dashboard/verify/{$id}");
        }

        $motd = '';
        if (isset($status['description'])) {
            if (is_array($status['description']) && isset($status['description']['text'])) {
                $motd = $status['description']['text'];
                if (isset($status['description']['extra'])) {
                    foreach ($status['description']['extra'] as $extra) {
                        $motd .= $extra['text'] ?? '';
                    }
                }
            } else {
                $motd = is_string($status['description']) ? $status['description'] : '';
            }
        }
        
        // Remove formatting codes
        $motdClean = preg_replace('/[§&][0-9a-fk-or]/i', '', $motd);
        
        if (strpos($motdClean, $token) !== false) {
            Server::update($id, ['is_verified' => 1, 'verify_token' => null]);
            flash('success', 'Server successfully verified! ✔️');
            return $this->redirect('/dashboard');
        }

        flash('error', "Verification failed. Token '{$token}' not found in MOTD. Current MOTD: " . htmlspecialchars(substr($motdClean, 0, 50)));
        return $this->redirect("/dashboard/verify/{$id}");
    }
}
