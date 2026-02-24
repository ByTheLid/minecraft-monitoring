<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Models\Achievement;

class AchievementController extends Controller
{
    public function index(Request $request): Response
    {
        $achievements = Achievement::getAll();
        return $this->view('admin.achievements', ['achievements' => $achievements, 'adminPage' => 'achievements']);
    }

    public function create(Request $request): Response
    {
        $data = $request->validate([
            'achievement_key' => 'required',
            'name' => 'required',
            'description' => 'required',
            'icon' => 'required',
            'color' => 'required'
        ]);

        if (Achievement::getByKey($data['achievement_key'])) {
            flash('error', 'Achievement with this key already exists!');
            return $this->redirect('/admin/achievements');
        }

        $stmt = Database::getInstance()->prepare("
            INSERT INTO achievements (achievement_key, name, description, icon, color) 
            VALUES (:key, :name, :desc, :icon, :color)
        ");
        $stmt->execute([
            ':key' => $data['achievement_key'],
            ':name' => $data['name'],
            ':desc' => $data['description'],
            ':icon' => $data['icon'],
            ':color' => $data['color']
        ]);

        flash('success', 'Achievement created successfully.');
        return $this->redirect('/admin/achievements');
    }

    public function edit(Request $request, int $id): Response
    {
        $data = $request->validate([
            'achievement_key' => 'required',
            'name' => 'required',
            'description' => 'required',
            'icon' => 'required',
            'color' => 'required'
        ]);

        $stmt = Database::getInstance()->prepare("
            UPDATE achievements 
            SET achievement_key = :key, name = :name, description = :desc, icon = :icon, color = :color
            WHERE id = :id
        ");
        
        $stmt->execute([
            ':key' => $data['achievement_key'],
            ':name' => $data['name'],
            ':desc' => $data['description'],
            ':icon' => $data['icon'],
            ':color' => $data['color'],
            ':id' => $id
        ]);

        flash('success', 'Achievement updated successfully.');
        return $this->redirect('/admin/achievements');
    }

    public function delete(Request $request, int $id): Response
    {
        $stmt = Database::getInstance()->prepare("DELETE FROM achievements WHERE id = ?");
        $stmt->execute([$id]);

        flash('success', 'Achievement deleted successfully.');
        return $this->redirect('/admin/achievements');
    }
}
