<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

final class Router
{
    /**
     * @var array<string, array<int, array{pattern:string, regex:string, params:array<int,string>, handler:mixed}>>
     */
    private array $routes = [];

    private $notFoundHandler = null;

    public function get(string $pattern, $handler): self
    {
        return $this->add('GET', $pattern, $handler);
    }

    public function post(string $pattern, $handler): self
    {
        return $this->add('POST', $pattern, $handler);
    }

    public function put(string $pattern, $handler): self
    {
        return $this->add('PUT', $pattern, $handler);
    }

    public function delete(string $pattern, $handler): self
    {
        return $this->add('DELETE', $pattern, $handler);
    }

    public function add(string $method, string $pattern, $handler): self
    {
        $method = strtoupper(trim($method));

        if ($method === '') {
            throw new RuntimeException('HTTP method cannot be empty.');
        }

        [$regex, $params] = $this->compilePattern($pattern);

        $this->routes[$method][] = [
            'pattern' => $pattern,
            'regex' => $regex,
            'params' => $params,
            'handler' => $handler,
        ];

        return $this;
    }

    public function setNotFoundHandler(callable $handler): self
    {
        $this->notFoundHandler = $handler;

        return $this;
    }

    public function dispatch(?string $method = null, ?string $uri = null)
    {
        $resolvedMethod = $this->resolveMethod($method);
        $resolvedPath = $this->resolvePath($uri);
        $routes = $this->routes[$resolvedMethod] ?? [];

        foreach ($routes as $route) {
            $matches = [];
            if (!preg_match($route['regex'], $resolvedPath, $matches)) {
                continue;
            }

            $params = [];
            foreach ($route['params'] as $name) {
                $params[$name] = isset($matches[$name]) ? rawurldecode((string) $matches[$name]) : null;
            }

            return $this->invokeHandler($route['handler'], $params);
        }

        return $this->handleNotFound($resolvedMethod, $resolvedPath);
    }

    private function resolveMethod(?string $method): string
    {
        $resolved = strtoupper(trim((string) ($method ?? ($_SERVER['REQUEST_METHOD'] ?? 'GET'))));

        if ($resolved === 'POST') {
            $override = '';

            if (isset($_POST['_method'])) {
                $override = strtoupper(trim((string) $_POST['_method']));
            } elseif (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
                $override = strtoupper(trim((string) $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']));
            }

            if (in_array($override, ['PUT', 'DELETE'], true)) {
                return $override;
            }
        }

        return $resolved;
    }

    private function resolvePath(?string $uri): string
    {
        $rawUri = (string) ($uri ?? ($_SERVER['REQUEST_URI'] ?? '/'));
        $path = parse_url($rawUri, PHP_URL_PATH);
        $path = is_string($path) ? $path : '/';

        if ($path === '') {
            $path = '/';
        }

        if ($path !== '/') {
            $path = '/' . trim($path, '/');
        }

        return $path;
    }

    /**
     * @return array{0:string,1:array<int,string>}
     */
    private function compilePattern(string $pattern): array
    {
        $normalized = trim($pattern);
        if ($normalized === '') {
            $normalized = '/';
        }

        if ($normalized !== '/') {
            $normalized = '/' . trim($normalized, '/');
        }

        $paramNames = [];
        // Route placeholders are escaped by preg_quote (\{id\}), so match escaped braces here.
        $regex = preg_replace_callback(
            '/\\\{([a-zA-Z_][a-zA-Z0-9_]*)\\\}/',
            static function (array $matches) use (&$paramNames): string {
                $paramNames[] = $matches[1];

                return '(?P<' . $matches[1] . '>[^/]+)';
            },
            preg_quote($normalized, '#')
        );

        if ($regex === null) {
            throw new RuntimeException('Unable to compile route pattern: ' . $pattern);
        }

        $regex = '#^' . $regex . '$#';

        return [$regex, $paramNames];
    }

    private function invokeHandler($handler, array $params)
    {
        if (is_callable($handler)) {
            return call_user_func_array($handler, $params);
        }

        if (is_array($handler) && count($handler) === 2) {
            $classOrInstance = $handler[0];
            $method = $handler[1];

            if (!is_string($method) || $method === '') {
                throw new RuntimeException('Route handler method is invalid.');
            }

            if (is_string($classOrInstance)) {
                if (!class_exists($classOrInstance)) {
                    throw new RuntimeException('Controller class not found: ' . $classOrInstance);
                }

                $classOrInstance = new $classOrInstance();
            }

            if (!is_object($classOrInstance) || !method_exists($classOrInstance, $method)) {
                throw new RuntimeException('Controller method not found for route handler.');
            }

            return call_user_func_array([$classOrInstance, $method], $params);
        }

        throw new RuntimeException('Unsupported route handler.');
    }

    private function handleNotFound(string $method, string $path)
    {
        http_response_code(404);
        header('Content-Type: text/html; charset=UTF-8');

        if (is_callable($this->notFoundHandler)) {
            return call_user_func($this->notFoundHandler, $method, $path);
        }

        $safePath = htmlspecialchars($path, ENT_QUOTES, 'UTF-8');
        echo '<!doctype html>';
        echo '<html lang="en">';
        echo '<head><meta charset="utf-8"><title>404 Not Found</title></head>';
        echo '<body>';
        echo '<h1>404 Not Found</h1>';
        echo '<p>No route matched for <code>' . $method . ' ' . $safePath . '</code>.</p>';
        echo '</body>';
        echo '</html>';

        return null;
    }
}
