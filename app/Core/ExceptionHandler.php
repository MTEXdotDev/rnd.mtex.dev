<?php

declare(strict_types=1);

namespace App\Core;

use ErrorException;
use Throwable;
use App\Core\HttpException;

/**
 * ExceptionHandler — Global error and exception handler.
 *
 * Behaviour:
 *   - APP_DEBUG=true  → Detailed stack traces in the browser.
 *   - APP_DEBUG=false → Clean error page (hides internals from users).
 *
 * @author  MTEX.dev <gh.mtex.dev/php-zero>
 * @license MIT
 */
final class ExceptionHandler
{
    private static bool $registered = false;

    // ── Registration ──────────────────────────────────────────────────────────

    /**
     * Register set_exception_handler, set_error_handler and register_shutdown_function.
     */
    public static function register(): void
    {
        if (self::$registered) {
            return;
        }

        set_exception_handler([self::class, 'handleException']);

        set_error_handler(static function (
            int    $severity,
            string $message,
            string $file,
            int    $line
        ): bool {
            if (!(error_reporting() & $severity)) {
                return false;
            }

            throw new ErrorException($message, 0, $severity, $file, $line);
        });

        register_shutdown_function([self::class, 'handleShutdown']);

        self::$registered = true;
    }

    // ── Handlers ──────────────────────────────────────────────────────────────

    public static function handleException(Throwable $e): void
    {
        self::send($e);
    }

    public static function handleShutdown(): void
    {
        $error = error_get_last();

        if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE], true)) {
            self::send(new ErrorException(
                $error['message'],
                0,
                $error['type'],
                $error['file'],
                $error['line']
            ));
        }
    }

    // ── Output ────────────────────────────────────────────────────────────────

    private static function send(Throwable $e): void
    {
        // Discard any output that was already buffered
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $isDebug = Config::bool('APP_DEBUG', false);
        $isApi   = self::isApiRequest();

        // HttpException carries its own status code; everything else is 500
        $statusCode = $e instanceof HttpException ? $e->getStatusCode() : 500;

        http_response_code($statusCode);

        // Send any custom headers attached to the HttpException
        if ($e instanceof HttpException) {
            foreach ($e->getHeaders() as $name => $value) {
                header("{$name}: {$value}");
            }
        }

        if ($isApi) {
            header('Content-Type: application/json; charset=utf-8');
            echo self::renderJson($e, $isDebug, $statusCode);
            return;
        }

        header('Content-Type: text/html; charset=utf-8');

        // For HTTP exceptions, always show a clean page regardless of APP_DEBUG
        // (the message is intentional, not an internal error)
        if ($e instanceof HttpException) {
            echo self::renderHttpHtml($statusCode, $e->getMessage());
            return;
        }

        echo $isDebug ? self::renderDebugHtml($e) : self::renderProductionHtml();
    }

    // ── Renderers ─────────────────────────────────────────────────────────────

    private static function renderJson(Throwable $e, bool $debug, int $statusCode = 500): string
    {
        $message = $e instanceof HttpException ? $e->getMessage() : 'Internal Server Error';

        $payload = [
            'error'   => $message,
            'code'    => $statusCode,
        ];

        if ($debug && !($e instanceof HttpException)) {
            $payload['message'] = $e->getMessage();
            $payload['file']    = $e->getFile();
            $payload['line']    = $e->getLine();
            $payload['trace']   = array_map(
                static fn (array $f): string =>
                    ($f['file'] ?? '') . ':' . ($f['line'] ?? '') . ' — ' . ($f['function'] ?? ''),
                $e->getTrace()
            );
        }

        return json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?: '{"error":"Internal Server Error"}';
    }

    private static function renderHttpHtml(int $code, string $message): string
    {
        $safeCode    = htmlspecialchars((string) $code, ENT_QUOTES);
        $safeMessage = htmlspecialchars($message, ENT_QUOTES);

        return <<<HTML
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>{$safeCode} — {$safeMessage}</title>
            <style>
                *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
                body { font-family: system-ui, -apple-system, sans-serif; background: #0f172a; color: #e2e8f0;
                       display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 2rem; }
                .card { background: #1e293b; border: 1px solid #334155; border-radius: 1rem;
                        padding: 3rem; max-width: 480px; width: 100%; text-align: center; }
                h1 { font-size: 4rem; font-weight: 800; color: #f1f5f9; letter-spacing: -2px; }
                p  { margin-top: 1rem; color: #94a3b8; line-height: 1.6; }
                a  { color: #38bdf8; text-decoration: none; }
            </style>
        </head>
        <body>
            <div class="card">
                <h1>{$safeCode}</h1>
                <p>{$safeMessage}</p>
                <p style="margin-top:1.5rem"><a href="/">← Back to home</a></p>
            </div>
        </body>
        </html>
        HTML;
    }

    private static function renderProductionHtml(): string
    {
        return <<<'HTML'
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>500 — Server Error</title>
            <style>
                *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
                body { font-family: system-ui, -apple-system, sans-serif; background: #0f172a; color: #e2e8f0;
                       display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 2rem; }
                .card { background: #1e293b; border: 1px solid #334155; border-radius: 1rem;
                        padding: 3rem; max-width: 480px; width: 100%; text-align: center; }
                h1 { font-size: 4rem; font-weight: 800; color: #f1f5f9; letter-spacing: -2px; }
                p  { margin-top: 1rem; color: #94a3b8; line-height: 1.6; }
                a  { color: #38bdf8; text-decoration: none; }
            </style>
        </head>
        <body>
            <div class="card">
                <h1>500</h1>
                <p>Something went wrong on our end. We have been notified and will fix this shortly.</p>
                <p style="margin-top:1.5rem"><a href="/">← Back to home</a></p>
            </div>
        </body>
        </html>
        HTML;
    }

    private static function renderDebugHtml(Throwable $e): string
    {
        $class   = htmlspecialchars(get_class($e));
        $message = htmlspecialchars($e->getMessage());
        $file    = htmlspecialchars($e->getFile());
        $line    = $e->getLine();
        $trace   = htmlspecialchars($e->getTraceAsString());
        $code    = self::extractSourceContext($e->getFile(), $e->getLine());

        return <<<HTML
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>Error — {$class}</title>
            <style>
                *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
                body { font-family: system-ui, -apple-system, sans-serif; background: #0f172a; color: #e2e8f0;
                       padding: 2rem; line-height: 1.6; }
                .header { border-left: 4px solid #ef4444; padding-left: 1rem; margin-bottom: 2rem; }
                .class  { font-size: .875rem; color: #f87171; font-weight: 600; text-transform: uppercase;
                          letter-spacing: .05em; }
                h1      { font-size: 1.5rem; font-weight: 700; color: #f1f5f9; margin-top: .25rem; }
                .meta   { margin-top: .5rem; font-size: .875rem; color: #64748b; }
                .meta span { color: #94a3b8; }
                section { background: #1e293b; border: 1px solid #334155; border-radius: .75rem;
                          padding: 1.5rem; margin-bottom: 1.5rem; }
                h2      { font-size: .875rem; font-weight: 600; color: #94a3b8; text-transform: uppercase;
                          letter-spacing: .05em; margin-bottom: 1rem; }
                pre     { font-family: 'JetBrains Mono', 'Fira Code', monospace; font-size: .8125rem;
                          overflow-x: auto; white-space: pre-wrap; word-break: break-word; color: #cbd5e1; }
                .highlight { background: rgba(239,68,68,.15); border-left: 3px solid #ef4444;
                             display: block; padding-left: .5rem; margin-left: -.5rem; }
            </style>
        </head>
        <body>
            <div class="header">
                <div class="class">{$class}</div>
                <h1>{$message}</h1>
                <div class="meta"><span>{$file}</span> on line <span>{$line}</span></div>
            </div>

            {$code}

            <section>
                <h2>Stack Trace</h2>
                <pre>{$trace}</pre>
            </section>
        </body>
        </html>
        HTML;
    }

    // ── Utilities ─────────────────────────────────────────────────────────────

    private static function extractSourceContext(string $file, int $errorLine, int $context = 7): string
    {
        if (!is_readable($file)) {
            return '';
        }

        $lines = file($file, FILE_IGNORE_NEW_LINES);

        if ($lines === false) {
            return '';
        }

        $start = max(0, $errorLine - $context - 1);
        $end   = min(count($lines) - 1, $errorLine + $context - 1);
        $html  = '<section><h2>Source</h2><pre>';

        for ($i = $start; $i <= $end; $i++) {
            $lineNo  = $i + 1;
            $lineHtml = sprintf('%4d │ %s', $lineNo, htmlspecialchars($lines[$i]));

            if ($lineNo === $errorLine) {
                $html .= '<span class="highlight">' . $lineHtml . '</span>' . "\n";
            } else {
                $html .= $lineHtml . "\n";
            }
        }

        return $html . '</pre></section>';
    }

    private static function isApiRequest(): bool
    {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';

        return str_starts_with($uri, '/api/')
            || ($_SERVER['HTTP_ACCEPT'] ?? '') === 'application/json'
            || ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
    }
}
