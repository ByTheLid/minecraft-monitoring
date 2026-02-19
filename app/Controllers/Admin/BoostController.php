<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\BoostPackage;
use App\Models\User;

class BoostController extends Controller
{
    public function index(Request $request): Response
    {
        $packages = BoostPackage::all('price', 'ASC');
        return $this->view('admin.boost', ['packages' => $packages]);
    }

    public function create(Request $request): Response
    {
        BoostPackage::create([
            'name' => sanitize($request->input('name', '')),
            'points' => (int) $request->input('points', 0),
            'price' => (float) $request->input('price', 0),
            'duration_days' => (int) $request->input('duration_days', 30),
            'color' => sanitize($request->input('color', '#ffcc00')),
            'features' => $request->input('features', '[]'),
            'is_popular' => (int) $request->input('is_popular', 0),
        ]);

        flash('success', 'Boost package created.');
        return $this->redirect('/admin/boost');
    }

    public function edit(Request $request): Response
    {
        $id = (int) $request->param('id');
        BoostPackage::update($id, [
            'name' => sanitize($request->input('name', '')),
            'points' => (int) $request->input('points', 0),
            'price' => (float) $request->input('price', 0),
            'duration_days' => (int) $request->input('duration_days', 30),
            'color' => sanitize($request->input('color', '#ffcc00')),
            'features' => $request->input('features', '[]'),
            'is_popular' => (int) $request->input('is_popular', 0),
        ]);

        flash('success', 'Boost package updated.');
        return $this->redirect('/admin/boost');
    }

    public function activate(Request $request): Response
    {
        $id = (int) $request->param('id');
        BoostPackage::update($id, ['is_active' => 1]);
        flash('success', 'Boost package activated.');
        return $this->redirect('/admin/boost');
    }

    public function deactivate(Request $request): Response
    {
        $id = (int) $request->param('id');
        BoostPackage::update($id, ['is_active' => 0]);
        flash('success', 'Boost package deactivated.');
        return $this->redirect('/admin/boost');
    }

    public function delete(Request $request): Response
    {
        $id = (int) $request->param('id');
        $password = $request->input('password', '');
        
        if (!User::verifyPassword(auth(), $password)) {
            flash('error', 'Incorrect password. Cannot delete package.');
            return $this->redirect('/admin/boost');
        }

        try {
            BoostPackage::delete($id);
            flash('success', 'Boost package permanently deleted.');
        } catch (\PDOException $e) {
            flash('error', 'Cannot delete package with existing purchases. Deactivate it instead.');
        }

        return $this->redirect('/admin/boost');
    }
}
