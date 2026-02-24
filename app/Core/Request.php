<?php

namespace App\Core;

class Request
{
    private string $method;
    private string $uri;
    private string $path;
    private array $query;
    private array $body;
    private array $server;
    private array $cookies;
    private array $files;
    private array $params = [];

    public function __construct()
    {
        $this->method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $this->uri = $_SERVER['REQUEST_URI'] ?? '/';
        $this->path = parse_url($this->uri, PHP_URL_PATH) ?: '/';
        $this->query = $_GET;
        $this->server = $_SERVER;
        $this->cookies = $_COOKIE;
        $this->files = $_FILES;

        // Parse body
        if ($this->method === 'POST' || $this->method === 'PUT' || $this->method === 'DELETE') {
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            if (str_contains($contentType, 'application/json')) {
                // Limit JSON body size to 2MB to prevent memory exhaustion
                $maxBodySize = 2 * 1024 * 1024;
                $contentLength = (int) ($_SERVER['CONTENT_LENGTH'] ?? 0);
                if ($contentLength > $maxBodySize) {
                    $this->body = [];
                } else {
                    $raw = file_get_contents('php://input', false, null, 0, $maxBodySize + 1);
                    if (strlen($raw) > $maxBodySize) {
                        $this->body = [];
                    } else {
                        $this->body = json_decode($raw, true) ?: [];
                    }
                }
            } else {
                $this->body = $_POST;
                // For PUT/DELETE with form data
                if ($this->method !== 'POST' && empty($this->body)) {
                    parse_str(file_get_contents('php://input'), $this->body);
                }
            }
        } else {
            $this->body = [];
        }

        // Support _method override for PUT/DELETE from forms
        if (isset($this->body['_method'])) {
            $this->method = strtoupper($this->body['_method']);
            unset($this->body['_method']);
        }
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function uri(): string
    {
        return $this->uri;
    }

    public function query(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->query;
        }
        return $this->query[$key] ?? $default;
    }

    public function input(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->body;
        }
        return $this->body[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($this->query, $this->body);
    }

    public function param(string $key, mixed $default = null): mixed
    {
        return $this->params[$key] ?? $default;
    }

    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    public function cookie(string $key, mixed $default = null): mixed
    {
        return $this->cookies[$key] ?? $default;
    }

    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    /**
     * Get client IP. Only trusts proxy headers from known reverse proxies.
     */
    public function ip(): string
    {
        $trustedProxies = ['127.0.0.1', '::1'];
        $remoteAddr = $this->server['REMOTE_ADDR'] ?? '0.0.0.0';

        if (in_array($remoteAddr, $trustedProxies, true)) {
            $forwarded = $this->server['HTTP_X_FORWARDED_FOR'] ?? '';
            if ($forwarded) {
                // First IP in chain is the original client
                return trim(explode(',', $forwarded)[0]);
            }
            return $this->server['HTTP_X_REAL_IP'] ?? $remoteAddr;
        }

        return $remoteAddr;
    }

    public function userAgent(): string
    {
        return $this->server['HTTP_USER_AGENT'] ?? '';
    }

    public function isAjax(): bool
    {
        return ($this->server['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest'
            || str_contains($this->server['HTTP_ACCEPT'] ?? '', 'application/json');
    }

    public function header(string $name): ?string
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        return $this->server[$key] ?? null;
    }

    public function expects(string $type): bool
    {
        return str_contains($this->server['HTTP_ACCEPT'] ?? '', $type);
    }
}
