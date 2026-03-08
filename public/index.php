<?php

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

// ── Autoloader ────────────────────────────────────────────────────────────────
spl_autoload_register(function (string $class): void {
    $map = [
        'App\\Core\\'        => BASE_PATH . '/app/Core/',
        'App\\Controllers\\' => BASE_PATH . '/app/Controllers/',
    ];
    foreach ($map as $prefix => $dir) {
        if (str_starts_with($class, $prefix)) {
            $file = $dir . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
            if (file_exists($file)) {
                require $file;
                return;
            }
        }
    }
});

// ── Bootstrap ─────────────────────────────────────────────────────────────────
use App\Core\Config;
use App\Core\ExceptionHandler;
use App\Core\Router;

Config::load(BASE_PATH . '/.env');

date_default_timezone_set(Config::get('APP_TIMEZONE', 'UTC'));

require BASE_PATH . '/app/Core/helpers.php';

ExceptionHandler::register();

// ── Routes ────────────────────────────────────────────────────────────────────
$router = new Router();

require BASE_PATH . '/routes/web.php';
require BASE_PATH . '/routes/api.php';

$router->dispatch(
    $_SERVER['REQUEST_METHOD'],
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/'
);
