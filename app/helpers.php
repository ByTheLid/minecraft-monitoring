<?php

use App\Core\Env;
use App\Core\Database;

function env(string $key, mixed $default = null): mixed
{
    return Env::get($key, $default);
}

function config(string $key, mixed $default = null): mixed
{
    static $configs = [];

    $parts = explode('.', $key, 2);
    $file = $parts[0];

    if (!isset($configs[$file])) {
        $path = dirname(__DIR__) . "/config/{$file}.php";
        $configs[$file] = file_exists($path) ? require $path : [];
    }

    if (isset($parts[1])) {
        return $configs[$file][$parts[1]] ?? $default;
    }

    return $configs[$file];
}

function base_url(string $path = ''): string
{
    $url = rtrim(env('APP_URL', ''), '/');
    return $path ? $url . '/' . ltrim($path, '/') : $url;
}

function asset(string $path): string
{
    return base_url($path);
}

function redirect(string $url): void
{
    header("Location: {$url}");
    exit;
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrf_token(): string
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf_token" value="' . csrf_token() . '">';
}

function csrf_verify(string $token): bool
{
    return hash_equals(csrf_token(), $token);
}

function flash(string $key, mixed $value = null): mixed
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if ($value !== null) {
        $_SESSION['_flash'][$key] = $value;
        return null;
    }

    $val = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);
    return $val;
}

function old(string $key, string $default = ''): string
{
    return $_SESSION['_old_input'][$key] ?? $default;
}

function auth(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_admin(): bool
{
    return (auth()['role'] ?? '') === 'admin';
}

function sanitize(string $value): string
{
    return trim(strip_tags($value));
}

function slug(string $text): string
{
    $text = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}

function format_number(int $number): string
{
    if ($number >= 1000000) {
        return round($number / 1000000, 1) . 'M';
    }
    if ($number >= 1000) {
        return round($number / 1000, 1) . 'K';
    }
    return (string) $number;
}

function time_ago(string $datetime): string
{
    $time = strtotime($datetime);
    $diff = time() - $time;

    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    if ($diff < 2592000) return floor($diff / 86400) . 'd ago';
    return date('M j, Y', $time);
}

function logger(): App\Core\Logger
{
    static $logger = null;
    if ($logger === null) {
        $logger = new App\Core\Logger();
    }
    return $logger;
}

function setting(string $key, mixed $default = null): mixed
{
    static $settings = null;
    
    // Lazy load all settings once per request
    if ($settings === null) {
        $settings = [];
        try {
            if (class_exists(\App\Models\Setting::class)) {
                $all = \App\Models\Setting::getAll();
                foreach ($all as $s) {
                    $settings[$s['key']] = $s['value'];
                }
            }
        } catch (\Throwable $e) {
            // Fallback if DB not ready
        }
    }
    
    return $settings[$key] ?? $default;
}
