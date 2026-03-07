<?php

declare(strict_types=1);

/**
 * Web Routes — HTML responses.
 *
 * The $router variable is injected from public/index.php.
 *
 * @var \App\Core\Router $router
 */

use App\Core\View;

// ── Homepage ──────────────────────────────────────────────────────────────────

$router->get('/', 'HomeController@index');

$router->get('/hello/{name}', function (string $name): string {
    return View::render('home', [
        'title'       => 'Hello, ' . e($name) . '!',
        'description' => 'Welcome to PHP-Zero, ' . e($name) . '.',
    ], 'app');
});

// ── Demo section ──────────────────────────────────────────────────────────────

$router->group('/demo', function (\App\Core\Router $r): void {

    // Hub
    $r->get('/',          'DemoController@index');

    // Routing demos
    $r->get('/routing',                     'DemoController@routing');
    $r->get('/user/{id}',                   'DemoController@user');
    $r->get('/slug/{slug:[a-z0-9-]+}',      'DemoController@slugRoute');

    // Request demos
    $r->get('/query',                       'DemoController@query');
    $r->get('/forms',                       'DemoController@forms');
    $r->post('/forms',                      'DemoController@formsPost');

    // Feature demos
    $r->get('/database',                    'DemoController@database');
    $r->post('/database',                   'DemoController@databasePost');
    $r->get('/cache',                       'DemoController@cacheDemo');
    $r->post('/cache',                      'DemoController@cacheDemoPost');
    $r->get('/str',                         'DemoController@str');
    $r->get('/escape',                      'DemoController@escape');
    $r->get('/errors',                      'DemoController@errors');

    // Error trigger — code must be exactly 3 digits
    $r->get('/trigger/{code:[0-9]{3}}',     'DemoController@triggerError');
});

