<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailService
{
    private PHPMailer $mailer;
    private bool $isConfigured = false;

    public function __construct()
    {
        $this->mailer = new PHPMailer(true);
        $this->configure();
    }

    private function configure(): void
    {
        $host = setting('smtp_host') ?: env('SMTP_HOST');
        $port = setting('smtp_port') ?: env('SMTP_PORT', 587);
        $user = setting('smtp_user') ?: env('SMTP_USER', '');
        $pass = setting('smtp_pass') ?: env('SMTP_PASS', '');

        // We can allow empty user/pass for local tools like Mailpit
        if ($host && $port) {
            $this->mailer->isSMTP();
            $this->mailer->Host = $host;
            if ($user && $pass) {
                $this->mailer->SMTPAuth = true;
                $this->mailer->Username = $user;
                $this->mailer->Password = $pass;
                $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            } else {
                $this->mailer->SMTPAuth = false;
                $this->mailer->SMTPSecure = ''; // No encryption needed for local mailpit usually
            }
            
            if ($port == 465) {
                $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            }
            
            $this->mailer->Port = (int)$port;
            $fromEmail = setting('smtp_from_email') ?: env('SMTP_FROM_EMAIL', 'noreply@' . $_SERVER['HTTP_HOST']);
            $fromName = setting('smtp_from_name') ?: env('SMTP_FROM_NAME', 'MC Monitoring');
            $this->mailer->setFrom($fromEmail, $fromName);
            
            $this->mailer->CharSet = 'UTF-8';
            $this->isConfigured = true;
        }
    }

    public function isReady(): bool
    {
        return $this->isConfigured;
    }

    public function sendPasswordResetLink(string $toEmail, string $token): bool
    {
        if (!$this->isConfigured) {
            return false;
        }

        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($toEmail);

            $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
            $appUrl = rtrim(setting('app_url', $scheme . '://' . $_SERVER['HTTP_HOST']), '/');
            $resetLink = $appUrl . '/reset-password?token=' . $token;

            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Password Reset Request';
            
            $body = "
                <div style='font-family: sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background: #f9f9f9; border-radius: 8px;'>
                    <h2 style='color: #333;'>Reset Your Password</h2>
                    <p style='color: #555; line-height: 1.6;'>You are receiving this email because we received a password reset request for your account.</p>
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='{$resetLink}' style='background-color: #4CAF50; color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px; font-weight: bold;'>Reset Password</a>
                    </div>
                    <p style='color: #555; line-height: 1.6;'>This password reset link will expire in 60 minutes.</p>
                    <p style='color: #555; line-height: 1.6;'>If you did not request a password reset, no further action is required.</p>
                    <hr style='border: none; border-top: 1px solid #ddd; margin: 20px 0;'>
                    <p style='font-size: 12px; color: #999;'>If you're having trouble clicking the \"Reset Password\" button, copy and paste the URL below into your web browser:<br><br>
                    <a href='{$resetLink}' style='color: #4CAF50; word-break: break-all;'>{$resetLink}</a></p>
                </div>
            ";

            $this->mailer->Body = $body;
            $this->mailer->AltBody = "You are receiving this because you requested a password reset. Please copy and paste this link into your browser to reset your password: {$resetLink}";

            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            error_log("MailService Error (Reset): " . $this->mailer->ErrorInfo);
            return false;
        }
    }
}
