<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;

class HomeController
{
    public function index(): string
    {
        return View::render('home', [
            'title'       => 'rnd.mtex.dev — Random Data API',
            'description' => 'Instant random & fake data for developers. No auth. No setup. Just GET requests.',
        ], 'app');
    }
}
