<?php

namespace App\Core;

class Router
{
    private array $routes = [];
    private array $groupMiddleware = [];
    private string $groupPrefix = '';

    public function get(string $path, array|string $handler, array $middleware = []): void
    {
        $this->addRoute('GET', $path, $handler, $middleware);
    }

    public function post(string $path, array|string $handler, array $middleware = []): void
    {
        $this->addRoute('POST', $path, $handler, $middleware);
    }

    public function put(string $path, array|string $handler, array $middleware = []): void
    {
        $this->addRoute('PUT', $path, $handler, $middleware);
    }

    public function delete(string $path, array|string $handler, array $middleware = []): void
    {
        $this->addRoute('DELETE', $path, $handler, $middleware);
    }

    public function group(string $prefix, callable $callback, array $middleware = []): void
    {
        $prevPrefix = $this->groupPrefix;
        $prevMiddleware = $this->groupMiddleware;

        $this->groupPrefix = $prevPrefix . $prefix;
        $this->groupMiddleware = array_merge($prevMiddleware, $middleware);

        $callback($this);

        $this->groupPrefix = $prevPrefix;
        $this->groupMiddleware = $prevMiddleware;
    }

    private function addRoute(string $method, string $path, array|string $handler, array $middleware): void
    {
        $fullPath = $this->groupPrefix . $path;
        $allMiddleware = array_merge($this->groupMiddleware, $middleware);

        $this->routes[] = [
            'method' => $method,
            'path' => $fullPath,
            'handler' => $handler,
            'middleware' => $allMiddleware,
            'pattern' => $this->pathToPattern($fullPath),
        ];
    }

    private function pathToPattern(string $path): string
    {
        // Convert {param} to named regex groups
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    public function resolve(Request $request): Response
    {
        $method = $request->method();
        $path = $request->path();

        // Remove trailing slash (except root)
        if ($path !== '/' && str_ends_with($path, '/')) {
            $path = rtrim($path, '/');
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['pattern'], $path, $matches)) {
                // Extract named params
                $params = array_filter($matches, fn($key) => !is_int($key), ARRAY_FILTER_USE_KEY);
                $request->setParams($params);

                // Build middleware pipeline
                $handler = function (Request $req) use ($route) {
                    return $this->callHandler($route['handler'], $req);
                };

                // Wrap handler with middleware (reverse order)
                $pipeline = $handler;
                foreach (array_reverse($route['middleware']) as $middlewareClass) {
                    $pipeline = function (Request $req) use ($middlewareClass, $pipeline) {
                        $middleware = new $middlewareClass();
                        return $middleware->handle($req, $pipeline);
                    };
                }

                return $pipeline($request);
            }
        }

        // 404
        $response = new Response();
        if ($request->isAjax() || str_starts_with($path, '/api/')) {
            return $response->json(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'message' => 'Route not found']], 404);
        }

        return $response->html($this->render404(), 404);
    }

    private function callHandler(array|string $handler, Request $request): Response
    {
        if (is_array($handler)) {
            [$class, $method] = $handler;
            $controller = new $class();
            return $controller->$method($request);
        }

        return $handler($request);
    }

    private function render404(): string
    {
        $viewFile = dirname(__DIR__) . '/Views/errors/404.php';
        if (file_exists($viewFile)) {
            ob_start();
            require $viewFile;
            return ob_get_clean();
        }
        return '<h1>404 Not Found</h1>';
    }
}
