# PHP-Zero — Roadmap

**Project:** [gh.mtex.dev/php-zero](https://gh.mtex.dev/php-zero)  
**Author:** [MTEX.dev](https://mtex.dev)

This document tracks completed work and planned features. Items are ordered by priority within each milestone.
Contributions and issue reports are welcome on GitHub.

---

## Released

### v1.0.0
- [x] `Config` — native `.env` parser, typed accessors
- [x] `Router` — regex-based, GET/POST/PUT/DELETE, dynamic `{params}`, groups
- [x] `View` — output-buffered engine, layout wrapping, fluent `view()` helper
- [x] `Database` — PDO wrapper, MySQL only, `select/insert/update/delete`, transactions
- [x] `ExceptionHandler` — debug vs. production pages, shutdown handler
- [x] `HttpException` — typed HTTP errors with status code
- [x] `public/.htaccess` — Apache rewrite, security headers
- [x] `routes/web.php` + `routes/api.php` — split route files
- [x] `/api/status` endpoint — memory, timezone, timestamp
- [x] `README.md`

### v1.1.0
- [x] `Database` — SQLite driver (`DB_DRIVER=sqlite|mysql`), driver-aware identifier quoting, `tableExists()`, `scalar()`, `statement()`
- [x] `abort(int $code, string $message)` — global helper → throws `HttpException`
- [x] `asset(string $path)` — cache-busted URLs, CDN prefix support
- [x] `Request` — typed wrapper: input, files, headers, `bearerToken()`, `ip()`, JSON body, `require()`
- [x] `Session` — start/destroy/regenerate, flash messages, CSRF token
- [x] `Response` — fluent builder, `json/html/text/redirect/download`, cache helpers
- [x] `helpers.php` — `env()`, `abort()`, `redirect()`, `json()`, `asset()`, `view()`, `csrf_field()`, `old()`, `dd()`, `dump()`

### v1.2.0
- [x] Output escaping family — `e()`, `attr()`, `raw()`, `js()`
- [x] `partial(string $view, array $data)` — sub-view helper
- [x] `Validator` — pipe-rule engine, 22 rules, `validated()`, custom messages
- [x] `validate()` global — auto-flash + redirect on failure
- [x] `errors(string $field)` view helper
- [x] `Logger` — PSR-3 levels, named channels, daily-rotated files
- [x] `Cache` — file-based TTL cache, `remember()`, `once()`, `flush()`
- [x] `Str` — 20+ utilities: `slug`, case helpers, `truncate`, `mask`, `random`, `uuid`, `formatBytes`
- [x] `public/css/app.css` — extracted stylesheet, BEM classes
- [x] `partials/alert.php` — flash message partial with SVG icons
- [x] `DemoController` + 9 demo views + `/demo` route group

---

## Planned

### v1.3.0 — Middleware & Pipeline
- [ ] **Middleware interface** — `handle(Request $request, callable $next): Response`
- [ ] **Router middleware** — attach to individual routes or groups: `$router->get('/admin', ...)->middleware('auth')`
- [ ] **Built-in middleware** — `AuthMiddleware`, `CsrfMiddleware`, `RateLimitMiddleware`, `CorsMiddleware`
- [ ] **Global middleware stack** — registered in `public/index.php`, run on every request
- [ ] **Route middleware aliases** — `'auth'`, `'guest'`, `'throttle:60,1'`

### v1.3.0 — Auth Foundation
- [ ] **`Auth` class** — `Auth::login($user)`, `Auth::logout()`, `Auth::user()`, `Auth::check()`, `Auth::id()`
- [ ] **Password helpers** — `password_hash()` / `password_verify()` wrappers with `bcrypt` cost config
- [ ] **`auth()` global** — shorthand for `Auth::user()` / `Auth::check()`
- [ ] **Auth demo** — login/logout form, `AuthMiddleware` example

### v1.4.0 — Collections & Model Layer
- [ ] **`Collection` class** — wrap `array` results with `map()`, `filter()`, `first()`, `pluck()`, `groupBy()`, `sortBy()`, `sum()`, `chunk()`
- [ ] **Database returns `Collection`** — `$db->select(...)` returns `Collection` instead of bare array
- [ ] **`Model` base class** — static `find(int $id)`, `where(array $conditions)`, `all()`, `create(array $data)`, `save()`, `delete()` — backed by `Database`
- [ ] **Convention-based table names** — `User` → `users`, `BlogPost` → `blog_posts` via `Str::snake()`
- [ ] **Timestamps** — optional `created_at` / `updated_at` auto-management

### v1.4.0 — Query Builder
- [ ] **`QueryBuilder` class** — fluent SQL: `DB::table('users')->where('active', 1)->orderBy('name')->limit(10)->get()`
- [ ] **Supported clauses** — `select`, `where`, `orWhere`, `whereIn`, `join`, `leftJoin`, `orderBy`, `groupBy`, `having`, `limit`, `offset`
- [ ] **Aggregates** — `count()`, `sum()`, `avg()`, `min()`, `max()`
- [ ] **`DB` facade** — `DB::table()`, `DB::raw()`, `DB::select()`, `DB::statement()`

### v1.5.0 — Migrations
- [ ] **`Migration` base class** — `up(): void` / `down(): void` interface
- [ ] **`migrations` table** — tracks applied batch numbers
- [ ] **CLI runner** — `php zero migrate`, `php zero migrate:rollback`, `php zero migrate:fresh`
- [ ] **Schema builder** — `Schema::create('users', function (Blueprint $t) { ... })`, typed column methods
- [ ] **SQLite + MySQL** — schema builder generates correct DDL per driver

### v1.5.0 — CLI Tool (`php zero`)
- [ ] **`zero` script** — entry point at project root
- [ ] **`make:controller Name`** — scaffold `app/Controllers/NameController.php`
- [ ] **`make:migration name`** — scaffold timestamped migration file
- [ ] **`cache:clear`** — flush `storage/cache/`
- [ ] **`log:clear`** — flush `storage/logs/`
- [ ] **`serve`** — start PHP built-in server on `localhost:8000`
- [ ] **`env:check`** — validate required `.env` keys are present

### v1.6.0 — Events & Hooks
- [ ] **`Event` dispatcher** — `Event::dispatch('user.registered', $user)`, `Event::on('user.registered', $listener)`
- [ ] **Listener classes** — implement `handle(array $payload): void`
- [ ] **Async-ready** — listeners optionally queued (file-based queue backend)
- [ ] **Built-in events** — `request.received`, `route.matched`, `exception.handled`

### v1.6.0 — HTTP Client
- [ ] **`Http` class** — cURL-based, no Guzzle dependency
- [ ] **Methods** — `Http::get(url, query)`, `Http::post(url, body)`, `Http::put()`, `Http::delete()`
- [ ] **Response object** — `->json()`, `->body()`, `->status()`, `->header()`, `->ok()`, `->throw()`
- [ ] **Options** — timeout, auth headers, `withToken()`, `withHeaders()`, retry logic

### v1.7.0 — View Enhancements
- [ ] **Template components** — `<?= component('button', ['label' => 'Save']) ?>` with slot support
- [ ] **View composers** — bind data to a view automatically: `View::compose('nav', fn () => ['user' => Auth::user()])`
- [ ] **Stack system** — `@push('scripts')` / `@stack('scripts')` for injecting scripts from child views
- [ ] **View caching** — optional compiled-view cache for production
- [ ] **i18n / translation** — `t('auth.login')`, locale detection, JSON translation files

### v1.8.0 — Testing Layer
- [ ] **`TestCase` base class** — bootstraps the framework in an isolated environment
- [ ] **HTTP testing** — `$this->get('/users')`, `$this->post('/login', $data)`, `->assertStatus(200)`, `->assertJson()`
- [ ] **Database testing** — in-memory SQLite per test, automatic rollback
- [ ] **Fake helpers** — `Cache::fake()`, `Logger::fake()`, `Event::fake()`

### v1.9.0 — Developer Experience
- [ ] **Error page improvements** — request data panel (GET/POST/headers), route match info
- [ ] **`Config::required(array $keys)`** — throw on missing keys at boot time
- [ ] **`.env.example` generator** — `php zero env:scaffold`
- [ ] **OpenAPI / Swagger stub** — `php zero make:openapi` from route definitions
- [ ] **Health endpoint** — `/api/health` with DB ping, cache ping, disk space
- [ ] **Profiling** — optional request profiler: SQL count, time per phase, memory delta

---

## Ideas Under Consideration

These are not yet scheduled but may land in a minor release:

- **WebSocket support** — via Ratchet or native socket server (separate process)
- **Rate limiter** — `RateLimit::allow('ip', 60, 60)` backed by Cache
- **Queue / jobs** — file-based job queue, `dispatch(new SendEmailJob($user))`
- **Mail** — `Mail::to($user)->send(new WelcomeMail())` via `mail()` or SMTP
- **Cookie helper** — `Cookie::set()`, `Cookie::get()`, `Cookie::forget()`, signed cookies
- **Pagination** — `$db->paginate('SELECT * FROM posts', page: 2, perPage: 15)` → `Paginator`
- **File storage** — `Storage::put('avatars/user.jpg', $data)`, local disk + abstracted interface
- **Signed URLs** — `url_signed('/download/report.pdf', expiry: 3600)`

---

## Versioning Policy

PHP-Zero follows **Semantic Versioning** (`MAJOR.MINOR.PATCH`):

- **PATCH** — bug fixes, documentation improvements, no API changes.
- **MINOR** — new features, backward-compatible. New classes / helpers may be added.
- **MAJOR** — breaking changes. Deprecated in the prior minor release first.

Minimum PHP version will only advance on a MAJOR release with at least 3 months notice.
