<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\User;
use App\Services\SecurityService;

class TwoFactorController extends Controller
{
    /**
     * GET /dashboard/2fa/setup — Show 2FA setup page with secret + QR
     */
    public function setup(Request $request): Response
    {
        $user = User::find(auth()['id']);

        if ($user['two_factor_enabled']) {
            flash('info', 'Two-factor authentication is already enabled.');
            return $this->redirect('/dashboard/settings');
        }

        // Generate secret if not already stored
        $secret = $user['two_factor_secret'];
        if (!$secret) {
            $secret = $this->generateSecret();
            User::update($user['id'], ['two_factor_secret' => $secret]);
        }

        // Generate provisioning URI for QR code
        $issuer = urlencode(env('APP_NAME', 'MC Monitoring'));
        $account = urlencode($user['username']);
        $otpAuthUrl = "otpauth://totp/{$issuer}:{$account}?secret={$secret}&issuer={$issuer}&digits=6&period=30";

        // Use Google Chart API for QR code (no dependency needed)
        $qrUrl = "https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl=" . urlencode($otpAuthUrl);

        return $this->view('dashboard.2fa-setup', [
            'secret' => $secret,
            'qrUrl' => $qrUrl,
            'user' => $user,
        ]);
    }

    /**
     * POST /dashboard/2fa/enable — Verify OTP and enable 2FA
     */
    public function enable(Request $request): Response
    {
        $user = User::find(auth()['id']);
        $code = trim($request->input('code', ''));

        if (!$code || strlen($code) !== 6 || !ctype_digit($code)) {
            flash('error', 'Please enter a valid 6-digit code.');
            return $this->redirect('/dashboard/2fa/setup');
        }

        $secret = $user['two_factor_secret'];
        if (!$secret) {
            flash('error', 'No 2FA secret found. Please restart setup.');
            return $this->redirect('/dashboard/2fa/setup');
        }

        // Verify TOTP code
        if (!$this->verifyTotp($secret, $code)) {
            flash('error', 'Invalid code. Please try again.');
            return $this->redirect('/dashboard/2fa/setup');
        }

        // Enable 2FA
        User::update($user['id'], [
            'two_factor_enabled' => 1,
            'requires_2fa' => 0, // No longer required since it's active
        ]);

        // Generate backup codes
        $backupCodes = SecurityService::generateBackupCodes($user['id']);

        flash('success', 'Two-factor authentication enabled!');
        flash('backup_codes', json_encode($backupCodes));
        return $this->redirect('/dashboard/2fa/setup');
    }

    /**
     * POST /dashboard/2fa/disable — Disable 2FA (requires password)
     */
    public function disable(Request $request): Response
    {
        $user = User::find(auth()['id']);
        $password = $request->input('password', '');

        if (!User::verifyPassword($user, $password)) {
            flash('error', 'Incorrect password.');
            return $this->redirect('/dashboard/settings');
        }

        User::update($user['id'], [
            'two_factor_enabled' => 0,
            'two_factor_secret' => null,
        ]);

        flash('success', 'Two-factor authentication disabled.');
        return $this->redirect('/dashboard/settings');
    }

    /**
     * GET /2fa/verify — Show 2FA verification form during login
     */
    public function verifyForm(Request $request): Response
    {
        if (!isset($_SESSION['2fa_pending_user_id'])) {
            return $this->redirect('/login');
        }

        return $this->view('auth.2fa-verify');
    }

    /**
     * POST /2fa/verify — Verify 2FA code during login
     */
    public function verify(Request $request): Response
    {
        $userId = $_SESSION['2fa_pending_user_id'] ?? null;
        if (!$userId) {
            return $this->redirect('/login');
        }

        $user = User::find($userId);
        if (!$user) {
            unset($_SESSION['2fa_pending_user_id']);
            return $this->redirect('/login');
        }

        $code = trim($request->input('code', ''));

        // Try TOTP first
        $valid = $this->verifyTotp($user['two_factor_secret'], $code);

        // Try backup code if TOTP fails
        if (!$valid) {
            $valid = SecurityService::verifyBackupCode($userId, $code);
        }

        if (!$valid) {
            flash('error', 'Invalid verification code.');
            return $this->redirect('/2fa/verify');
        }

        // Complete login
        unset($_SESSION['2fa_pending_user_id']);
        $_SESSION['user'] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'role' => $user['role'],
            'design_preference' => $user['design_preference'] ?? 'aesthetic',
        ];
        session_regenerate_id(true);

        flash('success', 'Welcome back!');
        return $this->redirect('/dashboard');
    }

    /**
     * Generate a random Base32 secret for TOTP
     */
    private function generateSecret(int $length = 16): string
    {
        $base32chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';
        $bytes = random_bytes($length);
        for ($i = 0; $i < $length; $i++) {
            $secret .= $base32chars[ord($bytes[$i]) % 32];
        }
        return $secret;
    }

    /**
     * Verify a TOTP code against a secret (RFC 6238)
     * Pure PHP implementation — no external dependencies
     */
    private function verifyTotp(string $secret, string $code, int $window = 1): bool
    {
        $timestamp = time();
        $period = 30;

        for ($i = -$window; $i <= $window; $i++) {
            $counter = intdiv($timestamp, $period) + $i;
            $expectedCode = $this->generateTotp($secret, $counter);
            if (hash_equals($expectedCode, $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate a TOTP code for a given counter (RFC 4226/6238 style)
     */
    private function generateTotp(string $secret, int $counter): string
    {
        // Decode Base32 secret
        $key = $this->base32Decode($secret);

        // Counter as 8-byte big-endian
        $data = pack('N*', 0, $counter);

        // HMAC-SHA1
        $hash = hash_hmac('sha1', $data, $key, true);

        // Dynamic truncation
        $offset = ord($hash[19]) & 0x0F;
        $value = (
            ((ord($hash[$offset]) & 0x7F) << 24) |
            ((ord($hash[$offset + 1]) & 0xFF) << 16) |
            ((ord($hash[$offset + 2]) & 0xFF) << 8) |
            (ord($hash[$offset + 3]) & 0xFF)
        );

        return str_pad($value % 1000000, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Decode a Base32 string
     */
    private function base32Decode(string $input): string
    {
        $map = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $input = strtoupper(rtrim($input, '='));
        $buffer = 0;
        $bitsLeft = 0;
        $output = '';

        for ($i = 0; $i < strlen($input); $i++) {
            $val = strpos($map, $input[$i]);
            if ($val === false) continue;
            $buffer = ($buffer << 5) | $val;
            $bitsLeft += 5;
            if ($bitsLeft >= 8) {
                $bitsLeft -= 8;
                $output .= chr(($buffer >> $bitsLeft) & 0xFF);
            }
        }

        return $output;
    }
}
