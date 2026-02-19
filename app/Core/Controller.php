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
        $validator = new Validator();
        $validator->validate($data, $rules);
        
        // Controller expects [field => error_message] (single error per field?)
        // Controller logic was: return $errors where $errors[field] = single string.
        // Validator stores array of errors per field.
        
        $flattenedErrors = [];
        foreach ($validator->errors() as $field => $messages) {
             $flattenedErrors[$field] = $messages[0];
        }
        
        return $flattenedErrors;
    }
}
