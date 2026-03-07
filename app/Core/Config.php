<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Config — Environment & application configuration loader.
 *
 * Parses a `.env` file (KEY=VALUE pairs) into the process environment and
 * exposes a simple static accessor.
 *
 * @author  MTEX.dev <gh.mtex.dev/php-zero>
 * @license MIT
 */
final class Config
{
    /** @var array<string, string> In-memory cache of loaded values */
    private static array $cache = [];

    /** @var bool Whether the .env file has been loaded */
    private static bool $loaded = false;

    // ── Public API ────────────────────────────────────────────────────────────

    /**
     * Parse and load a .env file into the environment.
     *
     * Calling this multiple times is safe; subsequent calls are no-ops unless
     * $force is true.
     *
     * @param string $path  Absolute path to the .env file.
     * @param bool   $force Re-load even if already loaded.
     */
    public static function load(string $path, bool $force = false): void
    {
        if (self::$loaded && !$force) {
            return;
        }

        if (!is_file($path) || !is_readable($path)) {
            // Graceful degradation — run without .env (env vars may be set externally)
            self::$loaded = true;
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = trim($line);

            // Skip comments and malformed lines
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);

            $key   = trim($key);
            $value = self::normaliseValue(trim($value));

            if ($key === '') {
                continue;
            }

            // Only set if not already present in the real environment
            if (getenv($key) === false) {
                putenv("{$key}={$value}");
                $_ENV[$key]    = $value;
                $_SERVER[$key] = $value;
            }

            self::$cache[$key] = $value;
        }

        self::$loaded = true;
    }

    /**
     * Retrieve a configuration value.
     *
     * Lookup order: in-memory cache → $_ENV → getenv() → $default.
     *
     * @param string      $key     The environment variable name.
     * @param string|null $default Fallback when the key is absent.
     */
    public static function get(string $key, ?string $default = null): ?string
    {
        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }

        if (isset($_ENV[$key])) {
            return (string) $_ENV[$key];
        }

        $env = getenv($key);

        if ($env !== false) {
            return (string) $env;
        }

        return $default;
    }

    /**
     * Return a boolean config value.
     *
     * Truthy strings: "true", "1", "yes", "on".
     */
    public static function bool(string $key, bool $default = false): bool
    {
        $value = self::get($key);

        if ($value === null) {
            return $default;
        }

        return in_array(strtolower($value), ['true', '1', 'yes', 'on'], true);
    }

    /**
     * Return an integer config value.
     */
    public static function int(string $key, int $default = 0): int
    {
        $value = self::get($key);

        return $value !== null ? (int) $value : $default;
    }

    /**
     * Check whether a key is present in the configuration.
     */
    public static function has(string $key): bool
    {
        return self::get($key) !== null;
    }

    /**
     * Return all loaded values (from .env only, not from the real environment).
     *
     * @return array<string, string>
     */
    public static function all(): array
    {
        return self::$cache;
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /**
     * Strip surrounding quotes and handle escape sequences.
     */
    private static function normaliseValue(string $value): string
    {
        // Remove surrounding double or single quotes
        if (
            (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
            (str_starts_with($value, "'") && str_ends_with($value, "'"))
        ) {
            $value = substr($value, 1, -1);
        }

        // Strip inline comments that follow a space (e.g. VALUE=foo # comment)
        if (str_contains($value, ' #')) {
            $value = trim(explode(' #', $value, 2)[0]);
        }

        return $value;
    }
}
