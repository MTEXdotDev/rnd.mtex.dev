<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * Router — Regex-based HTTP router with group and middleware support.
 *
 * Supports GET, POST, PUT, DELETE methods and dynamic route parameters
 * such as `/user/{id}` or `/posts/{slug:[a-z0-9-]+}`.
 *
 * @author  MTEX.dev <gh.mtex.dev/php-zero>
 * @license MIT
 */
final class Router
{
    /** @var array<int, array{method: string, pattern: string, handler: callable|string, prefix: string, json: bool}> */
    private array $routes = [];

    /** @var string Current group prefix */
    private string $groupPrefix = '';

    /** @var bool Whether routes in the current group return JSON */
    private bool $groupJson = false;

    // ── Route registration ────────────────────────────────────────────────────

    public function get(string $path, callable|string $handler): self
    {
        return $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, callable|string $handler): self
    {
        return $this->addRoute('POST', $path, $handler);
    }

    public function put(string $path, callable|string $handler): self
    {
        return $this->addRoute('PUT', $path, $handler);
    }

    public function delete(string $path, callable|string $handler): self
    {
        return $this->addRoute('DELETE', $path, $handler);
    }

    /**
     * Define a group of routes sharing a common prefix.
     *
     * @param string   $prefix   URL prefix (e.g. "/api").
     * @param callable $callback Function receiving this Router instance.
     * @param bool     $json     When true, all routes in the group send JSON headers.
     */
    public function group(string $prefix, callable $callback, bool $json = false): void
    {
        $previousPrefix = $this->groupPrefix;
        $previousJson   = $this->groupJson;

        $this->groupPrefix = $previousPrefix . $prefix;
        $this->groupJson   = $json;

        $callback($this);

        $this->groupPrefix = $previousPrefix;
        $this->groupJson   = $previousJson;
    }

    // ── Dispatch ──────────────────────────────────────────────────────────────

    /**
     * Match the request to a registered route and invoke its handler.
     */
    public function dispatch(string $method, string $uri): void
    {
        $uri    = '/' . trim($uri, '/');
        $method = strtoupper($method);

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $params = [];

            if ($this->matchPattern($route['pattern'], $uri, $params)) {
                if ($route['json']) {
                    header('Content-Type: application/json; charset=utf-8');
                }

                $response = $this->callHandler($route['handler'], $params);

                if ($response !== null) {
                    echo $response;
                }

                return;
            }
        }

        $this->handleNotFound($method, $uri);
    }

    // ── Internal helpers ──────────────────────────────────────────────────────

    private function addRoute(string $method, string $path, callable|string $handler): self
    {
        $fullPath = $this->groupPrefix . '/' . ltrim($path, '/');
        $fullPath = '/' . ltrim($fullPath, '/');

        $this->routes[] = [
            'method'  => strtoupper($method),
            'pattern' => $this->compilePattern($fullPath),
            'handler' => $handler,
            'prefix'  => $this->groupPrefix,
            'json'    => $this->groupJson,
        ];

        return $this;
    }

    /**
     * Compile a route path into a regex pattern.
     *
     * Supports:
     *   {id}                → (?P<id>[^/]+)
     *   {slug:[a-z0-9-]+}   → (?P<slug>[a-z0-9-]+)
     */
    private function compilePattern(string $path): string
    {
        // Escape forward slashes for use inside regex delimiter
        $pattern = preg_replace_callback(
            '/\{(\w+)(?::([^}]+))?\}/',
            static function (array $m): string {
                $name  = $m[1];
                $regex = $m[2] ?? '[^/]+';
                return "(?P<{$name}>{$regex})";
            },
            $path
        );

        return '#^' . $pattern . '$#';
    }

    /**
     * Match a URI against a compiled pattern and populate $params.
     *
     * @param array<string, string> $params (populated by reference)
     */
    private function matchPattern(string $pattern, string $uri, array &$params): bool
    {
        if (!preg_match($pattern, $uri, $matches)) {
            return false;
        }

        // Extract only named captures
        foreach ($matches as $key => $value) {
            if (is_string($key)) {
                $params[$key] = $value;
            }
        }

        return true;
    }

    /**
     * Invoke a handler (callable or "Controller@method" string).
     *
     * @param  array<string, string> $params Route parameters.
     */
    private function callHandler(callable|string $handler, array $params): mixed
    {
        if (is_callable($handler)) {
            return $handler(...array_values($params));
        }

        // "ControllerClass@method" convention
        if (is_string($handler) && str_contains($handler, '@')) {
            [$class, $method] = explode('@', $handler, 2);

            // Support short class names under App\Controllers
            if (!str_contains($class, '\\')) {
                $class = "App\\Controllers\\{$class}";
            }

            if (!class_exists($class)) {
                throw new RuntimeException("Controller class [{$class}] not found.");
            }

            $controller = new $class();

            if (!method_exists($controller, $method)) {
                throw new RuntimeException("Method [{$method}] not found on [{$class}].");
            }

            return $controller->{$method}(...array_values($params));
        }

        throw new RuntimeException("Invalid route handler.");
    }

    /**
     * Send a 404 response.
     */
    private function handleNotFound(string $method, string $uri): void
    {
        http_response_code(404);

        // If request looks like an API call, return JSON
        if (str_starts_with($uri, '/api/')) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'error'   => 'Not Found',
                'message' => "No route matches [{$method}] {$uri}",
                'code'    => 404,
            ], JSON_PRETTY_PRINT);
            return;
        }

        echo '<!DOCTYPE html><html><head><title>404 Not Found</title>'
            . '<style>body{font-family:system-ui,sans-serif;text-align:center;padding:4rem;color:#555}'
            . 'h1{font-size:4rem;color:#111}code{background:#f3f3f3;padding:.2em .4em;border-radius:4px}</style></head>'
            . '<body><h1>404</h1><p>No route matches <code>[' . htmlspecialchars($method) . '] '
            . htmlspecialchars($uri) . '</code></p></body></html>';
    }
}
