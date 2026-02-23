<?php

namespace App\Core;

class ExceptionHandler
{
    public static function register(): void
    {
        error_reporting(E_ALL);
        
        // Custom error handler
        set_error_handler([self::class, 'handleError']);
        
        // Custom exception handler
        set_exception_handler([self::class, 'handleException']);
        
        // Custom shutdown function to catch fatal errors
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    public static function handleError(int $level, string $message, string $file = '', int $line = 0): bool
    {
        if (error_reporting() & $level) {
            throw new \ErrorException($message, 0, $level, $file, $line);
        }
        return false;
    }

    public static function handleException(\Throwable $exception): void
    {
        // 1. Log the error
        try {
            logger()->error("Uncaught Exception: " . $exception->getMessage(), [
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString()
            ]);
        } catch (\Throwable $e) {
            // Fallback if logger fails
            error_log((string)$exception);
        }

        // 2. Render response
        self::renderResponse($exception);
    }

    public static function handleShutdown(): void
    {
        $error = error_get_last();
        if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_PARSE])) {
            self::handleException(new \ErrorException(
                $error['message'], 0, $error['type'], $error['file'], $error['line']
            ));
        }
    }

    private static function renderResponse(\Throwable $e): void
    {
        // Clean any output buffers
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        http_response_code(500);
        $request = new Request();
        $response = new Response();

        $isDebug = env('APP_DEBUG', false);

        if ($request->isAjax() || str_starts_with($request->path(), '/api/')) {
            $data = [
                'success' => false, 
                'error' => ['code' => 'INTERNAL_ERROR', 'message' => 'Internal server error']
            ];
            
            if ($isDebug) {
                $data['error']['debug'] = [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ];
            }
            $response->json($data, 500)->send();
            exit;
        }

        // Render HTML
        if ($isDebug) {
            $response->html(
                '<div style="background:#f8d7da;color:#721c24;padding:20px;font-family:sans-serif;border-radius:5px;margin:20px;">' .
                '<h1 style="margin-top:0;">Oops! Application Error</h1>' .
                '<p><strong>Message:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>' .
                '<p><strong>File:</strong> ' . $e->getFile() . ' on line ' . $e->getLine() . '</p>' .
                '<hr><pre style="white-space:pre-wrap;background:#fff;padding:15px;border:1px solid #ecc9ce;">' . 
                htmlspecialchars($e->getTraceAsString()) . 
                '</pre></div>',
                500
            )->send();
        } else {
            // Use standard custom 500 view
            $viewPath = dirname(__DIR__, 2) . '/app/Views/errors/500.php';
            if (file_exists($viewPath)) {
                extract(['exception' => $e]);
                ob_start();
                require $viewPath;
                $content = ob_get_clean();
                $response->html($content, 500)->send();
            } else {
                $response->html('<h1>500 Internal Server Error</h1><p>Something went wrong. Please try again later.</p>', 500)->send();
            }
        }
        
        exit(1);
    }
}
