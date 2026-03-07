<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Response — Fluent HTTP response builder.
 *
 * Replaces scattered header() + echo + exit calls with a readable chain:
 *
 *   return Response::make()
 *       ->status(201)
 *       ->header('X-Request-Id', 'abc123')
 *       ->json(['id' => $newId]);
 *
 *   return Response::json(['error' => 'Not found'], 404);
 *
 *   return Response::redirect('/dashboard');
 *
 *   return Response::download('/tmp/report.pdf', 'monthly-report.pdf');
 *
 * Calling a terminal method (json, html, text, redirect, download, send)
 * sends all queued headers, sets the status code, writes the body and exits.
 *
 * @author  MTEX.dev <gh.mtex.dev/php-zero>
 * @license MIT
 */
final class Response
{
    private int $statusCode = 200;

    /** @var array<string, string> */
    private array $headers = [];

    private ?string $body = null;

    // ── Factory ───────────────────────────────────────────────────────────────

    public static function make(): self
    {
        return new self();
    }

    // ── Fluent setters ────────────────────────────────────────────────────────

    /**
     * Set the HTTP status code.
     */
    public function status(int $code): self
    {
        $this->statusCode = $code;
        return $this;
    }

    /**
     * Add (or replace) a response header.
     */
    public function header(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * Add multiple headers at once.
     *
     * @param array<string, string> $headers
     */
    public function withHeaders(array $headers): self
    {
        foreach ($headers as $name => $value) {
            $this->headers[$name] = $value;
        }
        return $this;
    }

    /**
     * Add a cache-control header.
     *
     * Common presets:
     *   ->cache('no-store')
     *   ->cache('public, max-age=3600')
     *   ->cache('private, no-cache, must-revalidate')
     */
    public function cache(string $directive): self
    {
        return $this->header('Cache-Control', $directive);
    }

    /**
     * Prevent all caching.
     */
    public function noCache(): self
    {
        return $this
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    // ── Terminal methods (send & exit) ────────────────────────────────────────

    /**
     * Send a JSON response and exit.
     *
     * @return never
     */
    public function json(
        mixed $data,
        int   $flags = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
    ): never {
        $this->header('Content-Type', 'application/json; charset=utf-8');
        $this->body = json_encode($data, $flags) ?: '{}';
        $this->send();
    }

    /**
     * Send an HTML response and exit.
     *
     * @return never
     */
    public function html(string $content): never
    {
        $this->header('Content-Type', 'text/html; charset=utf-8');
        $this->body = $content;
        $this->send();
    }

    /**
     * Send a plain-text response and exit.
     *
     * @return never
     */
    public function text(string $content): never
    {
        $this->header('Content-Type', 'text/plain; charset=utf-8');
        $this->body = $content;
        $this->send();
    }

    /**
     * Send a redirect response and exit.
     *
     * @return never
     */
    public function redirect(string $url, int $code = 302): never
    {
        $this->statusCode = $code;
        $this->header('Location', $url);
        $this->body = '';
        $this->send();
    }

    /**
     * Trigger a file download and exit.
     *
     * @param string $filePath    Absolute server path to the file.
     * @param string $filename    The name the browser will save it as.
     * @param string $contentType MIME type (auto-detected if empty).
     * @return never
     */
    public function download(string $filePath, string $filename = '', string $contentType = ''): never
    {
        if (!is_readable($filePath)) {
            abort(404, 'File not found.');
        }

        $filename    = $filename !== '' ? $filename : basename($filePath);
        $contentType = $contentType !== ''
            ? $contentType
            : (mime_content_type($filePath) ?: 'application/octet-stream');

        $safeFilename = rawurlencode($filename);

        $this
            ->header('Content-Type', $contentType)
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"; filename*=UTF-8''{$safeFilename}")
            ->header('Content-Length', (string) filesize($filePath))
            ->header('Content-Transfer-Encoding', 'binary');

        $this->sendHeaders();
        readfile($filePath);
        exit;
    }

    /**
     * Stream a string as a download and exit.
     *
     * @return never
     */
    public function downloadContent(
        string $content,
        string $filename,
        string $contentType = 'application/octet-stream',
    ): never {
        $safeFilename = rawurlencode($filename);

        $this
            ->header('Content-Type', $contentType)
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"; filename*=UTF-8''{$safeFilename}")
            ->header('Content-Length', (string) strlen($content));

        $this->body = $content;
        $this->send();
    }

    // ── Static shortcuts ──────────────────────────────────────────────────────

    /**
     * Shortcut: Response::json(['ok' => true], 201)
     *
     * @return never
     */
    public static function toJson(mixed $data, int $status = 200): never
    {
        self::make()->status($status)->json($data);
    }

    /**
     * Shortcut: Response::redirect('/login')
     *
     * @return never
     */
    public static function toRedirect(string $url, int $code = 302): never
    {
        self::make()->redirect($url, $code);
    }

    /**
     * Shortcut: Response::html(view('home', $data)->layout('app'))
     *
     * @return never
     */
    public static function toHtml(string $html, int $status = 200): never
    {
        self::make()->status($status)->html($html);
    }

    /**
     * Shortcut: Response::notFound()
     *
     * @return never
     */
    public static function notFound(string $message = 'Not Found'): never
    {
        abort(404, $message);
    }

    // ── Internal ──────────────────────────────────────────────────────────────

    /**
     * @return never
     */
    private function send(): never
    {
        $this->sendHeaders();
        echo $this->body ?? '';
        exit;
    }

    private function sendHeaders(): void
    {
        if (headers_sent($file, $line)) {
            throw new \RuntimeException(
                "Cannot send headers — already sent in {$file} on line {$line}."
            );
        }

        http_response_code($this->statusCode);

        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }
    }
}
