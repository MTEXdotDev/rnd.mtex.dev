<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;

/**
 * HomeController — Handles the main web pages.
 *
 * @author  MTEX.dev <gh.mtex.dev/php-zero>
 * @license MIT
 */
final class HomeController
{
    /**
     * GET /
     */
    public function index(): string
    {
        return View::render('home', [
            'title'       => 'PHP-Zero',
            'description' => 'Lightweight PHP Framework by MTEX.dev',
        ], 'app');
    }
}
