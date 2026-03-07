<?php

declare(strict_types=1);

/**
 * API Routes — JSON responses.
 *
 * All routes inside the group automatically receive the
 * `Content-Type: application/json` header.
 *
 * @var \App\Core\Router $router
 */

use App\Core\Config;

$router->group('/api', function (\App\Core\Router $router): void {

    // ── GET /api/status ───────────────────────────────────────────────────────

    $router->get('/status', function (): string {
        $peakBytes = memory_get_peak_usage(true);
        $peakMb    = round($peakBytes / 1_048_576, 3);

        $payload = [
            'status'      => 'online',
            'framework'   => 'php-zero',
            'php_version' => PHP_VERSION,
            'memory_peak' => "{$peakMb} MB",
            'timezone'    => date_default_timezone_get(),
            'timestamp'   => date('c'),              // ISO 8601
            'debug'       => Config::bool('APP_DEBUG', false),
        ];

        return json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    });

    // ── GET /api/ping ─────────────────────────────────────────────────────────

    $router->get('/ping', function (): string {
        return json_encode(['pong' => true, 'time' => microtime(true)]);
    });

}, json: true);
