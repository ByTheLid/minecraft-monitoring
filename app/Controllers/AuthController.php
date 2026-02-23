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

    public function forgotPasswordForm(Request $request): Response
    {
        if (auth()) {
            return $this->redirect('/dashboard');
        }
        return $this->view('auth.forgot-password');
    }

    public function sendResetLink(Request $request): Response
    {
        $email = sanitize($request->input('email', ''));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('error', 'Please enter a valid email address.');
            return $this->redirect('/forgot-password');
        }

        $user = User::findByEmail($email);
        if ($user) {
            $token = bin2hex(random_bytes(32));
            User::createPasswordResetToken($email, $token);

            $mailService = new \App\Services\MailService();
            if ($mailService->isReady()) {
                if ($mailService->sendPasswordResetLink($email, $token)) {
                    flash('success', 'If this email exists in our system, a password reset link has been sent to it.');
                } else {
                    flash('error', 'Failed to send the email. Please contact an administrator.');
                }
            } else {
                flash('error', 'Mail service is not configured. Please contact an administrator.');
            }
        } else {
            // For security, do not reveal if email exists
            flash('success', 'If this email exists in our system, a password reset link has been sent to it.');
        }

        return $this->redirect('/forgot-password');
    }

    public function resetPasswordForm(Request $request): Response
    {
        if (auth()) {
            return $this->redirect('/dashboard');
        }

        $token = $request->query('token');
        if (!$token || !User::verifyPasswordResetToken($token)) {
            flash('error', 'Invalid or expired password reset token.');
            return $this->redirect('/forgot-password');
        }

        return $this->view('auth.reset-password', ['token' => $token]);
    }

    public function resetPassword(Request $request): Response
    {
        $token = $request->input('token', '');
        $password = $request->input('password', '');
        $passwordConfirm = $request->input('password_confirm', '');

        $email = User::verifyPasswordResetToken($token);
        if (!$email) {
            flash('error', 'Invalid or expired password reset token.');
            return $this->redirect('/forgot-password');
        }

        if (strlen($password) < 8) {
            flash('error', 'Password must be at least 8 characters long.');
            return $this->redirect('/reset-password?token=' . $token);
        }

        if ($password !== $passwordConfirm) {
            flash('error', 'Passwords do not match.');
            return $this->redirect('/reset-password?token=' . $token);
        }

        if (User::updatePasswordByEmail($email, $password)) {
            User::deletePasswordResetTokens($email);
            flash('success', 'Your password has been successfully reset. You can now login.');
            return $this->redirect('/login');
        }

        flash('error', 'An error occurred while resetting your password. Please try again later.');
        return $this->redirect('/reset-password?token=' . $token);
    }
}
