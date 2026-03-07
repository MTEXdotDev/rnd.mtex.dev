<?php

declare(strict_types=1);

/**
 * PHP-Zero Framework — Entry Point
 *
 * @author  MTEX.dev <gh.mtex.dev/php-zero>
 * @license MIT
 */

define('BASE_PATH', dirname(__DIR__));
define('APP_START', microtime(true));

// ─── Autoloader ──────────────────────────────────────────────────────────────

spl_autoload_register(function (string $class): void {
    $map = [
        'App\\Core\\'        => BASE_PATH . '/app/Core/',
        'App\\Controllers\\' => BASE_PATH . '/app/Controllers/',
    ];

    foreach ($map as $prefix => $baseDir) {
        if (str_starts_with($class, $prefix)) {
            $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
            $file     = $baseDir . $relative . '.php';

            if (is_file($file)) {
                require_once $file;
                return;
            }
        }
    }
});

// ─── Global helpers ───────────────────────────────────────────────────────────

require_once BASE_PATH . '/app/Core/helpers.php';

// ─── Bootstrap ───────────────────────────────────────────────────────────────

use App\Core\Config;
use App\Core\ExceptionHandler;
use App\Core\Router;

// 1. Load environment + configuration
Config::load(BASE_PATH . '/.env');

// 2. Set timezone
date_default_timezone_set(Config::get('APP_TIMEZONE', 'Europe/Berlin'));

// 3. Register global exception / error handler
ExceptionHandler::register();

// ─── Routing ─────────────────────────────────────────────────────────────────

$router = new Router();

require BASE_PATH . '/routes/web.php';
require BASE_PATH . '/routes/api.php';

$router->dispatch(
    method: $_SERVER['REQUEST_METHOD'],
    uri:    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/'
);
