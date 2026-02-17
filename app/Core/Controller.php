<?php

namespace App\Core;

abstract class Controller
{
    protected function json(mixed $data, int $status = 200): Response
    {
        return (new Response())->json($data, $status);
    }

    protected function success(mixed $data = null, array $meta = [], int $status = 200): Response
    {
        $payload = ['success' => true];
        if ($data !== null) {
            $payload['data'] = $data;
        }
        if ($meta) {
            $payload['meta'] = $meta;
        }
        return $this->json($payload, $status);
    }

    protected function error(string $code, string $message, array $details = [], int $status = 400): Response
    {
        $error = ['code' => $code, 'message' => $message];
        if ($details) {
            $error['details'] = $details;
        }
        return $this->json(['success' => false, 'error' => $error], $status);
    }

    protected function view(string $template, array $data = [], int $status = 200): Response
    {
        $content = $this->render($template, $data);
        return (new Response())->html($content, $status);
    }

    protected function render(string $template, array $data = []): string
    {
        $viewDir = dirname(__DIR__) . '/Views';
        $file = $viewDir . '/' . str_replace('.', '/', $template) . '.php';

        if (!file_exists($file)) {
            throw new \RuntimeException("View not found: {$template}");
        }

        // Render the view content
        extract($data);
        ob_start();
        require $file;
        $content = ob_get_clean();

        // If layout is set, wrap content in layout
        if (isset($layout)) {
            $layoutFile = $viewDir . '/layouts/' . $layout . '.php';
            if (file_exists($layoutFile)) {
                $data['content'] = $content;
                extract($data, EXTR_OVERWRITE);
                ob_start();
                require $layoutFile;
                $content = ob_get_clean();
            }
        }

        return $content;
    }

    protected function redirect(string $url, int $status = 302): Response
    {
        return (new Response())->redirect($url, $status);
    }

    protected function validate(array $data, array $rules): array
    {
        $errors = [];

        foreach ($rules as $field => $ruleSet) {
            $ruleList = is_string($ruleSet) ? explode('|', $ruleSet) : $ruleSet;
            $value = $data[$field] ?? null;

            foreach ($ruleList as $rule) {
                $params = [];
                if (str_contains($rule, ':')) {
                    [$rule, $paramStr] = explode(':', $rule, 2);
                    $params = explode(',', $paramStr);
                }

                $error = $this->checkRule($field, $value, $rule, $params);
                if ($error) {
                    $errors[$field] = $error;
                    break; // stop on first error per field
                }
            }
        }

        return $errors;
    }

    private function checkRule(string $field, mixed $value, string $rule, array $params): ?string
    {
        return match ($rule) {
            'required' => ($value === null || $value === '') ? "{$field} is required" : null,
            'email' => ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) ? "Invalid email" : null,
            'min' => ($value && strlen($value) < (int)$params[0]) ? "{$field} must be at least {$params[0]} characters" : null,
            'max' => ($value && strlen($value) > (int)$params[0]) ? "{$field} must not exceed {$params[0]} characters" : null,
            'numeric' => ($value && !is_numeric($value)) ? "{$field} must be a number" : null,
            'between' => ($value && ($value < (int)$params[0] || $value > (int)$params[1])) ? "{$field} must be between {$params[0]} and {$params[1]}" : null,
            'regex' => ($value && !preg_match($params[0], $value)) ? "{$field} has invalid format" : null,
            'url' => ($value && !filter_var($value, FILTER_VALIDATE_URL)) ? "Invalid URL" : null,
            'ip' => ($value && !filter_var($value, FILTER_VALIDATE_IP) && !preg_match('/^[a-zA-Z0-9][a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $value)) ? "Invalid IP or domain" : null,
            default => null,
        };
    }
}
