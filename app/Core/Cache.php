<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Cache — File-based key/value cache with TTL support.
 *
 * Cache files live in /storage/cache/ by default and are plain PHP-serialized
 * files — no extra dependencies, zero config for local development.
 *
 * Usage:
 *   // Store for 1 hour
 *   Cache::set('homepage_stats', $stats, ttl: 3600);
 *
 *   // Retrieve (returns null on miss / expiry)
 *   $stats = Cache::get('homepage_stats');
 *
 *   // The remember() pattern — compute-once, cache forever (or with TTL)
 *   $users = Cache::remember('all_users', ttl: 300, callback: function () use ($db) {
 *       return $db->select('SELECT * FROM users');
 *   });
 *
 *   // Forget a single key
 *   Cache::forget('homepage_stats');
 *
 *   // Wipe everything
 *   Cache::flush();
 *
 * Configuration via .env:
 *   CACHE_PATH=       (override directory; defaults to BASE_PATH/storage/cache)
 *   CACHE_TTL=3600    (default TTL in seconds; 0 = forever)
 *
 * @author  MTEX.dev <gh.mtex.dev/php-zero>
 * @license MIT
 */
final class Cache
{
    private static ?string $cacheDir = null;

    // ── Core API ──────────────────────────────────────────────────────────────

    /**
     * Store a value in the cache.
     *
     * @param string $key  Cache key (letters, digits, hyphens, underscores, dots).
     * @param mixed  $value Any serializable PHP value.
     * @param int    $ttl  Time-to-live in seconds. 0 = never expires.
     */
    public static function set(string $key, mixed $value, int $ttl = 0): void
    {
        $ttl      = $ttl === 0 ? (int) Config::get('CACHE_TTL', '0') : $ttl;
        $expiresAt = $ttl > 0 ? time() + $ttl : 0;

        $payload = serialize(['expires_at' => $expiresAt, 'value' => $value]);

        file_put_contents(self::path($key), $payload, LOCK_EX);
    }

    /**
     * Retrieve a cached value. Returns $default on miss or expiry.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $file = self::path($key);

        if (!is_file($file)) {
            return $default;
        }

        $raw = file_get_contents($file);

        if ($raw === false) {
            return $default;
        }

        $payload = @unserialize($raw);

        if (!is_array($payload) || !array_key_exists('value', $payload)) {
            return $default;
        }

        // Check expiry (0 = never expires)
        if ($payload['expires_at'] > 0 && $payload['expires_at'] < time()) {
            self::forget($key);
            return $default;
        }

        return $payload['value'];
    }

    /**
     * Check whether a non-expired entry exists for $key.
     */
    public static function has(string $key): bool
    {
        return self::get($key, "\x00__MISS__\x00") !== "\x00__MISS__\x00";
    }

    /**
     * Remove a single cache entry.
     */
    public static function forget(string $key): void
    {
        $file = self::path($key);

        if (is_file($file)) {
            unlink($file);
        }
    }

    /**
     * Return a cached value, or compute it, cache it, and return it.
     *
     * This is the most common cache pattern:
     *
     *   $posts = Cache::remember('recent_posts', ttl: 600, callback: fn() =>
     *       $db->select('SELECT * FROM posts ORDER BY created_at DESC LIMIT 10')
     *   );
     *
     * @template T
     * @param  callable(): T $callback Invoked only on cache miss.
     * @return T
     */
    public static function remember(string $key, int $ttl, callable $callback): mixed
    {
        $cached = self::get($key);

        if ($cached !== null) {
            return $cached;
        }

        $value = $callback();
        self::set($key, $value, $ttl);

        return $value;
    }

    /**
     * Like remember() but caches for ever (TTL = 0).
     *
     * @template T
     * @param  callable(): T $callback
     * @return T
     */
    public static function rememberForever(string $key, callable $callback): mixed
    {
        return self::remember($key, 0, $callback);
    }

    /**
     * Store a value that expires when the process ends (request-level cache).
     * This is backed by a static in-memory array, not the filesystem.
     *
     * Useful for deduplicating DB calls within a single request.
     */
    public static function once(string $key, callable $callback): mixed
    {
        static $store = [];

        if (!array_key_exists($key, $store)) {
            $store[$key] = $callback();
        }

        return $store[$key];
    }

    /**
     * Delete all entries in the cache directory.
     */
    public static function flush(): void
    {
        $dir = self::dir();

        if (!is_dir($dir)) {
            return;
        }

        foreach (glob($dir . '/*.cache') ?: [] as $file) {
            unlink($file);
        }
    }

    /**
     * Return how many seconds remain before a key expires.
     * Returns 0 if the key does not exist or never expires.
     */
    public static function ttl(string $key): int
    {
        $file = self::path($key);

        if (!is_file($file)) {
            return 0;
        }

        $raw     = file_get_contents($file);
        $payload = $raw ? @unserialize($raw) : null;

        if (!is_array($payload) || $payload['expires_at'] === 0) {
            return 0;
        }

        return max(0, $payload['expires_at'] - time());
    }

    // ── Internals ─────────────────────────────────────────────────────────────

    private static function dir(): string
    {
        if (self::$cacheDir !== null) {
            return self::$cacheDir;
        }

        $configured = Config::get('CACHE_PATH', '');

        self::$cacheDir = $configured !== ''
            ? $configured
            : (defined('BASE_PATH')
                ? BASE_PATH . '/storage/cache'
                : dirname(__DIR__, 3) . '/storage/cache');

        return self::$cacheDir;
    }

    private static function path(string $key): string
    {
        $dir = self::dir();

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // Hash the key to produce a safe filename
        return $dir . '/' . sha1($key) . '.cache';
    }
}
