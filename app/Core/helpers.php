<?php

declare(strict_types=1);

/**
 * PHP-Zero — Global helper functions.
 *
 * Loaded once from public/index.php. All helpers delegate to Core classes so
 * they remain thin convenience wrappers, not logic holders.
 *
 * @author  MTEX.dev <gh.mtex.dev/php-zero>
 * @license MIT
 */

use App\Core\Config;
use App\Core\HttpException;
use App\Core\Session;
use App\Core\View;

// ── Environment ───────────────────────────────────────────────────────────────

if (!function_exists('env')) {
    /**
     * Read an environment / .env value.
     *
     *   env('APP_DEBUG')          // ?string
     *   env('APP_DEBUG', 'false') // string with fallback
     */
    function env(string $key, ?string $default = null): ?string
    {
        return Config::get($key, $default);
    }
}

// ── HTTP ──────────────────────────────────────────────────────────────────────

if (!function_exists('abort')) {
    /**
     * Throw an HttpException, triggering the global exception handler.
     *
     * Examples:
     *   abort(404);
     *   abort(403, 'You are not allowed to do that.');
     *   abort(401, 'Please log in first.');
     *
     * @throws HttpException
     * @return never
     */
    function abort(int $code, string $message = ''): never
    {
        throw new HttpException($code, $message);
    }
}

if (!function_exists('redirect')) {
    /**
     * Send a Location header and terminate the script.
     *
     * Examples:
     *   redirect('/dashboard');
     *   redirect('/login', 302);
     *   redirect()->back();        // not yet — use redirect(Request::header('Referer') ?? '/')
     *
     * @return never
     */
    function redirect(string $url, int $code = 302): never
    {
        if (headers_sent($file, $line)) {
            throw new \RuntimeException(
                "Cannot redirect — headers already sent in {$file} on line {$line}."
            );
        }

        http_response_code($code);
        header('Location: ' . $url);
        exit;
    }
}

if (!function_exists('json')) {
    /**
     * Send a JSON response and terminate the script.
     *
     * Examples:
     *   json(['status' => 'ok']);
     *   json(['error' => 'Not found'], 404);
     *
     * @return never
     */
    function json(mixed $data, int $status = 200, int $flags = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, $flags);
        exit;
    }
}

// ── Assets ────────────────────────────────────────────────────────────────────

if (!function_exists('asset')) {
    /**
     * Generate a cache-busted URL for a file in the /public directory.
     *
     * The version query string is derived from the file's last-modified time so
     * browsers automatically invalidate cached assets when files change.
     *
     * Examples:
     *   asset('css/app.css')   → /css/app.css?v=1718000000
     *   asset('js/app.js')     → /js/app.js?v=1718000000
     *   asset('img/logo.svg')  → /img/logo.svg?v=1718000000
     *
     * When APP_ASSET_URL is set (e.g. a CDN base URL), that prefix is used
     * instead of a root-relative path.
     *
     *   APP_ASSET_URL=https://cdn.example.com
     *   asset('css/app.css')   → https://cdn.example.com/css/app.css?v=...
     */
    function asset(string $path): string
    {
        $path     = ltrim($path, '/');
        $diskPath = (defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__)) . '/public/' . $path;

        $version = is_file($diskPath) ? filemtime($diskPath) : time();

        $base = rtrim(Config::get('APP_ASSET_URL', ''), '/');

        return "{$base}/{$path}?v={$version}";
    }
}

// ── Output escaping ───────────────────────────────────────────────────────────

if (!function_exists('e')) {
    /**
     * Escape a value for safe output inside HTML text content and echo it.
     *
     * This is the standard way to print user-supplied data in views.
     * Combines echo + htmlspecialchars in one short call.
     *
     * Usage in views:
     *   <?= e($title) ?>
     *   <?= e($user['name']) ?>
     *
     * Handles strings, int, float, bool, null and Stringable objects.
     * Arrays / objects that cannot be stringified return an empty string.
     */
    function e(mixed $value, string $encoding = 'UTF-8'): string
    {
        if ($value === null || $value === false) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? '1' : '';
        }

        if (is_array($value) || (is_object($value) && !method_exists($value, '__toString'))) {
            return '';
        }

        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, $encoding);
    }
}

if (!function_exists('raw')) {
    /**
     * Mark a string as intentionally unescaped HTML.
     *
     * Use this only for trusted content (e.g. output from a Markdown parser,
     * or HTML you constructed yourself). Never pass user input to raw().
     *
     * Usage:
     *   <?= raw($markdownHtml) ?>
     *   <?= raw('<strong>Bold</strong>') ?>
     */
    function raw(string $html): string
    {
        return $html;
    }
}

if (!function_exists('attr')) {
    /**
     * Escape a value for safe output inside an HTML attribute.
     *
     * Identical to e() but named for clarity when the context is an attribute:
     *   <input value="<?= attr($value) ?>">
     *   <div class="<?= attr($extraClass) ?>">
     *   <a href="<?= attr($url) ?>">
     *
     * Also safe for data-* attributes and aria-* attributes.
     */
    function attr(mixed $value, string $encoding = 'UTF-8'): string
    {
        return e($value, $encoding);
    }
}

if (!function_exists('js')) {
    /**
     * JSON-encode a value for safe inline output inside a <script> block.
     *
     * Escapes forward slashes and unicode to prevent </script> injection.
     *
     * Usage:
     *   <script>
     *       const config = <?= js($config) ?>;
     *       const name   = <?= js($user['name']) ?>;
     *   </script>
     *
     * @throws \JsonException on encoding failure.
     */
    function js(mixed $value): string
    {
        return json_encode(
            $value,
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT |
            JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
        );
    }
}

// ── Views ─────────────────────────────────────────────────────────────────────

if (!function_exists('view')) {
    /**
     * Create a View instance for fluent rendering.
     *
     * Examples:
     *   echo view('home', ['title' => 'Home'])->layout('app');
     *   echo view('partials/card')->with(['item' => $item]);
     */
    function view(string $view, array $data = []): View
    {
        return View::make($view, $data);
    }
}

if (!function_exists('partial')) {
    /**
     * Render a partial view and return its HTML string.
     *
     * Partials live in app/Views/partials/ by convention, but any dot-path works.
     *
     * Usage inside a view file:
     *   <?= partial('partials/alert', ['type' => 'success', 'msg' => 'Saved!']) ?>
     *   <?= partial('partials/user-card', ['user' => $user]) ?>
     */
    function partial(string $view, array $data = []): string
    {
        return View::render($view, $data);
    }
}

// ── Session / CSRF ────────────────────────────────────────────────────────────

if (!function_exists('csrf_token')) {
    /**
     * Return the current session CSRF token (generates one if absent).
     */
    function csrf_token(): string
    {
        return Session::csrf();
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Return an HTML hidden input containing the CSRF token.
     *
     * Usage inside a view:
     *   <form method="POST">
     *       <?= csrf_field() ?>
     *       ...
     *   </form>
     */
    function csrf_field(): string
    {
        $token = htmlspecialchars(Session::csrf(), ENT_QUOTES);
        return "<input type=\"hidden\" name=\"_csrf_token\" value=\"{$token}\">";
    }
}

if (!function_exists('old')) {
    /**
     * Retrieve a value previously flashed to the session (e.g. after form validation).
     *
     * Usage:
     *   <input name="email" value="<?= old('email') ?>">
     */
    function old(string $key, mixed $default = ''): mixed
    {
        return Session::getFlash("_old_{$key}", $default);
    }
}

// ── Logging ───────────────────────────────────────────────────────────────────

if (!function_exists('log_debug')) {
    /** Write a DEBUG log entry on the default channel. */
    function log_debug(string $message, array $context = []): void
    {
        \App\Core\Logger::debug($message, $context);
    }
}

if (!function_exists('log_info')) {
    /** Write an INFO log entry on the default channel. */
    function log_info(string $message, array $context = []): void
    {
        \App\Core\Logger::info($message, $context);
    }
}

if (!function_exists('log_warning')) {
    /** Write a WARNING log entry on the default channel. */
    function log_warning(string $message, array $context = []): void
    {
        \App\Core\Logger::warning($message, $context);
    }
}

if (!function_exists('log_error')) {
    /** Write an ERROR log entry on the default channel. */
    function log_error(string $message, array $context = []): void
    {
        \App\Core\Logger::error($message, $context);
    }
}

// ── Cache ─────────────────────────────────────────────────────────────────────

if (!function_exists('cache')) {
    /**
     * Get or set a cache value.
     *
     * Zero arguments → returns the Cache class name for static calls:
     *   cache()::remember('key', 300, fn() => $db->select(...))
     *
     * One argument (string key) → retrieve:
     *   $value = cache('user_count');
     *
     * Two arguments → store forever:
     *   cache('user_count', $count);
     *
     * Three arguments → store with TTL (seconds):
     *   cache('user_count', $count, 300);
     */
    function cache(string $key = '', mixed $value = null, int $ttl = 0): mixed
    {
        if ($key === '') {
            return \App\Core\Cache::class;
        }

        if (func_num_args() === 1) {
            return \App\Core\Cache::get($key);
        }

        \App\Core\Cache::set($key, $value, $ttl);
        return $value;
    }
}

// ── Validation ────────────────────────────────────────────────────────────────

if (!function_exists('validate')) {
    /**
     * Validate input data and abort(422) on failure (flash errors + old input).
     *
     * On failure, errors and old input are flashed to the session and the user
     * is redirected back to the previous page (or $redirectTo).
     *
     * On success, returns the validated data array.
     *
     *   $data = validate(Request::all(), [
     *       'name'  => 'required|min_length:2',
     *       'email' => 'required|email',
     *   ]);
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, string> $rules
     * @param  array<string, string> $messages Custom error messages.
     * @param  string                $redirectTo  URL to redirect on failure.
     * @return array<string, mixed>  Validated data.
     */
    function validate(
        array  $data,
        array  $rules,
        array  $messages   = [],
        string $redirectTo = '',
    ): array {
        $v = \App\Core\Validator::make($data, $rules, $messages);

        if ($v->passes()) {
            return $v->validated();
        }

        // Flash errors and old input for the next request
        \App\Core\Session::flash('_errors', $v->errors());

        foreach ($v->old() as $key => $value) {
            \App\Core\Session::flash("_old_{$key}", $value);
        }

        // Redirect back (or to a specific URL)
        $back = $redirectTo !== ''
            ? $redirectTo
            : (\App\Core\Request::header('Referer') ?? '/');

        redirect($back);
    }
}

if (!function_exists('errors')) {
    /**
     * Return validation errors flashed to the session.
     *
     * Usage in views:
     *   <?php if ($e = errors('email')): ?>
     *       <span class="error"><?= e($e) ?></span>
     *   <?php endif; ?>
     *
     * @return string|null The first error message for $field, or null.
     */
    function errors(string $field): ?string
    {
        $all = \App\Core\Session::getFlash('_errors', []);
        return $all[$field][0] ?? null;
    }
}

// ── Debugging ─────────────────────────────────────────────────────────────────

if (!function_exists('dump')) {
    /**
     * Pretty-print one or more values to the browser and continue execution.
     *
     * Outputs an unstyled <pre> block for HTML contexts.
     */
    function dump(mixed ...$vars): void
    {
        $isCli  = PHP_SAPI === 'cli';
        $isJson = str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');

        foreach ($vars as $var) {
            if ($isCli || $isJson) {
                echo print_r($var, true) . "\n";
            } else {
                echo '<pre style="background:#0f172a;color:#e2e8f0;padding:1rem;border-radius:.5rem;'
                    . 'font-family:monospace;font-size:.8125rem;overflow:auto;margin:1rem 0;">';
                echo htmlspecialchars(print_r($var, true), ENT_QUOTES);
                echo '</pre>';
            }
        }
    }
}

if (!function_exists('dd')) {
    /**
     * "Dump and Die" — print values then terminate immediately.
     *
     * @return never
     */
    function dd(mixed ...$vars): never
    {
        dump(...$vars);
        exit(1);
    }
}
