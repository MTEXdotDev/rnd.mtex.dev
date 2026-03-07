<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Request — Typed, immutable-style wrapper around the current HTTP request.
 *
 * All methods are static so the class can be used anywhere without injection.
 *
 * @author  MTEX.dev <gh.mtex.dev/php-zero>
 * @license MIT
 */
final class Request
{
    // ── Input (GET / POST / combined) ─────────────────────────────────────────

    /**
     * Read a value from the query string ($_GET).
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return isset($_GET[$key]) ? self::clean($_GET[$key]) : $default;
    }

    /**
     * Read a value from the request body ($_POST).
     */
    public static function post(string $key, mixed $default = null): mixed
    {
        return isset($_POST[$key]) ? self::clean($_POST[$key]) : $default;
    }

    /**
     * Read from query string or request body (POST takes precedence).
     */
    public static function input(string $key, mixed $default = null): mixed
    {
        if (isset($_POST[$key])) {
            return self::clean($_POST[$key]);
        }

        if (isset($_GET[$key])) {
            return self::clean($_GET[$key]);
        }

        // JSON body support
        $json = self::json();
        if (isset($json[$key])) {
            return $json[$key];
        }

        return $default;
    }

    /**
     * Return only the requested keys from combined input.
     *
     * @param  list<string> $keys
     * @return array<string, mixed>
     */
    public static function only(array $keys): array
    {
        $result = [];

        foreach ($keys as $key) {
            $result[$key] = self::input($key);
        }

        return $result;
    }

    /**
     * Return all input values (GET + POST merged, POST wins).
     *
     * @return array<string, mixed>
     */
    public static function all(): array
    {
        return array_merge($_GET, $_POST, self::json());
    }

    /**
     * Check whether a key is present (and non-empty) in the combined input.
     */
    public static function has(string $key): bool
    {
        return self::input($key) !== null && self::input($key) !== '';
    }

    /**
     * Validate that required keys are present. Calls abort(422) if any are missing.
     *
     * @param list<string> $keys
     */
    public static function require(array $keys): void
    {
        foreach ($keys as $key) {
            if (!self::has($key)) {
                abort(422, "Missing required field: {$key}");
            }
        }
    }

    // ── Files ─────────────────────────────────────────────────────────────────

    /**
     * Return a single uploaded file array from $_FILES, or null.
     *
     * @return array{name:string,type:string,tmp_name:string,error:int,size:int}|null
     */
    public static function file(string $key): ?array
    {
        if (!isset($_FILES[$key]) || $_FILES[$key]['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        return $_FILES[$key];
    }

    /**
     * Check whether a file was uploaded without errors.
     */
    public static function hasFile(string $key): bool
    {
        $file = self::file($key);

        return $file !== null && $file['error'] === UPLOAD_ERR_OK;
    }

    // ── Request metadata ──────────────────────────────────────────────────────

    /**
     * Return the HTTP method (always uppercase).
     */
    public static function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    /**
     * Return the request URI path (without query string).
     */
    public static function uri(): string
    {
        return parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
    }

    /**
     * Return the full URL including scheme and host.
     */
    public static function url(): string
    {
        $scheme = self::isHttps() ? 'https' : 'http';
        $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';

        return $scheme . '://' . $host . ($_SERVER['REQUEST_URI'] ?? '/');
    }

    public static function isGet(): bool    { return self::method() === 'GET';    }
    public static function isPost(): bool   { return self::method() === 'POST';   }
    public static function isPut(): bool    { return self::method() === 'PUT';    }
    public static function isDelete(): bool { return self::method() === 'DELETE'; }
    public static function isAjax(): bool
    {
        return ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
    }

    public static function isHttps(): bool
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || ($_SERVER['SERVER_PORT'] ?? '') === '443';
    }

    /**
     * Return the best-guess client IP address.
     */
    public static function ip(): string
    {
        foreach (['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
            if (!empty($_SERVER[$key])) {
                // X-Forwarded-For can be a comma-separated list
                return trim(explode(',', $_SERVER[$key])[0]);
            }
        }

        return '0.0.0.0';
    }

    // ── Headers ───────────────────────────────────────────────────────────────

    /**
     * Read a request header by name (case-insensitive, dashes normalised).
     *
     * Example: Request::header('Content-Type')
     */
    public static function header(string $name, ?string $default = null): ?string
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));

        // Special cases that PHP puts directly in $_SERVER without HTTP_ prefix
        if (in_array(strtoupper($name), ['CONTENT_TYPE', 'CONTENT_LENGTH'], true)) {
            $key = strtoupper(str_replace('-', '_', $name));
        }

        return isset($_SERVER[$key]) ? (string) $_SERVER[$key] : $default;
    }

    /**
     * Extract a Bearer token from the Authorization header.
     */
    public static function bearerToken(): ?string
    {
        $auth = self::header('Authorization') ?? '';

        if (str_starts_with($auth, 'Bearer ')) {
            return substr($auth, 7);
        }

        return null;
    }

    // ── JSON body ─────────────────────────────────────────────────────────────

    /**
     * Decode and return the raw JSON request body.
     *
     * @return array<string, mixed>
     */
    public static function json(): array
    {
        static $decoded = null;

        if ($decoded !== null) {
            return $decoded;
        }

        $contentType = self::header('Content-Type') ?? '';

        if (!str_contains($contentType, 'application/json')) {
            return $decoded = [];
        }

        $body = file_get_contents('php://input');

        if ($body === false || $body === '') {
            return $decoded = [];
        }

        $data = json_decode($body, true);

        return $decoded = is_array($data) ? $data : [];
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /**
     * Recursively trim string values from user input.
     */
    private static function clean(mixed $value): mixed
    {
        if (is_string($value)) {
            return trim($value);
        }

        if (is_array($value)) {
            return array_map(self::clean(...), $value);
        }

        return $value;
    }
}
