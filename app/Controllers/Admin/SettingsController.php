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
            'social_discord', 'social_vk', 'social_telegram',
            'asset_logo', 'asset_favicon',
            'smtp_host', 'smtp_port', 'smtp_user', 'smtp_pass', 'smtp_from_email', 'smtp_from_name'
        ];
        
        foreach ($keys as $key) {
            $value = $request->input($key);
            if ($value !== null) {
                Setting::set($key, $value);
            }
        }

        $jsonKeys = [
            'gamification_action_caps',
            'gamification_points_per_action',
            'gamification_rank_thresholds'
        ];
        foreach ($jsonKeys as $key) {
            $value = $request->input($key);
            if ($value !== null && is_array($value)) {
                Setting::set($key, json_encode($value));
            }
        }

        flash('success', 'Settings updated.');
        return $this->redirect('/admin/settings');
    }
}
