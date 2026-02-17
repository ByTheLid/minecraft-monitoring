<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\User;
use App\Services\AuthService;

class AuthController extends Controller
{
    public function loginForm(Request $request): Response
    {
        if (auth()) {
            return $this->redirect('/dashboard');
        }
        return $this->view('auth.login');
    }

    public function login(Request $request): Response
    {
        $login = sanitize($request->input('login', ''));
        $password = $request->input('password', '');

        $_SESSION['_old_input'] = ['login' => $login];

        if (!$login || !$password) {
            flash('error', 'Please fill in all fields.');
            return $this->redirect('/login');
        }

        $user = AuthService::attempt($login, $password, $request->ip(), $request->userAgent());

        if (!$user) {
            flash('error', 'Invalid credentials or too many attempts.');
            return $this->redirect('/login');
        }

        unset($_SESSION['_old_input']);
        flash('success', 'Welcome back, ' . e($user['username']) . '!');
        return $this->redirect('/dashboard');
    }

    public function registerForm(Request $request): Response
    {
        if (auth()) {
            return $this->redirect('/dashboard');
        }
        return $this->view('auth.register');
    }

    public function register(Request $request): Response
    {
        $data = [
            'username' => sanitize($request->input('username', '')),
            'email' => sanitize($request->input('email', '')),
            'password' => $request->input('password', ''),
            'password_confirm' => $request->input('password_confirm', ''),
        ];

        $_SESSION['_old_input'] = ['username' => $data['username'], 'email' => $data['email']];

        // Validate
        $errors = $this->validate($data, [
            'username' => 'required|min:3|max:32',
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $data['username'])) {
            $errors['username'] = 'Username can only contain letters, numbers and underscores';
        }

        if ($data['password'] !== $data['password_confirm']) {
            $errors['password_confirm'] = 'Passwords do not match';
        }

        if (empty($errors) && User::isUsernameTaken($data['username'])) {
            $errors['username'] = 'Username is already taken';
        }

        if (empty($errors) && User::isEmailTaken($data['email'])) {
            $errors['email'] = 'Email is already registered';
        }

        if ($errors) {
            flash('error', implode('. ', $errors));
            return $this->redirect('/register');
        }

        $userId = User::register($data['username'], $data['email'], $data['password']);

        // Auto-login
        AuthService::attempt($data['username'], $data['password'], $request->ip(), $request->userAgent());

        unset($_SESSION['_old_input']);
        flash('success', 'Registration successful! Welcome aboard.');
        return $this->redirect('/dashboard');
    }

    public function logout(Request $request): Response
    {
        AuthService::logout();
        return $this->redirect('/');
    }
}
