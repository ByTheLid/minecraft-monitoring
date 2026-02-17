<?php

namespace App\Core;

class Response
{
    private int $statusCode = 200;
    private array $headers = [];
    private string $body = '';

    public function status(int $code): static
    {
        $this->statusCode = $code;
        return $this;
    }

    public function header(string $name, string $value): static
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function json(mixed $data, int $status = 200): static
    {
        $this->statusCode = $status;
        $this->headers['Content-Type'] = 'application/json; charset=utf-8';
        $this->body = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return $this;
    }

    public function html(string $content, int $status = 200): static
    {
        $this->statusCode = $status;
        $this->headers['Content-Type'] = 'text/html; charset=utf-8';
        $this->body = $content;
        return $this;
    }

    public function redirect(string $url, int $status = 302): static
    {
        $this->statusCode = $status;
        $this->headers['Location'] = $url;
        return $this;
    }

    public function send(): void
    {
        http_response_code($this->statusCode);

        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }

        echo $this->body;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getBody(): string
    {
        return $this->body;
    }
}
