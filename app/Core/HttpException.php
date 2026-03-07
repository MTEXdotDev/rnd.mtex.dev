<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * HttpException — Represents an HTTP error response.
 *
 * Thrown by the global abort() helper and caught by ExceptionHandler,
 * which uses the status code to send the correct HTTP response.
 *
 * @author  MTEX.dev <gh.mtex.dev/php-zero>
 * @license MIT
 */
final class HttpException extends RuntimeException
{
    /** @var array<string, string> Extra headers to send with the response. */
    private array $headers;

    /**
     * @param int                  $statusCode HTTP status code (e.g. 404, 403, 422).
     * @param string               $message    Human-readable description.
     * @param array<string,string> $headers    Additional response headers.
     */
    public function __construct(
        private readonly int $statusCode,
        string $message = '',
        array $headers = [],
    ) {
        parent::__construct(
            message: $message !== '' ? $message : self::defaultMessage($statusCode),
            code: $statusCode,
        );

        $this->headers = $headers;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /** @return array<string, string> */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    // ── Convenience factory methods ───────────────────────────────────────────

    public static function notFound(string $message = 'Not Found'): self
    {
        return new self(404, $message);
    }

    public static function forbidden(string $message = 'Forbidden'): self
    {
        return new self(403, $message);
    }

    public static function unauthorized(string $message = 'Unauthorized'): self
    {
        return new self(401, $message);
    }

    public static function unprocessable(string $message = 'Unprocessable Entity'): self
    {
        return new self(422, $message);
    }

    public static function tooManyRequests(string $message = 'Too Many Requests'): self
    {
        return new self(429, $message);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private static function defaultMessage(int $code): string
    {
        return match ($code) {
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            422 => 'Unprocessable Entity',
            429 => 'Too Many Requests',
            500 => 'Internal Server Error',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            default => 'HTTP Error',
        };
    }
}
