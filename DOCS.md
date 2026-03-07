# PHP-Zero — API Reference

**Version:** 1.2.0  
**Author:** [MTEX.dev](https://mtex.dev) · [gh.mtex.dev/php-zero](https://gh.mtex.dev/php-zero)

Complete reference for every class, method, and global helper in PHP-Zero.

---

## Table of Contents

1. [Config](#config)
2. [Router](#router)
3. [View](#view)
4. [Request](#request)
5. [Response](#response)
6. [Session](#session)
7. [Database](#database)
8. [Validator](#validator)
9. [Cache](#cache)
10. [Logger](#logger)
11. [Str](#str)
12. [HttpException](#httpexception)
13. [ExceptionHandler](#exceptionhandler)
14. [Global Helpers](#global-helpers)

---

## Config

**Namespace:** `App\Core\Config`  
**File:** `app/Core/Config.php`

Parses a `.env` file and exposes typed accessors. All methods are static.

### Methods

#### `Config::load(string $path, bool $force = false): void`
Parse and load a `.env` file into the PHP environment. Safe to call multiple times — subsequent calls are no-ops unless `$force = true`.

```php
Config::load(BASE_PATH . '/.env');
Config::load('/other/.env', force: true); // force reload
```

#### `Config::get(string $key, ?string $default = null): ?string`
Return a config value as a string. Lookup order: in-memory cache → `$_ENV` → `getenv()` → `$default`.

```php
Config::get('APP_NAME');              // 'PHP-Zero'
Config::get('MISSING_KEY', 'fallback'); // 'fallback'
```

#### `Config::bool(string $key, bool $default = false): bool`
Return a value interpreted as boolean. Truthy strings: `"true"`, `"1"`, `"yes"`, `"on"`.

```php
Config::bool('APP_DEBUG');       // true / false
Config::bool('MISSING', false);  // false
```

#### `Config::int(string $key, int $default = 0): int`
Return a value cast to integer.

```php
Config::int('DB_PORT', 3306); // 3306
```

#### `Config::has(string $key): bool`
Return `true` if the key is present (and non-null).

#### `Config::all(): array`
Return all values loaded from the `.env` file (excludes externally-set env vars).

---

## Router

**Namespace:** `App\Core\Router`  
**File:** `app/Core/Router.php`

Regex-based HTTP router. Dynamic segments are compiled to named capture groups.

### Methods

#### `$router->get(string $path, callable|string $handler): self`
#### `$router->post(string $path, callable|string $handler): self`
#### `$router->put(string $path, callable|string $handler): self`
#### `$router->delete(string $path, callable|string $handler): self`

Register a route. The handler may be a closure or a `"ControllerClass@method"` string.

```php
$router->get('/users', 'UserController@index');

$router->post('/users', function (): string {
    return json_encode(['created' => true]);
});
```

#### Dynamic segments

```php
// {name}           → matches any non-slash string → injected as $name
// {id:[0-9]+}      → matches only digits
// {slug:[a-z0-9-]+} → lowercase slugs only
$router->get('/post/{slug:[a-z0-9-]+}', 'PostController@show');
```

#### `$router->group(string $prefix, callable $callback, bool $json = false): void`

Group routes under a shared prefix. When `json: true`, all routes send `Content-Type: application/json` automatically.

```php
$router->group('/api', function (Router $r): void {
    $r->get('/users',     'UserController@index');
    $r->post('/users',    'UserController@store');
    $r->delete('/users/{id}', 'UserController@destroy');
}, json: true);
```

#### `$router->dispatch(string $method, string $uri): void`

Called once from `public/index.php`. Matches the request and invokes the handler.

---

## View

**Namespace:** `App\Core\View`  
**File:** `app/Core/View.php`

Output-buffered template engine with layout wrapping.

### Static methods

#### `View::render(string $view, array $data = [], ?string $layout = null): string`

Render a view file, optionally inside a layout. Returns the HTML string.

```php
return View::render('home', ['title' => 'Home'], 'app');
return View::render('partials/card', ['item' => $item]);  // no layout
```

#### `View::make(string $view, array $data = []): View`

Create a `View` instance for fluent chaining. Used internally by `view()`.

#### `View::setViewsPath(string $path): void`

Override the base views directory (defaults to `app/Views/`).

### Fluent API

```php
// All three are equivalent:
echo view('home', ['title' => 'Home'])->layout('app');
echo View::make('home', ['title' => 'Home'])->layout('app');
echo View::render('home', ['title' => 'Home'], 'app');

// Add variables after construction
echo view('home')->with(['title' => 'Home', 'user' => $user])->layout('app');
```

### Layout files

Place layouts in `app/Views/layouts/`. Echo `$content` where the view body should appear:

```php
<!-- app/Views/layouts/app.php -->
<html>
<head><link rel="stylesheet" href="<?= asset('css/app.css') ?>"></head>
<body>
    <?= $content ?>
</body>
</html>
```

All variables passed to `View::render()` are available in both the view **and** the layout.

---

## Request

**Namespace:** `App\Core\Request`  
**File:** `app/Core/Request.php`

Static wrapper around the current HTTP request. All values from `$_GET` / `$_POST` are trimmed automatically.

### Input

| Method | Description |
|--------|-------------|
| `Request::get(string $key, mixed $default = null)` | Read from `$_GET` |
| `Request::post(string $key, mixed $default = null)` | Read from `$_POST` |
| `Request::input(string $key, mixed $default = null)` | Read from GET + POST + JSON body (POST wins) |
| `Request::only(array $keys): array` | Whitelist specific keys from combined input |
| `Request::all(): array` | All GET + POST + JSON body merged |
| `Request::has(string $key): bool` | Check key is present and non-empty |
| `Request::require(array $keys): void` | `abort(422)` if any key is missing |

### Files

```php
Request::file('avatar');      // $_FILES['avatar'] or null
Request::hasFile('avatar');   // bool — true if uploaded without errors
```

### Metadata

```php
Request::method();    // 'GET' | 'POST' | 'PUT' | 'DELETE'
Request::uri();       // '/api/users' (no query string)
Request::url();       // 'https://example.com/api/users?page=2'
Request::ip();        // '192.168.1.1' (proxy-aware)
Request::isGet();     // bool
Request::isPost();    // bool
Request::isPut();     // bool
Request::isDelete();  // bool
Request::isAjax();    // bool — checks X-Requested-With header
Request::isHttps();   // bool
```

### Headers

```php
Request::header('Content-Type');         // ?string
Request::bearerToken();                  // strips "Bearer " prefix → ?string
Request::json();                         // decoded JSON body → array
```

---

## Response

**Namespace:** `App\Core\Response`  
**File:** `app/Core/Response.php`

Fluent HTTP response builder. All terminal methods send headers and exit.

### Fluent API

```php
Response::make()
    ->status(201)
    ->header('X-Request-Id', 'abc-123')
    ->json(['id' => $newId]);

Response::make()
    ->status(200)
    ->cache('public, max-age=3600')
    ->html($htmlContent);
```

### Methods

| Method | Description |
|--------|-------------|
| `->status(int $code)` | Set HTTP status code |
| `->header(string $name, string $value)` | Add a response header |
| `->withHeaders(array $headers)` | Add multiple headers |
| `->cache(string $directive)` | Set Cache-Control header |
| `->noCache()` | Send no-store / no-cache / must-revalidate headers |
| `->json(mixed $data)` | *(terminal)* Send JSON + exit |
| `->html(string $content)` | *(terminal)* Send HTML + exit |
| `->text(string $content)` | *(terminal)* Send plain text + exit |
| `->redirect(string $url, int $code = 302)` | *(terminal)* Send redirect + exit |
| `->download(string $filePath, string $filename = '')` | *(terminal)* Stream file download + exit |
| `->downloadContent(string $content, string $filename, string $contentType)` | *(terminal)* Stream string as download + exit |

### Static shortcuts

```php
Response::toJson(['ok' => true], 201);
Response::toRedirect('/dashboard');
Response::toHtml(view('home', $data)->layout('app'));
Response::notFound('Custom 404 message');
```

---

## Session

**Namespace:** `App\Core\Session`  
**File:** `app/Core/Session.php`

Session wrapper with flash message support and CSRF token management.

### Lifecycle

```php
Session::start();       // Start or resume (called automatically on first use)
Session::destroy();     // Full logout — destroy all data
Session::regenerate();  // Rotate session ID (call after login)
Session::id();          // Return current session ID string
```

### Read / Write

```php
Session::set('user_id', 42);
Session::get('user_id');          // 42
Session::get('missing', 'fallback'); // 'fallback'
Session::has('user_id');          // true
Session::forget('user_id');
Session::flush();                 // Clear all data, keep session alive
```

### Flash messages

Flash data survives exactly one request (set → redirect → read → gone).

```php
// Set (survives to the next request)
Session::flash('success', 'Profile saved!');
Session::flash('error',   'Something went wrong.');
redirect('/dashboard');

// Read (in the next request — consumed immediately)
Session::getFlash('success');   // 'Profile saved!'
Session::getFlash('success');   // null (already consumed)
Session::hasFlash('error');     // bool
Session::reflash();             // Re-queue current flash for one more request
```

### CSRF

```php
$token = Session::csrf();               // generate or retrieve token
Session::verifyCsrf($submittedToken);   // bool — timing-safe comparison

// In a form view:
echo csrf_field();  // <input type="hidden" name="_csrf_token" value="...">
```

---

## Database

**Namespace:** `App\Core\Database`  
**File:** `app/Core/Database.php`

Singleton PDO wrapper supporting MySQL and SQLite. Driver selected via `DB_DRIVER` in `.env`.

### Connection

```php
$db = Database::getInstance();   // singleton
Database::reset();               // destroy singleton (useful in tests)

$db->getDriver();  // 'mysql' | 'sqlite'
$db->isSqlite();   // bool
$db->isMysql();    // bool
$db->getPdo();     // raw PDO instance
```

### SELECT

```php
// All rows
$users = $db->select('SELECT * FROM users WHERE active = :a', ['a' => 1]);

// Single row — returns array|null
$user = $db->selectOne('SELECT * FROM users WHERE id = :id', ['id' => 42]);

// Single scalar value
$count = $db->scalar('SELECT COUNT(*) FROM users');
```

### INSERT / UPDATE / DELETE

```php
// INSERT — returns last insert ID as string
$id = $db->insert('users', ['name' => 'Alice', 'email' => 'alice@example.com']);

// UPDATE — returns affected row count
$rows = $db->update('users', ['name' => 'Alice Smith'], ['id' => 42]);

// DELETE — returns deleted row count
$rows = $db->delete('users', ['id' => 42]);
```

### Raw queries

```php
$stmt = $db->query('SELECT * FROM users WHERE role = :r', ['r' => 'admin']);
$rows = $stmt->fetchAll();
```

### Transactions

```php
$db->transaction(function () use ($db): void {
    $db->insert('orders', ['user_id' => 1, 'total' => 99.99]);
    $db->update('inventory', ['stock' => 0], ['product_id' => 7]);
});

// Manual
$db->beginTransaction();
$db->commit();
$db->rollback();
```

### Schema helpers

```php
$db->tableExists('users');              // bool — works on MySQL and SQLite
$db->statement('CREATE TABLE ...');     // execute DDL, returns bool
```

### .env reference

```ini
DB_DRIVER=sqlite          # 'mysql' | 'sqlite'
DB_PATH=                  # SQLite file path (auto-created; ':memory:' for in-memory)
DB_HOST=localhost          # MySQL only
DB_PORT=3306               # MySQL only
DB_NAME=my_app             # MySQL only
DB_USER=root               # MySQL only
DB_PASS=secret             # MySQL only
DB_CHARSET=utf8mb4         # MySQL only
```

---

## Validator

**Namespace:** `App\Core\Validator`  
**File:** `app/Core/Validator.php`

Rule-based input validation engine. Returns typed errors; never touches headers or the session itself.

### Usage

```php
$v = Validator::make(Request::all(), [
    'name'     => 'required|min_length:2|max_length:80',
    'email'    => 'required|email',
    'password' => 'required|min_length:8|confirmed',
    'age'      => 'nullable|numeric|min:18',
]);

if ($v->fails()) {
    // $v->errors()    — array<field, string[]>
    // $v->error('email') — first error for 'email' or null
    // $v->allErrors() — flat list<string>
    // $v->old()       — all declared-field values (for re-populating forms)
}

$data = $v->validated(); // throws if fails(); only declared fields
```

### Validate shorthand (auto-redirects)

```php
$data = validate(Request::all(), ['name' => 'required', 'email' => 'required|email']);
// Flashes errors + old input and redirects back on failure.
// Returns validated array on success.
```

### All rules

| Rule | Description |
|------|-------------|
| `required` | Must be present and non-empty |
| `nullable` | May be absent / empty (disables required) |
| `string` | Must be a string |
| `numeric` | Must be numeric (int or float) |
| `integer` | Must be an integer |
| `boolean` | Must be "true", "false", "1", or "0" |
| `email` | Valid e-mail address |
| `url` | Valid URL (http/https) |
| `alpha` | Letters only |
| `alpha_num` | Letters and digits only |
| `alpha_dash` | Letters, digits, hyphens, underscores |
| `min:N` | Numeric value ≥ N |
| `max:N` | Numeric value ≤ N |
| `min_length:N` | String length ≥ N chars |
| `max_length:N` | String length ≤ N chars |
| `in:a,b,c` | Value must be one of the listed options |
| `not_in:a,b` | Value must NOT be in the list |
| `confirmed` | A matching `{field}_confirmation` field must exist |
| `regex:/pattern/` | Must match the given regex |
| `date` | Must be parseable by `strtotime()` |
| `accepted` | Must be "yes", "on", "1", or "true" |

### Custom error messages

```php
Validator::make($data, $rules, [
    'email.required' => 'We need your email address.',
    'email.email'    => 'That doesn\'t look like a valid email.',
]);
```

---

## Cache

**Namespace:** `App\Core\Cache`  
**File:** `app/Core/Cache.php`

File-based key/value cache with TTL, stored in `storage/cache/`.

### Methods

```php
Cache::set('key', $value, ttl: 3600); // store for 1 hour
Cache::get('key');                    // mixed|null on miss/expiry
Cache::get('key', 'default');         // with fallback
Cache::has('key');                    // bool
Cache::forget('key');                 // delete one entry
Cache::flush();                       // delete all entries
Cache::ttl('key');                    // seconds remaining (0 = forever or missing)
```

### remember()

```php
// Compute and cache for 5 minutes; served from cache on subsequent calls
$posts = Cache::remember('recent_posts', ttl: 300, callback: function () use ($db): array {
    return $db->select('SELECT * FROM posts ORDER BY created_at DESC LIMIT 10');
});

Cache::rememberForever('site_config', fn () => $db->select('SELECT * FROM config'));
```

### once() — request-level in-memory deduplication

```php
// Same DB call within a request returns the cached result without hitting disk
$user = Cache::once("user:{$id}", fn () => $db->selectOne('SELECT * FROM users WHERE id = :id', ['id' => $id]));
```

### .env reference

```ini
CACHE_TTL=0       # default TTL; 0 = never expires
CACHE_PATH=       # override storage directory
```

---

## Logger

**Namespace:** `App\Core\Logger`  
**File:** `app/Core/Logger.php`

File-based PSR-3-inspired logger. Writes to `storage/logs/{channel}-YYYY-MM-DD.log`.

### Static methods (default channel)

```php
Logger::debug('Query executed',    ['ms' => 12.4]);
Logger::info('User logged in',     ['user_id' => 42]);
Logger::notice('Cache miss',       ['key' => 'posts']);
Logger::warning('Disk space low',  ['free_mb' => 512]);
Logger::error('Payment failed',    ['order' => $id]);
Logger::critical('DB unreachable', ['host' => 'db.example.com']);
Logger::alert('Disk full');
Logger::emergency('Service down');
```

### Named channels

```php
Logger::channel('payments')->error('Charge declined', ['amount' => 99.99]);
Logger::channel('audit')->info('Admin deleted user', ['target_id' => 7]);
Logger::channel('db')->debug('Slow query', ['ms' => 850, 'sql' => $sql]);
```

### Global helper shortcuts

```php
log_debug('Cache miss',   ['key' => $k]);
log_info('User login',    ['id' => $id]);
log_warning('Slow query', ['ms' => 900]);
log_error('Webhook fail', ['status' => 500]);
```

### .env reference

```ini
LOG_CHANNEL=app     # default channel name
LOG_LEVEL=debug     # minimum level: debug|info|notice|warning|error|critical|alert|emergency
LOG_PATH=           # override log directory
```

---

## Str

**Namespace:** `App\Core\Str`  
**File:** `app/Core/Str.php`

Immutable string utility library. All methods are pure static.

### Case

```php
Str::studly('user_profile')  // 'UserProfile'
Str::camel('user_profile')   // 'userProfile'
Str::snake('UserProfile')    // 'user_profile'
Str::kebab('UserProfile')    // 'user-profile'
Str::title('hello world')    // 'Hello World'
```

### Slug

```php
Str::slug('Hello, World!')    // 'hello-world'
Str::slug('über straße')      // 'uber-strase'
Str::slug('foo bar', '_')     // 'foo_bar'
```

### Truncation

```php
Str::truncate($text, 100)            // 'Lorem ipsum…'
Str::truncate($text, 100, '...')     // 'Lorem ipsum...'
Str::words($text, 20)                // first 20 words + '…'
```

### Masking

```php
Str::mask('alice@example.com', '*', 3)      // 'ali**@example.com'
Str::mask('4111111111111111', 'x', 0, 4)    // 'xxxxxxxxxxxx1111'
```

### Generation

```php
Str::random(32)  // URL-safe random string [A-Za-z0-9\-_]
Str::uuid()      // UUID v4
```

### Search

```php
Str::contains('foobar', 'oba')         // true
Str::containsAny('foobar', ['x','o'])  // true
Str::startsWith('foobar', 'foo')       // true
Str::endsWith('foobar', 'bar')         // true
Str::between('<b>hi</b>', '<b>', '</b>') // 'hi'
```

### Utilities

```php
Str::length('héllo')          // 5 (UTF-8 aware)
Str::byteSize('héllo')        // 7 (raw bytes)
Str::pad('5', 3, '0', STR_PAD_LEFT)  // '005'
Str::repeat('ab', 3)          // 'ababab'
Str::swap(['foo'=>'bar'], 'foo and foo') // 'bar and bar'
Str::isJson('{"ok":true}')    // true
Str::formatBytes(1_048_576)   // '1 MB'
```

---

## HttpException

**Namespace:** `App\Core\HttpException`  
**File:** `app/Core/HttpException.php`

Carries an HTTP status code and optional headers. Thrown by `abort()`, caught by `ExceptionHandler`.

```php
// Via global helper
abort(404, 'Page not found.');
abort(403);

// Direct construction
throw new HttpException(429, 'Too many requests.', ['Retry-After' => '60']);

// Factory shortcuts
throw HttpException::notFound();
throw HttpException::forbidden('Admins only.');
throw HttpException::unauthorized();
throw HttpException::unprocessable('Validation failed.');
throw HttpException::tooManyRequests();
```

---

## ExceptionHandler

**Namespace:** `App\Core\ExceptionHandler`  
**File:** `app/Core/ExceptionHandler.php`

Registered once in `public/index.php`. Hooks `set_exception_handler`, `set_error_handler`, and `register_shutdown_function`.

```php
ExceptionHandler::register(); // called once in bootstrap
```

**Rendering behaviour:**

| Exception type | `APP_DEBUG=true` | `APP_DEBUG=false` |
|----------------|-----------------|-------------------|
| `HttpException` | Clean HTTP error page (code + message) | Same |
| Any other | Debug page with source + stack trace | Generic 500 page |
| Any (API request) | JSON with message + trace | JSON: error + code |

API detection: URI starts with `/api/` OR `Accept: application/json` OR `X-Requested-With: XMLHttpRequest`.

---

## Global Helpers

All helpers are defined in `app/Core/helpers.php` and available globally after bootstrap.

### Output escaping

```php
e(mixed $value): string         // HTML text content — use by default
attr(mixed $value): string      // HTML attribute values
raw(string $html): string       // Trusted/pre-sanitised HTML (opt-out of escaping)
js(mixed $value): string        // Safe inline <script> JSON value
```

### Views

```php
view(string $view, array $data = []): View    // fluent View instance
partial(string $view, array $data = []): string  // render sub-view fragment
```

### HTTP

```php
abort(int $code, string $message = ''): never   // throw HttpException
redirect(string $url, int $code = 302): never   // Location header + exit
json(mixed $data, int $status = 200): never     // JSON response + exit
```

### Assets

```php
asset(string $path): string   // /css/app.css?v=1718000000 (cache-busted)
// Set APP_ASSET_URL in .env for CDN: https://cdn.example.com/css/app.css?v=...
```

### Session / CSRF / Forms

```php
csrf_token(): string           // current CSRF token
csrf_field(): string           // <input type="hidden" name="_csrf_token" value="...">
old(string $key, mixed $default = ''): mixed  // previously-flashed form value
errors(string $field): ?string  // first validation error for $field
```

### Validation

```php
validate(array $data, array $rules, array $messages = [], string $redirectTo = ''): array
// Runs Validator::make(); flashes errors + old input and redirects back on failure.
// Returns the validated data array on success.
```

### Environment

```php
env(string $key, ?string $default = null): ?string  // shorthand for Config::get()
```

### Logging

```php
log_debug(string $message, array $context = []): void
log_info(string $message, array $context = []): void
log_warning(string $message, array $context = []): void
log_error(string $message, array $context = []): void
```

### Cache

```php
cache(string $key): mixed               // get
cache(string $key, $value, int $ttl): mixed // set
```

### Debugging

```php
dump(mixed ...$vars): void   // pretty-print, continue execution
dd(mixed ...$vars): never    // pretty-print, die immediately
```
