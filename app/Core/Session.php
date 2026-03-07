<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Session — Typed session wrapper with flash message support.
 *
 * Flash messages survive exactly one request (set → read → gone), which is
 * ideal for post-redirect-get patterns.
 *
 * Usage:
 *   Session::start();
 *   Session::set('user_id', 42);
 *   Session::flash('success', 'Your profile was saved.');
 *   redirect('/dashboard');
 *
 * In the next request:
 *   Session::getFlash('success'); // 'Your profile was saved.'
 *   Session::getFlash('success'); // null (already consumed)
 *
 * @author  MTEX.dev <gh.mtex.dev/php-zero>
 * @license MIT
 */
final class Session
{
    private const FLASH_KEY = '__flash';

    private static bool $started = false;

    // ── Lifecycle ─────────────────────────────────────────────────────────────

    /**
     * Start the session (safe to call multiple times).
     */
    public static function start(): void
    {
        if (self::$started || session_status() === PHP_SESSION_ACTIVE) {
            self::$started = true;
            return;
        }

        session_name(Config::get('SESSION_NAME', 'phpzero_session'));

        $lifetime = Config::int('SESSION_LIFETIME', 7200);

        session_set_cookie_params([
            'lifetime' => $lifetime,
            'path'     => '/',
            'secure'   => Config::bool('SESSION_SECURE', false),
            'httponly' => true,
            'samesite' => Config::get('SESSION_SAMESITE', 'Lax'),
        ]);

        session_start();

        self::$started = true;

        // Rotate flash — age messages one step
        self::ageFlash();
    }

    /**
     * Destroy the session entirely (logout).
     */
    public static function destroy(): void
    {
        self::ensureStarted();

        $_SESSION = [];
        session_destroy();
        self::$started = false;
    }

    /**
     * Regenerate the session ID (call after login to prevent fixation).
     */
    public static function regenerate(bool $deleteOld = true): void
    {
        self::ensureStarted();
        session_regenerate_id($deleteOld);
    }

    /**
     * Return the current session ID.
     */
    public static function id(): string
    {
        self::ensureStarted();
        return session_id() ?: '';
    }

    // ── Read / Write ──────────────────────────────────────────────────────────

    public static function set(string $key, mixed $value): void
    {
        self::ensureStarted();
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        self::ensureStarted();
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        self::ensureStarted();
        return array_key_exists($key, $_SESSION);
    }

    public static function forget(string $key): void
    {
        self::ensureStarted();
        unset($_SESSION[$key]);
    }

    /**
     * Remove all session data (but keep the session alive).
     */
    public static function flush(): void
    {
        self::ensureStarted();
        $_SESSION = [];
    }

    // ── Flash messages ────────────────────────────────────────────────────────

    /**
     * Store a flash message. It will be available in the NEXT request only.
     */
    public static function flash(string $key, mixed $value): void
    {
        self::ensureStarted();
        $_SESSION[self::FLASH_KEY]['new'][$key] = $value;
    }

    /**
     * Read a flash message from the CURRENT request and consume it.
     */
    public static function getFlash(string $key, mixed $default = null): mixed
    {
        self::ensureStarted();
        $value = $_SESSION[self::FLASH_KEY]['current'][$key] ?? $default;
        unset($_SESSION[self::FLASH_KEY]['current'][$key]);
        return $value;
    }

    /**
     * Check whether a flash message exists for the current request.
     */
    public static function hasFlash(string $key): bool
    {
        self::ensureStarted();
        return isset($_SESSION[self::FLASH_KEY]['current'][$key]);
    }

    /**
     * Re-flash current messages so they survive one more request.
     * Useful when redirecting again before the user sees them.
     */
    public static function reflash(): void
    {
        self::ensureStarted();
        $current = $_SESSION[self::FLASH_KEY]['current'] ?? [];
        foreach ($current as $key => $value) {
            $_SESSION[self::FLASH_KEY]['new'][$key] = $value;
        }
    }

    // ── CSRF ──────────────────────────────────────────────────────────────────

    /**
     * Return (and generate if absent) the CSRF token for this session.
     */
    public static function csrf(): string
    {
        self::ensureStarted();

        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['_csrf_token'];
    }

    /**
     * Verify a submitted CSRF token using a timing-safe comparison.
     */
    public static function verifyCsrf(string $token): bool
    {
        self::ensureStarted();
        return hash_equals($_SESSION['_csrf_token'] ?? '', $token);
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private static function ensureStarted(): void
    {
        if (!self::$started) {
            self::start();
        }
    }

    /**
     * Move "new" flash data into "current", discarding previous "current" data.
     */
    private static function ageFlash(): void
    {
        $_SESSION[self::FLASH_KEY]['current'] = $_SESSION[self::FLASH_KEY]['new'] ?? [];
        $_SESSION[self::FLASH_KEY]['new']     = [];
    }
}
