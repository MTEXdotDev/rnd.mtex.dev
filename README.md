# PHP-Zero

**Lightweight PHP Framework by [MTEX.dev](https://mtex.dev)**

> Zero external dependencies. Pure PHP 8.1+. Production-ready micro-framework for developers who want control without the overhead.

**Version:** 1.1.0

---

## Table of Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Directory Structure](#directory-structure)
- [Configuration](#configuration)
- [Routing](#routing)
- [Views & Layouts](#views--layouts)
- [Database](#database)
- [Helper Functions](#helper-functions)
- [Session & Flash Messages](#session--flash-messages)
- [Exception Handling & abort()](#exception-handling--abort)
- [Request Class](#request-class)
- [Assets](#assets)
- [Debugging](#debugging)
- [License](#license)

---

## Requirements

- PHP **8.1** or higher
- Apache with `mod_rewrite` (or Nginx rewrite rules)
- PDO extension
- PDO_MySQL (only for MySQL driver)
- PDO_SQLite (only for SQLite driver — usually bundled with PHP)

---

## Installation

```bash
# 1. Clone the repository
git clone https://github.com/mtex-dev/php-zero.git
cd php-zero

# 2. Copy and configure the environment file
cp .env.example .env

# 3. Point your web server document root at /public

# 4. (SQLite only) Ensure the /database directory is writable
chmod 755 database/
```

No `composer install` required.

### Apache

The included `public/.htaccess` handles rewriting. Ensure `AllowOverride All` is enabled.

### Nginx

```nginx
server {
    root /var/www/php-zero/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

---

## Directory Structure

```
php-zero/
├── public/
│   ├── index.php              # Entry point / bootstrap
│   ├── .htaccess              # Apache rewrite rules
│   └── css/
│       └── app.css            # Application stylesheet
├── app/
│   ├── Core/
│   │   ├── Config.php         # .env loader & config accessor
│   │   ├── Database.php       # PDO wrapper (MySQL + SQLite)
│   │   ├── ExceptionHandler.php
│   │   ├── helpers.php        # Global helper functions
│   │   ├── HttpException.php  # abort() target
│   │   ├── Request.php        # HTTP request wrapper
│   │   ├── Router.php         # Regex router
│   │   ├── Session.php        # Session + flash messages
│   │   └── View.php           # Template engine with layouts
│   ├── Controllers/
│   │   └── HomeController.php
│   └── Views/
│       ├── layouts/
│       │   └── app.php        # Default HTML layout
│       └── home.php
├── database/                  # SQLite database files (auto-created)
├── routes/
│   ├── web.php
│   └── api.php
└── .env
```

---

## Configuration

### .env reference

```ini
# Application
APP_NAME="PHP-Zero"
APP_ENV=development        # development | production
APP_DEBUG=true             # true = stack traces; false = clean error page
APP_TIMEZONE=Europe/Berlin
APP_ASSET_URL=             # optional CDN prefix for asset()

# Database
DB_DRIVER=sqlite           # mysql | sqlite
DB_PATH=                   # SQLite: path to .sqlite file (auto-created)
DB_HOST=localhost          # MySQL only
DB_PORT=3306               # MySQL only
DB_NAME=php_zero           # MySQL only
DB_USER=root               # MySQL only
DB_PASS=                   # MySQL only

# Session
SESSION_NAME=phpzero_session
SESSION_LIFETIME=7200
SESSION_SECURE=false       # true in production (HTTPS required)
SESSION_SAMESITE=Lax
```

### Config class

```php
use App\Core\Config;

Config::get('APP_NAME');              // ?string
Config::get('APP_NAME', 'Default');   // string with fallback
Config::bool('APP_DEBUG', false);     // bool
Config::int('DB_PORT', 3306);         // int
Config::has('DB_PASS');               // bool
```

---

## Routing

```php
// Basic verbs
$router->get('/about',        'HomeController@about');
$router->post('/contact',     'ContactController@store');
$router->put('/posts/{id}',   'PostController@update');
$router->delete('/posts/{id}', 'PostController@destroy');

// Dynamic parameters
$router->get('/user/{id}', function (string $id): string {
    return "User #{$id}";
});

// Custom regex constraint
$router->get('/posts/{slug:[a-z0-9-]+}', function (string $slug): string {
    return View::render('post', ['slug' => $slug], 'app');
});

// Route groups (shared prefix + optional JSON headers)
$router->group('/api', function (Router $r): void {
    $r->get('/users',     'UserController@index');
    $r->post('/users',    'UserController@store');
    $r->delete('/users/{id}', 'UserController@destroy');
}, json: true);
```

---

## Views & Layouts

```php
// Static method
return View::render('home', ['title' => 'Home'], 'app');

// Fluent helper
return view('home', ['title' => 'Home'])->layout('app');

// No layout
return view('partials/card')->with(['item' => $item]);
```

### Layout file

```php
<!-- app/Views/layouts/app.php -->
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body>
    <?= $content ?>
</body>
</html>
```

Variables passed to `View::render()` are available directly inside both the view and the layout.

---

## Database

### Driver selection

Set `DB_DRIVER` in `.env`:

```ini
DB_DRIVER=sqlite   # zero-config local development
DB_DRIVER=mysql    # production MySQL / MariaDB
```

### CRUD examples

```php
use App\Core\Database;

$db = Database::getInstance();

// SELECT
$users = $db->select('SELECT * FROM users WHERE active = :a', ['a' => 1]);
$user  = $db->selectOne('SELECT * FROM users WHERE id = :id', ['id' => 42]);
$count = $db->scalar('SELECT COUNT(*) FROM users');

// INSERT
$id = $db->insert('users', ['name' => 'Alice', 'email' => 'alice@example.com']);

// UPDATE
$db->update('users', ['name' => 'Alice Smith'], ['id' => 42]);

// DELETE
$db->delete('users', ['id' => 42]);

// Transactions
$db->transaction(function () use ($db): void {
    $db->insert('orders',    ['user_id' => 1, 'total' => 99.99]);
    $db->update('inventory', ['stock' => 0], ['product_id' => 7]);
});

// Schema helpers
$db->tableExists('users');            // bool
$db->statement('CREATE TABLE ...');   // DDL

// Driver info
$db->getDriver();  // 'mysql' | 'sqlite'
$db->isSqlite();   // bool
```

---

## Helper Functions

All helpers are globally available after bootstrap.

| Helper | Description |
|--------|-------------|
| `env(key, default)` | Read a `.env` / environment value |
| `abort(code, message)` | Throw an `HttpException` (e.g. `abort(404)`) |
| `redirect(url, code)` | Send `Location` header and exit |
| `json(data, status)` | Send JSON response and exit |
| `asset(path)` | Cache-busted URL for public files |
| `view(name, data)` | Create a fluent `View` instance |
| `csrf_token()` | Return the current CSRF token |
| `csrf_field()` | Render a hidden `<input>` with the CSRF token |
| `old(key, default)` | Retrieve a previously-flashed form value |
| `dump(...$vars)` | Pretty-print values and continue |
| `dd(...$vars)` | Pretty-print values and die |

---

## Session & Flash Messages

```php
use App\Core\Session;

Session::start();           // called automatically on first use

// Read / write
Session::set('user_id', 42);
Session::get('user_id');    // 42
Session::has('user_id');    // true
Session::forget('user_id');

// Flash — survives exactly one redirect
Session::flash('success', 'Profile saved!');
redirect('/dashboard');

// In the next request:
Session::getFlash('success');  // 'Profile saved!'
Session::getFlash('success');  // null (consumed)

// CSRF
$token = Session::csrf();
Session::verifyCsrf($token);   // bool

// In a form view
echo csrf_field();             // <input type="hidden" name="_csrf_token" value="...">

// Security
Session::regenerate();         // rotate ID after login
Session::destroy();            // full logout
```

---

## Exception Handling & abort()

```php
// Throw a clean HTTP exception from anywhere
abort(404, 'The page you are looking for does not exist.');
abort(403, 'You are not authorised to view this resource.');
abort(401);                    // uses default message: "Unauthorized"
abort(429, 'Slow down!');

// Factory shortcuts on HttpException
use App\Core\HttpException;
throw HttpException::notFound();
throw HttpException::forbidden('Admin only.');
throw HttpException::unauthorized();
```

`HttpException` is caught by `ExceptionHandler` and renders:
- A clean HTTP error page (correct status code + message) — regardless of `APP_DEBUG`
- A JSON error body for API requests

All other exceptions render a debug page (`APP_DEBUG=true`) or a generic 500 page (`APP_DEBUG=false`).

---

## Request Class

```php
use App\Core\Request;

// Input
Request::get('page', 1);             // $_GET with fallback
Request::post('email');              // $_POST
Request::input('search');            // GET + POST + JSON body
Request::only(['name', 'email']);    // whitelist
Request::all();                      // all input merged
Request::has('email');               // bool (non-empty)
Request::require(['name', 'email']); // abort(422) on missing

// Files
Request::file('avatar');             // $_FILES['avatar'] or null
Request::hasFile('avatar');          // bool

// Metadata
Request::method();    // 'GET', 'POST', ...
Request::uri();       // '/api/users'
Request::url();       // 'https://example.com/api/users'
Request::ip();        // client IP (proxy-aware)
Request::isPost();    // bool
Request::isAjax();    // bool
Request::isHttps();   // bool

// Headers
Request::header('Content-Type');
Request::bearerToken();      // strips "Bearer " prefix

// JSON body
Request::json();             // decoded array
```

---

## Assets

```php
// Generate a versioned URL for any file in /public
asset('css/app.css');    // /css/app.css?v=1718000000
asset('js/app.js');      // /js/app.js?v=1718000000
asset('img/logo.svg');   // /img/logo.svg?v=1718000000
```

The version query parameter is derived from `filemtime()`, so browsers automatically invalidate cached files when they change.

Set `APP_ASSET_URL` in `.env` to prefix with a CDN base URL:

```ini
APP_ASSET_URL=https://cdn.example.com
# asset('css/app.css') → https://cdn.example.com/css/app.css?v=...
```

---

## Debugging

```php
dump($user, $request);   // pretty-print and continue
dd($query, $params);     // pretty-print and die

// Both detect HTML vs CLI context and format accordingly
```

---

## License

MIT License — © [MTEX.dev](https://mtex.dev)

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.

---

## v1.2.0 additions

### Output escaping

Every view should use these instead of raw `echo` or `htmlspecialchars()`:

```php
// e() — escape and echo HTML text content (the default for everything)
<?= e($user['name']) ?>
<?= e($title ?? 'Default') ?>

// attr() — same as e(), named for attribute contexts
<input value="<?= attr($value) ?>">
<div data-id="<?= attr($id) ?>">

// raw() — intentional unescaped output (trusted HTML only — never user input)
<?= raw($markdownRenderedHtml) ?>
<?= raw('<strong>Bold</strong>') ?>

// js() — safe inline JavaScript value output
<script>
    const config = <?= js($config) ?>;
    const userId = <?= js($user['id']) ?>;
</script>

// partial() — render a sub-view inline
<?= partial('partials/alert', ['type' => 'success', 'message' => 'Saved!']) ?>
<?= partial('partials/user-card', ['user' => $user]) ?>
```

### Validator

```php
use App\Core\Validator;

// Manual validation
$v = Validator::make(Request::all(), [
    'name'     => 'required|min_length:2|max_length:80',
    'email'    => 'required|email',
    'password' => 'required|min_length:8|confirmed',
    'age'      => 'nullable|numeric|min:18',
    'role'     => 'required|in:admin,editor,viewer',
    'slug'     => 'required|alpha_dash',
]);

if ($v->fails()) {
    Session::flash('_errors', $v->errors());
    redirect('/form');
}

$data = $v->validated(); // only declared fields, safe to INSERT

// Auto-validate shorthand (flashes errors + redirects back on failure)
$data = validate(Request::all(), [
    'name'  => 'required',
    'email' => 'required|email',
]);

// In the view — display the first error for a field
<?php if ($e = errors('email')): ?>
    <span class="alert alert-error"><?= e($e) ?></span>
<?php endif; ?>
```

**All validation rules:** `required`, `nullable`, `string`, `numeric`, `integer`, `boolean`, `email`, `url`, `alpha`, `alpha_num`, `alpha_dash`, `min:N`, `max:N`, `min_length:N`, `max_length:N`, `in:a,b,c`, `not_in:a,b`, `confirmed`, `regex:/pattern/`, `date`, `accepted`.

### Response

```php
use App\Core\Response;

// Fluent builder
return Response::make()
    ->status(201)
    ->header('X-Request-Id', 'abc-123')
    ->json(['id' => $newId]);

// Download a file
Response::make()->download('/path/to/report.pdf', 'monthly-report.pdf');

// Static shortcuts
Response::toJson(['ok' => true], 201);
Response::toRedirect('/dashboard');
Response::toHtml(view('home', $data)->layout('app'));

// Cache headers
Response::make()->noCache()->json($sensitiveData);
Response::make()->cache('public, max-age=3600')->html($content);
```

### Logger

```php
use App\Core\Logger;

// Static (default channel from LOG_CHANNEL .env value)
Logger::info('User logged in', ['user_id' => 42]);
Logger::warning('Rate limit close', ['ip' => $ip, 'count' => 95]);
Logger::error('Payment failed', ['order' => $id]);

// Named channels — each gets its own log file
Logger::channel('payments')->error('Stripe charge declined', ['amount' => 99.99]);
Logger::channel('audit')->info('Admin deleted user', ['target_id' => 7]);

// Short global helpers
log_info('Cache miss', ['key' => $k]);
log_error('Webhook signature invalid');
log_debug('SQL executed', ['ms' => 12.4]);
```

Log files: `storage/logs/{channel}-YYYY-MM-DD.log`

### Cache

```php
use App\Core\Cache;

// Store / retrieve
Cache::set('site_stats', $stats, ttl: 3600);
$stats = Cache::get('site_stats');         // null on miss / expiry
Cache::has('site_stats');                  // bool
Cache::forget('site_stats');
Cache::flush();                            // clear all

// remember() — compute once, cache for TTL seconds
$posts = Cache::remember('recent_posts', ttl: 300, callback: function () use ($db) {
    return $db->select('SELECT * FROM posts ORDER BY created_at DESC LIMIT 10');
});

// once() — in-memory, per-request deduplication (never hits disk)
$user = Cache::once("user:{$id}", fn() => $db->selectOne('SELECT * FROM users WHERE id = :id', ['id' => $id]));

// Short global helper
$val = cache('key');                       // get
cache('key', $val, 60);                   // set with TTL
```

### Str

```php
use App\Core\Str;

Str::slug('Hello, World!');               // 'hello-world'
Str::studly('user_profile_page');         // 'UserProfilePage'
Str::camel('user_profile_page');          // 'userProfilePage'
Str::snake('UserProfilePage');            // 'user_profile_page'
Str::kebab('UserProfilePage');            // 'user-profile-page'

Str::truncate($bio, 160);                 // 'Lorem ipsum…'
Str::words($body, 20);                    // first 20 words + '…'

Str::random(32);                          // URL-safe random string
Str::uuid();                              // UUID v4

Str::mask('hello@example.com', '*', 3);  // 'hel**@example.com'
Str::mask('4111111111111111', 'x', 0, 4);// 'xxxxxxxxxxxx1111'

Str::contains('foobar', 'oba');           // true
Str::startsWith('foobar', 'foo');         // true
Str::endsWith('foobar', 'bar');           // true
Str::between('<b>hi</b>', '<b>', '</b>'); // 'hi'

Str::formatBytes(1_048_576);              // '1 MB'
Str::isJson('{"ok":true}');              // true
```
