<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Setting;

class SettingsController extends Controller
{
    public function index(Request $request): Response
    {
        $settings = Setting::getAll();
        return $this->view('admin.settings', ['settings' => $settings]);
    }

    public function update(Request $request): Response
    {
        $keys = [
            'rank_kv', 'rank_kb', 'rank_ko', 'rank_ku', 
            'max_servers_per_user',
            'site_name', 'site_description', 'seo_keywords',
            'social_discord', 'social_vk', 'social_telegram'
        ];
        
        foreach ($keys as $key) {
            $value = $request->input($key);
            if ($value !== null) {
                Setting::set($key, $value);
            }
        }

        flash('success', 'Settings updated.');
        return $this->redirect('/admin/settings');
    }
}
