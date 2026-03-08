<?php

declare(strict_types=1);

use App\Controllers\RndController;
use App\Core\Router;

$router->group('/api', function (Router $r): void {

    // ── Meta ──────────────────────────────────────────────────────────────────
    $r->get('/ping',      'RndController@ping');
    $r->get('/endpoints', 'RndController@endpoints');
    $r->get('/status',    'RndController@status');

    // ── Generators ────────────────────────────────────────────────────────────
    $r->get('/uuid',     'RndController@uuid');
    $r->get('/name',     'RndController@name');
    $r->get('/email',    'RndController@email');
    $r->get('/color',    'RndController@color');
    $r->get('/gradient', 'RndController@gradient');
    $r->get('/number',   'RndController@number');
    $r->get('/string',   'RndController@randomString');
    $r->get('/lorem',    'RndController@lorem');
    $r->get('/ip',       'RndController@ip');
    $r->get('/date',     'RndController@date');
    $r->get('/pick',     'RndController@pick');
    $r->get('/roll',     'RndController@roll');
    $r->get('/coin',     'RndController@coin');
    $r->get('/hash',     'RndController@hash');
    $r->get('/password', 'RndController@password');
    $r->get('/avatar',   'RndController@avatar');

}, json: true);
